<?php declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfProcessing;

use Closure;

require_once __DIR__ . '/polyfill.php';

/**
 * @phpstan-type ObjName string // of the form '$id_$version'
 * @phpstan-type Node array{0: string, 1: mixed, ...} // 0 is the type
 * @phpstan-type Doc array{
 *     0: array{'xref': int[], 'trailer': array<string, mixed>},
 *     1: array<ObjName, Node[]>
 * }
 */
class PdfUnite
{
    /**
     * Map from src object names to dest object names.
     * This is used so multiple references to src use the new dest name.
     * @var array<ObjName, ObjName>
     */
    private array $src_dest = [];

    private ReadonlyDoc $src;

    private Doc $dest;

    /**
     * Lookup table for object's dict's
     * @var array<ObjName, array<string, Node>>
     */
    private array $obj_dicts = [];

    /** @var array{'/': DMap, '(': DMap} */
    private array $src_names;
    /** @var array{'/': Set, '(': Set} */
    private array $dest_names;

    /** @var array{0: Node, 1: Node}[] */
    private array $flattened_dest_names;
    private array $delete_if_merged = [];

    /**
     * @param Doc $src
     * @param Doc $dest
     */
    public function __construct(array $src, array $dest)
    {
        $this->src = new ReadonlyDoc($src);
        $this->dest = new Doc($dest);
        $this->src_names = ['/' => new DMap(), '(' => new DMap()];
        $this->dest_names = ['/' => new Set(), '(' => new Set()];
        $this->initDestNames();
    }

    /**
     * tcpdf pdf parser doesn't return the comments nor the parsed PDF version.
     * To be able to rebuild a PDF from the returned AST we also need the header comment.
     */
    public static function parseHeader(string $pdf_string): ?string
    {
        $start = strpos($pdf_string, '%PDF-');

        $in_comment = true;
        for ($i = $start + strlen('%PDF-'); true; $i++) {
            $byte = $pdf_string[$i];
            if ($in_comment) {
                if ($byte === "\r" || $byte === "\n") {
                    $in_comment = false;
                }
            } elseif (ctype_space($byte)) {
                // Continue
            } elseif ($byte === '%') {
                $in_comment = true;
            } else {
                return substr($pdf_string, $start, $i);
            }
        }

        return null;
    }

    /**
     * @return array{'outlineMerged': bool, 'structTreeMerged': bool, 'destsMerged': bool}
     */
    public function copyPages(): array
    {
        $this->copyOnlyPages();
        $struct = $this->mergeStructTree();
        $outline = $this->mergeOutline();
        $destinations = $this->mergeDests();

        return ['outlineMerged' => $outline, 'structTreeMerged' => $outline, 'destsMerged' => $destinations];
    }

    private function initDestNames(): void
    {
        $this->flattened_dest_names = [];

        $names = $this->dest->objGet($this->dest->catalog(), 'Names');
        if ($names === null) {
            return;
        }
        $dests = PdfNode::dict2array($this->dest->unref($names)[PdfNode::VALUE])['Dests'];

        $track_nodes = function (array $node) use ($dests) {
            if ($node[PdfNode::TYPE] === 'objref' && $dests[PdfNode::VALUE] !== $node[PdfNode::VALUE]) {
                $this->delete_if_merged[] = $node[PdfNode::VALUE];
            }
        };

        foreach ($this->dest->iterateNameTreeLeafs($dests, $track_nodes) as $nodes) {
            foreach ($this->iteratePairs($nodes) as $pair) {
                $this->dest_names[$pair[0][PdfNode::TYPE]]->add($pair[0][PdfNode::VALUE]);
                $this->flattened_dest_names[] = $pair;
            }
        }
    }

