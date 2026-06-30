<?php declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfProcessing;

/**
 * @phpstan-type ObjName string // of the form '$id_$version'
 * @phpstan-type Node array{0: string, 1: mixed, ...} // 0 is the type
 * @phpstan-type Doc array{
 *     0: array{'xref': int[], 'trailer': array<string, mixed>},
 *     1: array<ObjName, Node[]>
 * }
 */
class PdfWriter
{
    private const TYPE = 0;
    private const VALUE = 1;

    private const META = 0;
    private const OBJS = 1;

    /** @var resource */
    private $target;

    /**
     * @param resource $target
     */
    public function __construct($target)
    {
        $this->target = $target;
    }

    /**
     * @param Doc $doc
     */
    public function write(string $header, array $doc, $recalculate_xref = true): void
    {
        fwrite($this->target, $header);

        $obj_map = [];
        $max_obj_id = 1;

        foreach ($doc[self::OBJS] as $name => $nodes) {
            $nr = intval($name);
            $max_obj_id = max($nr, $max_obj_id);
            $obj_map[$nr] = ftell($this->target);
            $this->writeObj($name, $nodes);
        }

        $startxref = ftell($this->target);
        if ($recalculate_xref) {
            $this->writeCalculatedXref($doc, $obj_map, $max_obj_id);
        } else {
            $this->writeDocXref($doc);
        }

        $this->writeTrailer($doc, $obj_map, $recalculate_xref);
        fwrite($this->target, 'startxref' . PHP_EOL);
        fwrite($this->target, $startxref . PHP_EOL);
        fwrite($this->target, '%%EOF' . PHP_EOL);
    }

    /**
     * @param Node $node
     */
    private function writeNode(array $node): void
    {
        switch ($node[self::TYPE]) {
            case 'numeric':
            case 'boolean':
                fwrite($this->target, ' ' . $node[self::VALUE]);
                break;
            case '<<':
                fwrite($this->target, '<<');
                array_map([$this, 'writeNode'], $node[self::VALUE]);
                fwrite($this->target, '>>');
                break;
            case '<':
                fwrite($this->target, '<' . $node[self::VALUE] . '>');
                break;
            case '/':
                fwrite($this->target, ' /' . $node[self::VALUE]);
                break;
            case 'objref':
                fwrite($this->target, ' ' . str_replace('_', ' ', $node[self::VALUE]) . ' R');
                break;
            case '[':
                fwrite($this->target, '[');
                array_map([$this, 'writeNode'], $node[self::VALUE]);
                fwrite($this->target, ']');
                break;
            case '(':
                fwrite($this->target, '('.$node[self::VALUE].')');
                break;
            case 'stream':
                fwrite($this->target, 'stream' . PHP_EOL);
                fwrite($this->target, $node[self::VALUE]);
                break;
            case 'endstream':
                fwrite($this->target, 'endstream');
                break;
            case 'null':
                fwrite($this->target, ' null');
                break;
            default:
                throw new PdfWriterException('Unknown entry type', $node[self::TYPE]);
            }
    }

    /**
     * @param Doc $doc
     * @param array<int, int> $obj_map // obj number to file offset
     */
    private function writeCalculatedXref(array $doc, array $obj_map, int $max_obj_id): void
    {
        fwrite($this->target, 'xref' . PHP_EOL);
        fwrite($this->target, '0 ' . ($max_obj_id + 1) . PHP_EOL);
        fwrite($this->target, '0000000000 65535 f' . PHP_EOL);

        for ($i = 1; $i <= $max_obj_id; $i++) { // Must be accending in obj id order.
            if (isset($obj_map[$i])) {
                fprintf($this->target, "%010d 00000 n\r\n", $obj_map[$i]);
            } else {
                fprintf($this->target, "0000000000 00000 f\r\n");
            }
        }
    }

    /**
     * @param Doc $doc
     */
    private function writeDocXref(array $doc): void
    {
        fwrite($this->target, 'xref' . PHP_EOL);
        fwrite($this->target, '0 ' . (count($doc[self::META]['xref']) + 1) . PHP_EOL);
        fwrite($this->target, '0000000000 65535 f' . PHP_EOL);
        foreach ($doc[self::META]['xref'] as $entry) {
            fprintf($this->target, '%010d 00000 n' . PHP_EOL, $entry);
        }
    }

    /**
     * @param Doc $doc
     * @param array<int, int> $obj_map // obj number to file offset
     */
    private function writeTrailer(array $doc, array $obj_map, bool $recalculate_size): void
    {
        fwrite($this->target, 'trailer' . PHP_EOL);
        fwrite($this->target, '<<' . PHP_EOL);
        foreach ($doc[self::META]['trailer'] as $key => $value) {
            $key = $key === 'id' ? 'ID' : ucfirst($key);
            fwrite($this->target, '/' . $key . ' ');
            if ($key === 'Size' && $recalculate_size) {
                fwrite($this->target, (string) (count($obj_map) + 1));
                continue;
            }
            switch (gettype($value)) {
            case 'string':
                fwrite($this->target, str_replace('_', ' ', $value) . ' R');
                break;
            case 'integer':
                fwrite($this->target, (string) $value);
                break;
            case 'array':
                fwrite($this->target, '[');
                foreach ($value as $v) {
                    fwrite($this->target, ' <' . $v . '>');
                }
                fwrite($this->target, ']');
                break;
            default:
                throw new PdfWriterException('Trailer: Dunno how to write type ' . gettype($value), $value);
            }
            fwrite($this->target, PHP_EOL);
        }
        fwrite($this->target, PHP_EOL . '>>' . PHP_EOL);
    }

    /**
     * @param Node[] $nodes
     */
    private function writeObj(string $name, array $nodes): void
    {
        fwrite($this->target, str_replace('_', ' ', $name) . ' obj' . PHP_EOL);
        array_map([$this, 'writeNode'], $nodes);
        fwrite($this->target, PHP_EOL . 'endobj' . PHP_EOL);
    }
}
