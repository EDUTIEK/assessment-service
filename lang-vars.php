#!/usr/bin/env php
<?php declare(strict_types=1);

if (php_sapi_name() === 'cli') {
    main();
}

function main(): void
{
    if (in_array(arg(1, ''), ['', 'help', '--help', '-h'], true)) {
        help(STDOUT);
    } else if (arg(1) === 'from-csv') {
        $data = with_read_file(arg(2, '-'), 'read_csv');
        with_write_file(arg(3, '-'), fn($f) => export($f, $data));
    } else if (arg(1) === 'to-csv') {
        $data = require arg(2);
        with_write_file(arg(3, '-'), fn($f) => write_csv($f, $data));
    } else {
        help(STDERR);
    }
}

function help($file): void
{
    fprintf($file, "Usage:\n%s from-csv [SRC-FILE.csv|-] [DEST-FILE.php|-]\n%s to-csv SRC-FILE.php [DEST-FILE.csv|-]\n", arg(0), arg(0));
}

function read_csv($file): array
{
    $header = fgetcsv($file);
    $id = key(array_filter($header, fn($v) => $v === 'id'));
    $by_lang = [];
    while (($row = fgetcsv($file))) {
        if(!isset($row[$id])){var_dump($row);exit;}
        $id_field = $row[$id];
        foreach ($row as $i => $val){
            if ($i !== $id) {
                $by_lang[$header[$i]][$id_field] = $val;
            }
        }
    }

    return $by_lang;
}

function write_csv($file, array $data): void
{
    $langs = array_keys($data);
    fputcsv($file, array_merge(['id'], $langs));

    $ids = array_unique(array_merge(...array_values(array_map('array_keys', $data))));
    foreach ($ids as $id) {
        fputcsv($file, array_merge([$id], array_map(fn($lang) => $data[$lang][$id] ?? '', $langs)));
    }
}

function export($file, array $data): void
{
    fwrite($file, '<?php return ' . var_export($data, true) . ';' . PHP_EOL);
}

function with_read_file(string $filename, callable $proc)
{
    $file = $filename === '-' ? STDIN : fopen($filename, 'rb');
    if(!$file){
        error('Cannot open file "' . $filename . '" for reading.');
    }

    $ret = $proc($file);

    if ($file !== STDIN) {
        fclose($file);
    }

    return $ret;
}

function with_write_file(string $filename, callable $proc)
{
    $file = $filename === '-' ? STDOUT : fopen($filename, 'wb');
    if(!$file){
        error('Cannot open file "' . $filename . '" for writing.');
    }

    $ret = $proc($file);

    if ($file !== STDOUT) {
        fclose($file);
    }

    return $ret;
}

function error(string $message): void
{
    fprintf(STDERR, 'Error: %s', $message);
    help(STDERR);
    exit(1);
}

function arg(int $nr, ?string $default = null): string
{
    return $_SERVER['argv'][$nr] ?? $default ?? error('Missing argument');
}
