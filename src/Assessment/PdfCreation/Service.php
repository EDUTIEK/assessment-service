<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\PdfCreation;

use Edutiek\AssessmentService\Assessment\PdfCreation\FullService;
use Edutiek\AssessmentService\Assessment\Api\ComponentApiFactory;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\PdfConfig;
use Edutiek\AssessmentService\System\PdfCreator\PdfPart;

class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private ComponentApiFactory $apis,
        private Repositories $repos,
    ) {
    }

    public function getSortedParts(PdfPurpose $purpose): array
    {
        $configs = [];
        $max = 0;
        foreach ($this->repos->pdfConfig()->allByAssIdAndPurpose($this->ass_id, $purpose->value) as $config) {
            $configs[$config->getComponent()][$config->getKey()] = $config;
            $max = max($max, $config->getPosition());
        }

        $parts = [];
        foreach ($this->apis->components($this->ass_id, $this->user_id) as $component) {
            foreach ($this->getProvider($component, $purpose)?->getAvailableParts() ?? [] as $part) {
                $config = $configs[$part->getComponent()][$part->getKey()] ?? null;
                /** @var PdfConfig $config */
                if ($config !== null) {
                    $part->setIsActive($config->getIsActive());
                    $part->setPosition($config->getPosition());
                } else {
                    $part->setIsActive(true);
                    $part->setPosition(++$max);
                }
                $parts[sprintf('part%04d', $part->getPosition())] = $part;
            }
        }
        sort($parts);
        return array_values($parts);
    }

    /**
     * Save the activation and sorting
     * @param PdfConfigPart[] $parts
     */
    public function saveSortedParts(PdfPurpose $purpose, array $parts): void
    {
        $repo = $this->repos->pdfConfig();
        $this->repos->pdfConfig()->deleteByAssIdAndPurpose($this->ass_id, $purpose->value);

        foreach ($parts as $part) {
            $repo->save($repo->new()
                ->setAssId($this->ass_id)
                ->setPurpose($purpose->value)
                ->setComponent($part->getComponent())
                ->setKey($part->getKey())
                ->setIsActive($part->getIsActive())
                ->setPosition($part->getPosition()));
        }
    }

    public function createWritingPdf(int $writer_id): string
    {
        // todo: replace experimental stuff
        $parts = $this->getSortedParts(PdfPurpose::CORRECTION);
        $this->saveSortedParts(PdfPurpose::CORRECTION, $parts);
        return '';
    }

    public function createCorrectionPdf(int $writer_id): string
    {
        // TODO: Implement createCorrectionPdf() method.
        return '';
    }

    private function getProvider(string $component, PdfPurpose $purpose): ?PdfPartProvider
    {
        switch ($purpose) {
            case PdfPurpose::WRITING:
                return $this->apis->api($component)?->writingPartProvider($this->ass_id, $this->user_id);

            case PdfPurpose::CORRECTION:
                return $this->apis->api($component)?->correctionPartProvider($this->ass_id, $this->user_id);
        }
        return null;
    }
}
