<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

class SpreadsheetReader
{
    private const MAX_ROWS = 5000;

    /** @return array<int, array<int, string>> */
    public static function read(string $path, string $extension): array
    {
        return match (strtolower($extension)) {
            'csv', 'txt' => self::readCsv($path),
            'xlsx' => self::readXlsx($path),
            default => throw new RuntimeException('Only CSV and XLSX files are supported.'),
        };
    }

    /** @return array<int, array<int, string>> */
    private static function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('The uploaded file could not be read.');
        }

        $sample = (string) fgets($handle);
        rewind($handle);
        $delimiters = [',' => substr_count($sample, ','), ';' => substr_count($sample, ';'), "\t" => substr_count($sample, "\t")];
        arsort($delimiters);
        $delimiter = (string) array_key_first($delimiters);
        $rows = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($rows) > self::MAX_ROWS) {
                fclose($handle);
                throw new RuntimeException('A maximum of '.self::MAX_ROWS.' attendee rows can be imported at once.');
            }
            $rows[] = array_map(fn ($value) => self::clean((string) $value), $row);
        }
        fclose($handle);

        return self::removeBlankRows($rows);
    }

    /** @return array<int, array<int, string>> */
    private static function readXlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('XLSX import requires the PHP zip extension. CSV files can still be imported.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('The XLSX file is invalid or damaged.');
        }

        try {
            $sharedStrings = self::sharedStrings($zip);
            $sheetPath = null;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $name = $zip->getNameIndex($index);
                if (is_string($name) && preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
                    $sheetPath = $name;
                    break;
                }
            }
            if ($sheetPath === null || ($xml = $zip->getFromName($sheetPath)) === false) {
                throw new RuntimeException('No worksheet was found in the XLSX file.');
            }

            $document = self::xmlDocument($xml);
            $xpath = new DOMXPath($document);
            $rows = [];
            foreach ($xpath->query('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [] as $rowNode) {
                if (count($rows) > self::MAX_ROWS) {
                    throw new RuntimeException('A maximum of '.self::MAX_ROWS.' attendee rows can be imported at once.');
                }
                $row = [];
                foreach ($xpath->query('./*[local-name()="c"]', $rowNode) ?: [] as $cell) {
                    if (! $cell instanceof DOMElement) {
                        continue;
                    }
                    $reference = $cell->getAttribute('r');
                    preg_match('/^[A-Z]+/i', $reference, $match);
                    $column = self::columnIndex($match[0] ?? 'A');
                    $type = $cell->getAttribute('t');
                    $valueNode = $xpath->query('./*[local-name()="v"]', $cell)?->item(0);
                    $value = $valueNode?->textContent ?? '';
                    if ($type === 's') {
                        $value = $sharedStrings[(int) $value] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $parts = [];
                        foreach ($xpath->query('.//*[local-name()="t"]', $cell) ?: [] as $textNode) {
                            $parts[] = $textNode->textContent;
                        }
                        $value = implode('', $parts);
                    }
                    $row[$column] = self::clean($value);
                }
                if ($row !== []) {
                    $max = max(array_keys($row));
                    $rows[] = array_replace(array_fill(0, $max + 1, ''), $row);
                }
            }

            return self::removeBlankRows($rows);
        } finally {
            $zip->close();
        }
    }

    /** @return array<int, string> */
    private static function sharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }
        $document = self::xmlDocument($xml);
        $xpath = new DOMXPath($document);
        $strings = [];
        foreach ($xpath->query('//*[local-name()="si"]') ?: [] as $item) {
            $parts = [];
            foreach ($xpath->query('.//*[local-name()="t"]', $item) ?: [] as $textNode) {
                $parts[] = $textNode->textContent;
            }
            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    private static function xmlDocument(string $xml): DOMDocument
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (! $loaded) {
            throw new RuntimeException('The XLSX worksheet XML is invalid.');
        }

        return $document;
    }

    private static function columnIndex(string $letters): int
    {
        $index = 0;
        foreach (str_split(strtoupper($letters)) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }

    private static function clean(string $value): string
    {
        return trim(preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value);
    }

    /** @param array<int, array<int, string>> $rows */
    private static function removeBlankRows(array $rows): array
    {
        return array_values(array_filter($rows, fn (array $row) => collect($row)->contains(fn ($value) => trim((string) $value) !== '')));
    }
}
