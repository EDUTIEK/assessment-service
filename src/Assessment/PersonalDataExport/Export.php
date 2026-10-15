<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport;

use Edutiek\AssessmentService\System\User\ReadService as UserService;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Config\ReadService as Config;
use ZipArchive;
use Edutiek\AssessmentService\System\File\Delivery;
use Edutiek\AssessmentService\System\File\Disposition;

class Export
{

    public function __construct(
        private UsageResolver $assessment_usage_resolver,
        private UsageResolver $task_usage_resolver,
        private array $components_usage_resolver,
        private UserService $user_service,
        private Storage $storage,
        private Delivery $delivery,
        private Config $config
    ) {
    }

    /**
     * @param $user_id
     * @return Group
     */
    public function createExportGroup($user_id): Group
    {
        $query = new UsageQuery($user_id, null, null, null, null, false, [[$this->task_usage_resolver], $this->components_usage_resolver]);
        $nodes = $this->assessment_usage_resolver->collectUsage($query);
        $groups = array_merge(...array_map(fn (UsageNode $node) => $node->getGroupsFinal(), $nodes));

        $pde_assembly = new PersonalDataExportGroupAssembly(new UsageScope("System", $user_id));

        $user = $this->user_service->getUser($user_id);
        $pde_assembly->addUser($user);
        $pde_assembly->addAssessmentGroups($groups);

        return $pde_assembly->getGroup();
    }

    public function downloadZip(int $user_id): void
    {
        $group = $this->createExportGroup($user_id);

        $provider = fn (string $id) => $this->storage->getFileInfo($id);

        $json_converter = new GroupToJsonConverter($provider);
        $json = $json_converter->convert($group);

        $zipfile = $this->config->getSetup()->getAbsoluteTempPath()
            . uniqid('', true) . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipfile, ZipArchive::CREATE);

        $zip->addFromString('data.json', $json);

        foreach ($json_converter->getFileIdToFilenameMap() as $file_id => $filename) {
            $path = $this->storage->getReadablePath($file_id);
            if ($path !== null) {
                $zip->addFile($path, $filename);
            }
        }
        $zip->close();

        $fp = fopen($zipfile, 'r');
        $zip_name = "PersonalDataExport.zip";
        $info = $this->storage->saveFile(
            $fp,
            $this->storage->newInfo()
                          ->setMimeType('application/zip')
                          ->setFileName($zip_name)
        );
        $this->delivery->sendFile($info->getId(), Disposition::INLINE, $info);

        $this->storage->deleteFile($info->getId());
        #return $info->getId();
    }

}