    private function mergeDests(): bool
    {
        $src_names = $this->src->objGet($this->src->catalog(), 'Names');
        if ($src_names === null) {
            return false;
        }

        $src_dests = PdfNode::dict2array($this->src->unref($src_names)[PdfNode::VALUE])['Dests']; // Type check.
        $add_names = $this->flattened_dest_names;

        foreach ($this->src->iterateNameTreeLeafs($src_dests) as $nodes) {
            foreach ($this->itDict($nodes) as $name => $dest) {
                $new_name = $this->src_names['(']->get($name);
                if ($new_name !== null) {
                    $dest = $this->src->unref($dest);
                    $add_names[] = [['(', $new_name], [$dest[PdfNode::TYPE], array_map(
                        fn($n) => $n[PdfNode::TYPE] === 'objref' ? ['objref', $this->src_dest[$n[PdfNode::VALUE]]] : $n,
                        $dest[PdfNode::VALUE]
                    )]];
                }
            }
        }

        usort($add_names, function ($left, $right) {
            return strcmp($left[0][PdfNode::VALUE], $right[0][PdfNode::VALUE]);
        });

        $new_names = [];
        foreach ($add_names as $pair) {
            $new_names[] = $pair[0];
            $new_names[] = $pair[1];
        }

        $new_tree = ['<<', [['/', 'Names'], ['[', $new_names]]];

        $dest_names = $this->dest->objGet($this->dest->catalog(), 'Names');
        if ($dest_names === null) {
            $this->dest->objSet($this->dest->catalog(), 'Names', ['<<', [['/', 'Dests'], $new_tree]]);
            array_map($this->dest->deleteObj(...), $this->delete_if_merged);
            return true;
        }
        $dn = $this->dest->unref($dest_names);
        $d = PdfNode::dict2array($dn[PdfNode::VALUE])['Dests'];
        if ($d[PdfNode::TYPE] === 'objref') {
            $this->dest->objSetNodes($d[PdfNode::VALUE], [$new_tree]);
        } else {
            $new_tree = ['<<', [['/', 'Dests'], $new_tree]];
            if ($dest_names[PdfNode::TYPE] === 'objref') {
                $this->dest->objSetNodes($dest_names[PdfNode::VALUE], [$new_tree]);
            } else {
                $this->dest->objSet($this->dest->catalog(), 'Names', $new_tree);
            }
        }

        array_map($this->dest->deleteObj(...), $this->delete_if_merged);

        return true;
    }

    /**
     * @return Doc
     */
    public function writeBack(): array
    {
        $this->dest->writeBack();
        return $this->dest->tree();
    }

    /**
     * @param Node[] $nodes
     */
    private function copyNodes(array $nodes): array
    {
        return array_map(function (array $node) {
            if ($node[PdfNode::TYPE] === '<<') {
                $new_nodes = $this->copyNodes($node[PdfNode::VALUE]);
                $new = [];
                foreach ($this->itDict($new_nodes) as $key => $child) {
                    $new[] = ['/', $key];
                    if ($key === 'Dest' && in_array($child[PdfNode::TYPE], ['(', '/'], true)) {
                        $new[] = $this->renameSrcName($child);
                    } else {
                        $new[] = $child;
                    }
                }
                return [$node[PdfNode::TYPE], $new];
            } elseif ($node[PdfNode::TYPE] === '[') {
                return [$node[PdfNode::TYPE], $this->copyNodes($node[PdfNode::VALUE])];
            } elseif ($node[PdfNode::TYPE] === 'objref') {
                return ['objref', $this->copyObj($node[PdfNode::VALUE])];
            }
            return $node;
        }, $nodes);
    }

    /**
     * @param Node $node
     * @return Node
     */
    private function renameSrcName(array $node): array
    {
        $t = $node[PdfNode::TYPE];
        $name = $node[PdfNode::VALUE];
        if ($this->src_names[$t]->has($name)) { // Already renamed.
            return [$t, $this->src_names[$t]->get($name)];
        }
        $new_name = $name;
        for($i = 1; $this->src_names[$t]->hasValue($new_name) || $this->dest_names[$t]->has($new_name); $i++) {
            $new_name = $name . $i;
        }

        $this->src_names[$t]->set($name, $new_name);

        return [$t, $new_name];
    }

    /**
     * Copies $src_name from $this->src to $this->dest and returns the newly created ObjName.
     * ! This also modifies $this->src in the process to avoid copying large array trees from $this->src to $this->dest !
     *
     * @param ObjName $src_name
     * @return ObjName
     */
    private function copyObj(string $src_name): string
    {
        if (!isset($this->src_dest[$src_name])) {
            $this->src_dest[$src_name] = $this->dest->newObj([]); // Prevent infinite loop with dereferencing.
            $new_nodes = $this->copyNodes($this->src->get($src_name));
            $this->dest->objSetNodes($this->src_dest[$src_name], $new_nodes);
        }

        return $this->src_dest[$src_name];
    }

