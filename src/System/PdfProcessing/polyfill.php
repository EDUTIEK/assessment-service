<?php declare(strict_types=1);

/**
 * @template K
 * @template V
 * @template X
 *
 * @param array<K, V> $array
 * @param callable(V): bool $predicate
 * @param callable(array{K: V}): X $select
 * @return null|X
 */
function array_find_pair(array $array, callable $predicate, callable $select)
{
    foreach ($array as $k => $v) {
        if ($predicate($v)) {
            return $select([$k => $v]);
        }
    }
    return null;
}

if (!function_exists('array_find')) {
    /**
     * @template A
     * @param A[] $array
     * @param callable(A): bool $predicate
     * @return null|A
     */
    function array_find(array $array, callable $predicate)
    {
        return array_find_pair($array, $predicate, 'current');

    }
}
if (!function_exists('array_find_key')) {
    /**
     * @template K
     * @template V
     * @param array<K, V> $array
     * @param callable(V): bool $predicate
     * @return null|K
     */
    function array_find_key(array $array, callable $predicate)
    {
        return array_find_pair($array, $predicate, 'key');
    }
}
