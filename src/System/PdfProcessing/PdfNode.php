<?php declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfProcessing;

class PdfNode
{
    public const TYPE = 0; // Node's 0 index is it's type (Node[0])
    public const VALUE = 1; // Node's 0 index is it's value (Node[1])

    /**
     * Opposite of dict2array.
     * This will create a correct PDF dict entries from an associative array.
     *
     * @param array<string, Node> $array
     * @return Node[]
     */
    public static function array2dict(array $array): array
    {
        $dict = [];
        foreach ($array as $k => $v) {
            $dict[] = ['/', $k];
            $dict[] = $v;
        }
        return $dict;
    }

    /**
     * Creates a PHP associative array from dict entries (type '<<').
     * In a PDF dict keys an values alternate, keys are always of type '/' aka. simple strings.
     * @example
     * ```php
     * $this->dict2array([['/', 'key'], ['/', 'val'], ['/', 'key2'], ['[', []]])
     * ```
     * returns
     * ```php
     * ['key' => ['/', 'val'], 'key2' => ['[', []]]
     * ```
     *
     * @param Nodes[] $nodes
     * @return array<string, Node>
     */
    public static function dict2array(array $nodes): array
    {
        $array = [];
        for(; $key = current($nodes); next($nodes)) {
            $array[$key[self::VALUE]] = next($nodes);
        }

        return $array;
    }
}