    private function copyOnlyPages(): void
    {
        $pages_name = $this->dest->pages();
        $pages_src = $this->src->pages();
        $this->src_dest[$pages_src] = $pages_name;

        $count = (int) $this->dest->objGet($pages_name, 'Count')[PdfNode::VALUE];
        $pages = $this->dest->objGet($pages_name, 'Kids');

        $src_kids = $this->src->objGet($pages_src, 'Kids')[PdfNode::VALUE];

        foreach ($src_kids as $node) {
            $page = $this->copyObj($node[PdfNode::VALUE]);
            if ($this->dest->objGet($page, 'Type')[PdfNode::VALUE] === 'Pages') {
                $count += (int) $this->dest->objGet($page, 'Count')[PdfNode::VALUE];
            } else {
                $count++;
            }
            // /Nums also contains objects, not just pages. So counting pages is wrong.
            // This correctly updated in updateStructParentTree.
            // Reference to /Nums key but we keep it same as the index itself. (Not real reference).
            // $this->objSet($page, 'StructParents', ['numeric', (string) count($pages[PdfNode::VALUE])]);
            $pages[PdfNode::VALUE][] = ['objref', $page];
        }

        $this->dest->objSet($pages_name, 'Count', ['numeric', (string) $count]);
        $this->dest->objSet($pages_name, 'Kids', $pages);
    }

    private function mergeOutline(): bool
    {
        $dest_root = $this->dest->objGet($this->dest->catalog(), 'Outlines')[PdfNode::VALUE] ?? null;
        $src_root = $this->src->objGet($this->src->catalog(), 'Outlines')[PdfNode::VALUE] ?? null;

        if ($src_root === null) {
            return false;
        }

        if ($dest_root === null) {
            $dest_root = $this->copyObj($src_root);
            $this->dest->objSet($this->dest->catalog(), 'Outlines', ['objref', $dest_root]);
            return true;
        }

        $this->src_dest[$src_root] = $dest_root;
        $dlast = $this->dest->objGet($dest_root, 'Last');

        $sfirst = $this->src->objGet($src_root, 'First');
        $slast = $this->src->objGet($src_root, 'Last');

        if ($sfirst) {
            $f = $this->copyObj($sfirst[PdfNode::VALUE]);
            if ($dlast === null) {
                $this->dest->objSet($dest_root, 'First', ['objref', $f]);
            } else {
                $this->dest->objSet($dlast[PdfNode::VALUE], 'Next', ['objref', $f]);
            }
        }
        if ($slast) {
            $f = $this->copyObj($slast[PdfNode::VALUE]);
            $this->dest->objSet($dest_root, 'Last', ['objref', $f]);
        }

        $count = $this->dest->objGet($dest_root, 'Count')[PdfNode::VALUE] ?? 0;
        $count += $this->src->objGet($src_root, 'Count')[PdfNode::VALUE] ?? 0;
        $this->dest->objSet($dest_root, 'Count', ['numeric', $count]);

        return false;
    }

    private function mergeStructTree(): bool
    {
        $root = $this->dest->findType('StructTreeRoot');
        $other = $this->src->findType('StructTreeRoot');
        $doc = $this->dest->findPair('S', 'Document');
        $other_doc = $this->src->findPair('S', 'Document');
        if (in_array(null, [$root, $other, $doc, $other_doc], true)) {
            return false;
        }
        $this->src_dest[$other_doc] = $doc;

        $this->updateStructKids($doc, $other_doc);
        $this->updateStructParentTree($root, $other);

        return true;
    }

    /**
     * @param ObjName $doc
     * @param ObjName $other_doc
     */
    private function updateStructKids(string $doc, string $other_doc): void
    {
        $dest_kids = $this->dest->objGet($doc, 'K');

        $other_doc_kids = $this->src->objGet($other_doc, 'K');

        foreach ($other_doc_kids[PdfNode::VALUE] as $kid) {
            if ($kid[PdfNode::TYPE] !== 'objref') {
                throw new PdfUniteException('Kid of documnet is not given as a object reference', $kid);
            }
            $dest_kids[PdfNode::VALUE][] = ['objref', $this->copyObj($kid[PdfNode::VALUE])];
        }

        $this->dest->objSet($doc, 'K', $dest_kids);
    }

