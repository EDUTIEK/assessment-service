<?php

namespace Edutiek\AssessmentService\System\Spreadsheet;

use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Config\ReadService as Config;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

readonly class Service implements FullService
{
    private const noncharacters = [
        '\x{FFFE}-\x{FFFF}',
        '\x{1FFFE}-\x{1FFFF}',
        '\x{2FFFE}-\x{2FFFF}',
        '\x{3FFFE}-\x{3FFFF}',
        '\x{4FFFE}-\x{4FFFF}',
        '\x{5FFFE}-\x{5FFFF}',
        '\x{6FFFE}-\x{6FFFF}',
        '\x{7FFFE}-\x{7FFFF}',
        '\x{8FFFE}-\x{8FFFF}',
        '\x{9FFFE}-\x{9FFFF}',
        '\x{AFFFE}-\x{AFFFF}',
        '\x{BFFFE}-\x{BFFFF}',
        '\x{CFFFE}-\x{CFFFF}',
        '\x{DFFFE}-\x{DFFFF}',
        '\x{EFFFE}-\x{EFFFF}',
        '\x{FFFFE}-\x{FFFFF}',
        '\x{10FFFE}-\x{10FFFF}',
        '\x{FDD0}-\x{FDEF}'
    ];


    public function __construct(
        private Storage $store,
        private string $temp_path
    ) {
    }

    /**
     * Get the data from a file as an array
     */
    public function dataFromFile(string $id, ?string $select_sheet = null): array
    {
        $path = $this->store->getReadablePath($id);
        if ($path) {
            $spreadsheet = IOFactory::load($path);

            if($select_sheet !== null) {
                $spreadsheet->setActiveSheetIndexByName($select_sheet);
            }

            return $spreadsheet->getActiveSheet()->toArray();
        }
        return [];
    }

    public function getNewSheet(?string $title, array $header, array $rows): Sheet
    {
        return new Sheet($title, $header, $rows);
    }

    /**
     * Export data to a file
     * @param array<string, string> $header key/values of the header row
     * @param array<array<string, mixed>> $rows arrays of key/value of the content rows
     * @return string id of the stored file
     */
    public function dataToFile(array $header, array $rows, ExportType $type, string $title = null): string
    {
        return $this->sheetsToFile([$this->getNewSheet($title, $header, $rows)], $type);
    }

    public function sheetsToFile(array $sheets, ExportType $type): string
    {
        $workbook = new Spreadsheet();
        $first = true;

        foreach ($sheets as $data) {
            if($first) {
                $sheet = $workbook->getActiveSheet();
                $first = false;
            } else {
                $sheet = $workbook->createSheet();
            }

            if ($data->getTitle()) {
                $sheet->setTitle($this->sheetName($data->getTitle()));
            }

            $c = $r = 1;
            $columns = [];

            foreach ($data->getHeader() as $key => $title) {
                $columns[] = $key;
                $sheet->setCellValue($this->cellAddress($r, $c++), $this->cellValue($title));
            }

            foreach ($data->getRows() as $row) {
                $c = 1;
                $r++;
                foreach ($columns as $column) {
                    $sheet->setCellValue($this->cellAddress($r, $c++), $this->cellValue($row[$column] ?? null));
                }
            }
        }


        $file = $this->temp_path . '/' . uniqid('', true) . $type->extension();

        switch ($type) {
            case ExportType::CSV:
                $writer = new Csv($workbook);
                $writer->setUseBOM(true);
                $writer->setDelimiter(';');
                $writer->setEnclosure('"');
                $writer->setLineEnding("\r\n");
                break;
            case ExportType::EXCEL:
                $writer = new Xlsx($workbook);
                break;
        }
        $writer->save($file);

        $fp = fopen($file, 'r');
        $info = $this->store->saveFile(
            $fp,
            $this->store->newInfo()
                        ->setFileName($this->store->asciiFilename($title) . $type->extension())
                        ->setMimeType($type->mimetype())
        );
        unlink($file);

        return $info->getId();
    }

    //Possible way to add dropdown lists for a future implementation
    //$objValidation = new DataValidation();
    //
    //$objValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
    //$objValidation->setAllowBlank(true);
    //$objValidation->setShowDropDown(true);
    //$objValidation->setFormula1($a_target_formula);
    //
    //$address = CellAddress::fromColumnAndRow($a_col,$a_row) ;
    //$this->workbook->getActiveSheet()->getCell($address)->setDataValidation(clone $objValidation);

    /**
     * Get an excel cell address
     * row and column are 1-based
     */
    private function cellAddress(int $row, int $column)
    {
        return Coordinate::stringFromColumnIndex($column) . $row;
    }

    private function cellValue(mixed $value): string
    {
        switch (gettype($value)) {
            case "boolean":
                return $value ? '1' : '0';

            case 'double':
            case 'integer':
                return $value;

            case 'string':
                if (!mb_check_encoding($value, 'UTF-8')) {
                    return 'invalid utf-8';
                }
                return strip_tags(
                    mb_ereg_replace(
                        '[' . implode('', self::noncharacters) . ']',
                        '',
                        $value
                    )
                );

            case 'object':
                if ($value instanceof \DateTimeInterface) {
                    return $value;
                }
                // no break
            default:
                return '';
        }
    }

    private function sheetName(string $title): string
    {
        $title = str_replace(['*', ':', '/', '\\', '?', '[', ']', '\'-', '\''], '', $title);
        return substr($title, 0, 31);
    }
}
