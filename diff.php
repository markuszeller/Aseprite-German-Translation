<?php

$en = loadIniFile('en.ini');
$de = loadIniFile('de.ini');

ob_start();
printDiff(source: $en, target: $de);
file_put_contents('diff-missing-de.ini', ob_get_clean());
function printDiff(array $source, array $target): void
{
    foreach ($source as $section => $keys) {
        if ('' === $section) {
            continue;
        }

        $missingKeys = [];
        foreach ($keys as $key) {
            if (false === isset($target[$section]) || false === in_array($key, $target[$section])) {
                $missingKeys[] = $key;
            }
        }

        if ($missingKeys) {
            echo PHP_EOL;
            printf('[%s]', $section);
            echo PHP_EOL;
            echo implode(PHP_EOL, $missingKeys);
            echo PHP_EOL;
        }
    }
}

function loadIniFile(string $filename): array
{
    return getSectionsAndKeys(removeHereDoc(file_get_contents($filename)));
}

function removeHereDoc(string $string): string
{
    return preg_replace('/<<<(\w+)(.*?)\1/s', '$1', $string);
}

function getSectionsAndKeys(string $string): array
{
    $lines   = explode("\n", $string);
    $result  = [];
    $section = '';
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }
        if (preg_match('/^\[(.*)\]$/', $line, $matches)) {
            $section = $matches[1];
            continue;
        }
        if (preg_match('/^([^=]+)=/', $line, $matches)) {
            $result[$section][] = trim($matches[1]);
        }
    }

    return $result;
}
