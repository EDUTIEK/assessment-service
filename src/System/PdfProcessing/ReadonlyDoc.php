<?php declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfProcessing;

class ReadonlyDoc extends Doc
{
    public function newObj(array $nodes): string
    {
        $this->error(__FUNCTION__);
    }

    public function objSet(string $name, string $key, array $val): void
    {
        $this->error(__FUNCTION__);
    }

    public function objSetAll(string $name, array $array): void
    {
        $this->error(__FUNCTION__);
    }

    public function objSetNodes(string $obj_name, array $nodes): void
    {
        $this->error(__FUNCTION__);
    }

    public function deleteObj(string $obj_name): void
    {
        $this->error(__FUNCTION__);
    }

    private function error(string $n): void
    {
        throw new Exception('Cannot call ' . $n . ' in readonly doc');
    }
}
