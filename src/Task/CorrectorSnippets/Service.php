<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorSnippets;

use Edutiek\AssessmentService\Task\Data\CorrectorSnippetPurpose;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\System\Data\FileInfo;
use Edutiek\AssessmentService\System\Spreadsheet\FullService as Spreadsheets;
use Edutiek\AssessmentService\System\Spreadsheet\ExportType;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\System\Entity\FullService as Entities;
use Psr\Http\Message\UploadedFileInterface;
use Edutiek\AssessmentService\Task\Data\CorrectorSnippet;

class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private Spreadsheets $spreadsheets,
        private Storage $storage,
        private Entities $entities,
        private Language $language
    ) {
    }

    public function json(int $corrector_id): array
    {
        $data = [];
        foreach ($this->repos->correctorSnippets()->allByCorrectorId($this->ass_id, $corrector_id) as $snippet) {
            $data[] = $this->entities->arrayToPrimitives([
                'key' => $snippet->getKey(),
                'purpose' => $snippet->getPurpose(),
                'shortcut' => $snippet->getShortcut(),
                'text' => $snippet->getText()
            ]);
        }
        return $data;
    }

    public function export(int $corrector_id, CorrectorSnippetPurpose $purpose): FileInfo
    {
        $header = [
            'text' => 'text',
            'shortcut' => 'shortcut',
        ];

        $rows = [];
        foreach ($this->repos->correctorSnippets()->allByCorrectorIdAndPurpose($this->ass_id, $corrector_id, $purpose) as $snippet) {
            $rows[] = [
                'text' => $snippet->getText(),
                'shortcut' => $snippet->getShortcut()
            ];
        }

        $id = $this->spreadsheets->dataToFile(
            $header,
            $rows,
            ExportType::EXCEL,
            $this->language->txt('snippets_export_filename')
        );

        return $this->storage->getFileInfo($id)->setDisposable(true);
    }

    public function import(UploadedFileInterface $file, int $corrector_id, CorrectorSnippetPurpose $purpose): ?array
    {
        $info = $this->storage->saveFile($file->getStream(), null);
        if ($info) {
            $repo = $this->repos->correctorSnippets();
            $data = $this->spreadsheets->dataFromFile($info->getId());
            $this->storage->deleteFile($info->getId());

            $header = [];
            $snippets = [];
            $first = true;
            foreach ($data as $row) {
                $snippet = $repo->new()
                                ->setAssId($this->ass_id)
                                ->setCorrectorId($corrector_id)
                                ->setPurpose($purpose)
                                ->buildKey();

                foreach ($row as $col => $value) {
                    if ($first) {
                        $header[$col] = $value;
                    } else {
                        switch ($header[$col] ?? null) {
                            case 'text':
                                $snippet->setText($value);
                                break;
                            case 'shortcut':
                                $snippet->setShortcut($value);
                                break;
                        }
                    }
                }
                $first = false;
                $this->entities->secure($snippet, CorrectorSnippet::class);
                if ($snippet->getText() !== null && $snippet->getText() !== '') {
                    $snippets[] = $snippet;
                }
            }

            if (!empty($snippets)) {
                $repo->deleteByCorrectorIdAndPurpose($this->ass_id, $corrector_id, $purpose);
                foreach ($snippets as $snippet) {
                    $repo->save($snippet);
                }
                return ['Snippets' => $this->json($corrector_id)];
            }
        }
        return null;
    }
}
