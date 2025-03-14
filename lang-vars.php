#!/usr/bin/env php
<?php declare(strict_types=1);

if (php_sapi_name() === 'cli') {
    main();
}

function main(): void
{
    if (in_array(arg(1, ''), ['', 'help'], true) || flag('--help') | flag('-h')) {
        help(STDOUT);
        return;
    }

    $in = option('-in');
    $out = option('-out');
    $csv_options = [nice_separator(option('-s', ',')), option('-e', '"')];

    $ins = [
        'php' => fn($f) => require $f,
        'csv' => fn($f) => with_read_file($f, fn($f) => read_csv($f, ...$csv_options)),
        'json' => fn($f) => with_read_file($f, fn($f) => json_decode(stream_get_contents($f), true)),
    ];

    $outs = [
        'php' => 'export',
        'csv' => fn($f, $d) => write_csv($f, $d, ...$csv_options),
        'json' => fn($f, $d) => fwrite($f, json_encode($d)),
    ];

    $data = $ins[$in](arg(1, '-'));
    with_write_file(arg(2, '-'), fn($f) => $outs[$out]($f, $data));
}

function help($file): void
{
    fprintf($file, "Usage:\n%s -in php|csv|json -out php|csv|json [SRC-FILE|-] [DEST-FILE|-] [-s CSV-SEPARATOR] [-e CSV-ENCLOSURE]\n", arg(0));
}

function read_csv($file, string $separator, string $enclosure): array
{
    $header = fgetcsv($file, null, $separator, $enclosure, '');
    $id = key(array_filter($header, fn($v) => $v === 'id'));
    $by_lang = [];
    while (($row = fgetcsv($file, null, $separator, $enclosure, ''))) {
        $id_field = $row[$id];
        foreach ($row as $i => $val){
            if ($i !== $id) {
                $by_lang[$header[$i]][$id_field] = $val;
            }
        }
    }

    return $by_lang;
}

function write_csv($file, array $data, string $separator, string $enclosure): void
{
    $langs = array_keys($data);
    fputcsv($file, array_merge(['id'], $langs), $separator, $enclosure, '');

    $ids = array_unique(array_merge(...array_values(array_map('array_keys', $data))));
    foreach ($ids as $id) {
        fputcsv(
            $file,
            array_merge([$id], array_map(fn($lang) => $data[$lang][$id] ?? '', $langs)),
            $separator,
            $enclosure,
            ''
        );
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

function nice_separator(string $s): string
{
    return $s === '\t' ? "\t" : substr($s, 0, 1);
}

function error(string $message): void
{
    fprintf(STDERR, 'Error: %s' . PHP_EOL, $message);
    help(STDERR);
    exit(1);
}

function arg(int $nr, ?string $default = null): string
{
    $args = [];
    for ($i = 0; $i < count($_SERVER['argv']); $i++) {
        if (preg_match('/^-./', $_SERVER['argv'][$i])) {
            $i++;
        } else {
            $args[] = $_SERVER['argv'][$i];
        }
    }

    return $args[$nr] ?? $default ?? error('Missing argument');
}

function flag(string $flag): bool
{
    return in_array($flag, $_SERVER['argv'], true);
}

function option(string $option, ?string $default = null)
{
    $prev = null;
    foreach ($_SERVER['argv'] as $current) {
        if ($prev === $option) {
            return $current;
        }
        $prev = $current;
    }

    return $default ?? error('Missing option: ' . $option);
}
