<?php declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfProcessing;

class Doc
{
    private const OBJS = 1;

    private ?string $catalog = null;
    private ?string $pages = null;
    private ?int $last_id = null;

    private array $obj_dicts = [];

    public function __construct(private array $tree)
    {
    }

    public function get(string $name): ?array
    {
        return $this->tree[self::OBJS][$name] ?? null;
    }

    /**
     * @param Node[] $nodes
     * @return ObjName
     */
    public function newObj(array $nodes): string
    {
        $this->lastId(); // Init $this->last_id.
        $this->last_id++;
        $name = $this->last_id . '_0';
        $this->tree[self::OBJS][$name] = $nodes;
        return $name;
    }

    public function catalog(): string
    {
        return $this->catalog ??= $this->findType('Catalog');
    }

    public function pages(): string
    {
        return $this->pages ??= $this->objGet($this->catalog(), 'Pages')[PdfNode::VALUE];
    }

    /**
     * Get the Node of $key's value associated with $obj_name's dict.
     * This is cached to prevent multiple loops over the object's dict entries.
     *
     * @param ObjName $obj_name
     * @return ?Node
     */
    public function objGet(string $obj_name, string $key): ?array
    {
        if (!isset($this->obj_dicts[$obj_name])) {
            $this->obj_dicts[$obj_name] = $this->objDict2array($this->tree[self::OBJS][$obj_name]);
        }

        return $this->obj_dicts[$obj_name][$key] ?? null;
    }

    /**
     * Set $key to $val in $obj_name's dict.
     * To prevent multiple loops over the $object_name's dict, the changes are written to $this->tree when calling $this->writeBack().
     *
     * @param ObjName $obj_name
     * @param Node $val
     */
    public function objSet(string $obj_name, string $key, array $val): void
    {
        if (!isset($this->obj_dicts[$obj_name])) {
            $this->obj_dicts[$obj_name] = $this->objDict2array($this->tree[self::OBJS][$obj_name]);
        }
        $this->obj_dicts[$obj_name][$key] = $val;
    }

    /**
     * @param array<string, Node> $array
     */
    public function objSetAll(string $obj_name, array $array): void
    {
        $this->obj_dicts[$obj_name] = $array;
    }

    public function objSetNodes(string $obj_name, array $nodes): void
    {
        unset($this->obj_dicts[$obj_name]);
        $this->tree[self::OBJS][$obj_name] = $nodes;
    }

    public function deleteObj(string $obj_name): void
    {
        unset($this->obj_dicts[$obj_name]);
        unset($this->tree[self::OBJS][$obj_name]);
    }

    /**
     * @return Doc
     */
    public function writeBack(): void
    {
        foreach ($this->obj_dicts as $n => $d) {
            $i = array_find_key($this->tree[self::OBJS][$n], fn(array $a) => $a[PdfNode::TYPE] === '<<');
            $this->tree[self::OBJS][$n][$i][PdfNode::VALUE] = PdfNode::array2dict($d);
        }
    }

    public function tree(): array
    {
        return $this->tree;
    }

    public function findType(string $type): ?string
    {
        return $this->findPair('Type', $type);
    }

    public function findPair(string $key, string $value): ?string
    {
        return array_find_key($this->tree[self::OBJS], fn(array $nodes) => array_find(
            $nodes,
            fn($n) => $n[PdfNode::TYPE] === '<<' && $this->dictKeyEq($n[PdfNode::VALUE], $key, $value)
        ));
    }

    /**
     * @param Node $entry
     * @return Node
     */
    public function unref(array $entry): array // @Todo writeBack needed
    {
        return $entry[PdfNode::TYPE] === 'objref' ?
            current($this->get($entry[PdfNode::VALUE])) :
            $entry;
    }

    public function lastId(): int
    {
        return $this->last_id ??= max(array_map('intval', array_keys($this->tree[self::OBJS])));
    }

    /**
     * @param Node $root
     * @param null|callable(Node): void $on_node
     * @return \Iterator<Node[]>
     */
    public function iterateNumTreeLeafs(array $root, ?callable $on_node = null): \Iterator
    {
        return $this->iterateTreeLeafs('Nums', $root);
    }

    /**
     * @param Node $root
     * @param null|callable(Node): void $on_node
     * @return \Iterator<Node[]>
     */
    public function iterateNameTreeLeafs(array $root, ?callable $on_node = null): \Iterator
    {
        return $this->iterateTreeLeafs('Names', $root, $on_node);
    }

    /**
     * @param string $key // 'Names' or 'Nums' depending on the tree kind.
     * @param Node $root
     * @param null|callable(Node): void $on_node
     * @return \Iterator<Node[]>
     */
    private function iterateTreeLeafs(string $key, array $root, ?callable $on_node = null): \Iterator
    {
        $on_node ??= fn() => null;
        $todo = [$root];
        while ($todo !== []) {
            $ref_or_node = array_shift($todo);
            $on_node($ref_or_node);
            $node = $this->unref($ref_or_node);
            if ($node[PdfNode::TYPE] !== '<<') {
                throw new PdfUniteException('Invalid num tree child, not a dict', $ref_or_node);
            }
            $dict = PdfNode::dict2array($node[PdfNode::VALUE]);
            if (isset($dict[$key])) {
                // Typecheck array
                yield $dict[$key][PdfNode::VALUE];
            } elseif (!isset($dict['Kids'])) {
                throw new PdfUniteException("Invalid $key tree, neither $key nor Kids present", $ref_or_node);
            } else {
                // Typecheck array
                $todo = array_merge($todo, $dict['Kids'][PdfNode::VALUE]);
            }
        }
    }

    /**
     * @param Node[] $dict
     */
    private function dictKeyEq(array $dict, string $key, string $value): bool
    {
        for(; $entry_key = current($dict); next($dict)) {
            $entry_val = next($dict);
            if ($entry_key[0] === '/' && $entry_key[1] === $key && $entry_val[0] === '/' && $entry_val[1] === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * returns the first dict in $nodes in the form of an associative array instead of raw dict entries.
     * This can be used to select the dict associated to an object.
     *
     * Also see dict2array for more information.
     *
     * @param Node[] $nodes
     * @return array<string, Node>
     */
    private function objDict2array(array $nodes): array
    {
        return PdfNode::dict2array($this->nextDictEntries($nodes));
    }

    /**
     * Returns the first dict entries (type '<<') found in $nodes.
     *
     * @param Node[] $nodes
     * @return Node[]
     */
    private function nextDictEntries(array $nodes): array
    {
        return array_find($nodes, fn(array $n) => $n[PdfNode::TYPE] === '<<')[PdfNode::VALUE] ?? [];
    }

}