    private function updateStructParentTree(string $root, string $other): void
    {
        $src_parent_tree = $this->src->objGet($other, 'ParentTree');
        $src_nums = $this->flatten($this->src->iterateNumTreeLeafs($src_parent_tree));
        $dest_parent_tree = $this->dest->objGet($root, 'ParentTree');
        $dest_nums = $this->flatten($this->dest->iterateNumTreeLeafs($dest_parent_tree));

        // get X from [0 a 1 b 2 c ... X g].
        $next_key = (int) $dest_nums[count($dest_nums) - 2][PdfNode::VALUE];
        $next_key++;
        foreach ($this->itDict($src_nums) as $num => $node) {
            $dest_nums[] = ['numeric', (string) $next_key];
            $dest_nums[] = $this->updateNumsRefs($node, $next_key);
            $next_key++;
        }

        if ($dest_parent_tree[PdfNode::TYPE] === '<<') {
            $this->dest->objSet($root, 'ParentTree', ['<<', [['/', 'Nums'], ['[', $dest_nums]]]);
        } elseif ($dest_parent_tree[PdfNode::TYPE] === 'objref') {
            $this->dest->objSetAll($dest_parent_tree[PdfNode::VALUE], ['Nums' => ['[', $dest_nums]]);
        } else {
            throw new PdfUniteException('Invalid ParentTree', $dest_parent_tree);
        }
        $this->dest->objSet($root, 'ParentTreeNextKey', ['numeric', (string) $next_key]);
    }

    /**
     * @param Node $root
     * @param array<ObjName, Node[]> $objs
     * @return Node[]
     */
    private function flatten(\Iterator $src): array
    {
        $ret = [];
        foreach ($src as $val) {
            $ret = array_merge($ret, $val);
        }

        return $ret;
    }

    /**
     * @param Node $nums
     * @return Node
     */
    private function updateNumsRefs(array $nums_objef, int $next_key): array
    {
        $nums = $this->src->unref($nums_objef);
        if ($nums[PdfNode::TYPE] === '[') {
            return ['[', $this->updatePageNumsRef($nums[PdfNode::VALUE], $next_key)];
        } elseif ($nums[PdfNode::TYPE] === '<<') {
            $dest_name = $this->copyObj($nums_objef[PdfNode::VALUE]);
            $this->updateXObjectNumsRef($dest_name, $next_key);
            return ['objref', $dest_name];
        } else {
            throw new PdfUniteException('Invalid Num Tree Value', $nums);
        }
    }

    private function updatePageNumsRef(array $nums, int $next_key): array
    {
        return array_map(function ($node) use ($next_key) {
            if ($node[PdfNode::TYPE] === 'null') {
                return $node;
            }
            if ($node[PdfNode::TYPE] !== 'objref') {
                throw new PdfUniteException('Entry of Nums is not an obj reference', $node);
            }

            if (!isset($this->src_dest[$node[PdfNode::VALUE]])) {
                // throw new PdfUniteException('StructElement not copied before updating struct tree', $node[PdfNode::VALUE]);
                $this->copyObj($node[PdfNode::VALUE]);
            }
            $node[PdfNode::VALUE] = $this->src_dest[$node[PdfNode::VALUE]];
            $page = $this->dest->objGet($node[PdfNode::VALUE], 'Pg');
            $this->dest->objSet($page[PdfNode::VALUE], 'StructParents', ['numeric', (string) $next_key]);
            return $node;
        }, $nums);

    }

    private function updateXObjectNumsRef(string $dest_name, int $next_key): void
    {
        foreach ($this->dest->objGet($dest_name, 'K')[PdfNode::VALUE] as $node) {
            $node = $this->dest->unref($node);
            if ($node[PdfNode::TYPE] === '<<') {
                $d = PdfNode::dict2array($node[PdfNode::VALUE]);
                if (($d['Type'][PdfNode::VALUE] ?? 'StructElem') !== 'StructElem') {
                    $name = $d['Obj'][PdfNode::VALUE];
                    $this->dest->objSet($name, 'StructParent', ['numeric', (string) $next_key]);
                }
            }
        }
    }

    private function itDict($dict): \Iterator
    {
        foreach ($this->iteratePairs($dict) as $pair) {
            yield $pair[0][PdfNode::VALUE] => $pair[1];
        }
    }

    private function iteratePairs(array $array): \Iterator
    {
        for (; current($array) !== false; next($array)) {
            yield [current($array), next($array)];
        }
        reset($array);
    }
}
