<?php

namespace App\Support;

use DateTimeInterface;
use RuntimeException;
use ZipArchive;

final class XlsxExport
{
    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, int>  $dateColumns  Zero-based column indexes.
     * @param  array<int, int>  $numberColumns  Zero-based column indexes.
     */
    public static function create(array $rows, array $dateColumns = [], array $numberColumns = [], string $sheetName = 'Export'): string
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx-export-');
        if ($path === false) {
            throw new RuntimeException('Unable to create the Excel export.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($path);
            throw new RuntimeException('Unable to open the Excel export.');
        }

        $sheetName = mb_substr(preg_replace('/[\\\\\/?*\[\]:]/', '', $sheetName) ?: 'Export', 0, 31);
        $columnCount = max(array_map('count', $rows) ?: [1]);
        $rowCount = max(count($rows), 1);
        $lastCell = self::columnName($columnCount).'1';
        $dimension = 'A1:'.self::columnName($columnCount).$rowCount;

        $zip->addFromString('[Content_Types].xml', self::contentTypes());
        $zip->addFromString('_rels/.rels', self::rootRelationships());
        $zip->addFromString('docProps/app.xml', self::appProperties());
        $zip->addFromString('docProps/core.xml', self::coreProperties());
        $zip->addFromString('xl/workbook.xml', self::workbook($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRelationships());
        $zip->addFromString('xl/styles.xml', self::styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::sheet($rows, $dateColumns, $numberColumns, $dimension, $lastCell, $columnCount));
        $zip->close();

        return $path;
    }

    private static function sheet(array $rows, array $dateColumns, array $numberColumns, string $dimension, string $lastCell, int $columnCount): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<dimension ref="'.$dimension.'"/><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
        $xml .= '<cols>';
        for ($column = 1; $column <= $columnCount; $column++) {
            $width = in_array($column - 1, $dateColumns, true) ? 21 : ($column <= 4 ? 24 : 18);
            $xml .= '<col min="'.$column.'" max="'.$column.'" width="'.$width.'" customWidth="1"/>';
        }
        $xml .= '</cols><sheetData>';

        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 1;
            $xml .= '<row r="'.$excelRow.'">';
            foreach ($row as $columnIndex => $value) {
                $reference = self::columnName($columnIndex + 1).$excelRow;
                if ($rowIndex === 0) {
                    $xml .= self::inlineCell($reference, (string) $value, 1);
                } elseif (in_array($columnIndex, $dateColumns, true) && $value instanceof DateTimeInterface) {
                    $xml .= '<c r="'.$reference.'" s="2"><v>'.self::excelDate($value).'</v></c>';
                } elseif (in_array($columnIndex, $numberColumns, true) && is_numeric($value)) {
                    $xml .= '<c r="'.$reference.'" s="3"><v>'.(0 + $value).'</v></c>';
                } else {
                    $xml .= self::inlineCell($reference, $value === null ? '' : (string) $value);
                }
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData><autoFilter ref="A1:'.$lastCell.'"/></worksheet>';

        return $xml;
    }

    private static function inlineCell(string $reference, string $value, int $style = 0): string
    {
        $value = htmlspecialchars(self::validXml($value), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $preserve = preg_match('/^\s|\s$/u', $value) ? ' xml:space="preserve"' : '';

        return '<c r="'.$reference.'" s="'.$style.'" t="inlineStr"><is><t'.$preserve.'>'.$value.'</t></is></c>';
    }

    private static function validXml(string $value): string
    {
        return preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $value) ?? '';
    }

    private static function excelDate(DateTimeInterface $value): string
    {
        $seconds = gmmktime(
            (int) $value->format('H'),
            (int) $value->format('i'),
            (int) $value->format('s'),
            (int) $value->format('m'),
            (int) $value->format('d'),
            (int) $value->format('Y')
        );

        return rtrim(rtrim(number_format(($seconds / 86400) + 25569, 10, '.', ''), '0'), '.');
    }

    private static function columnName(int $column): string
    {
        $name = '';
        while ($column > 0) {
            $column--;
            $name = chr(65 + ($column % 26)).$name;
            $column = intdiv($column, 26);
        }

        return $name;
    }

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/></Types>';
    }

    private static function rootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/></Relationships>';
    }

    private static function workbook(string $sheetName): string
    {
        $sheetName = htmlspecialchars($sheetName, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="'.$sheetName.'" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private static function workbookRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    }

    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><numFmts count="1"><numFmt numFmtId="164" formatCode="yyyy-mm-dd hh:mm:ss"/></numFmts><fonts count="2"><font><sz val="11"/><name val="Calibri"/><family val="2"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/><family val="2"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1E3A5F"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment vertical="center"/></xf><xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/><xf numFmtId="1" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    private static function coreProperties(): string
    {
        $now = gmdate('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:creator>Webinar Platform</dc:creator><cp:lastModifiedBy>Webinar Platform</cp:lastModifiedBy><dcterms:created xsi:type="dcterms:W3CDTF">'.$now.'</dcterms:created><dcterms:modified xsi:type="dcterms:W3CDTF">'.$now.'</dcterms:modified></cp:coreProperties>';
    }

    private static function appProperties(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>Webinar Platform</Application></Properties>';
    }
}
