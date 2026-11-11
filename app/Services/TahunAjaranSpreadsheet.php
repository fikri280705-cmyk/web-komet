<?php
/**
 * Import and template support for academic-year setup spreadsheets.
 */

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;
use ZipArchive;
use DOMDocument;
use DOMXPath;

class TahunAjaranSpreadsheet {
    public const COLUMNS = [
        'tipe_data',
        'semester',
        'nama_kelas',
        'kode_jurusan',
        'tingkat',
        'kode_mapel',
        'pertemuan_per_minggu',
        'nuptk',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'ruangan',
        'apakah_lab'
    ];

    public function baca(string $path, string $extension): array {
        if ($extension === 'csv') {
            return $this->bacaCsv($path);
        }
        if ($extension === 'xlsx') {
            return $this->bacaXlsx($path);
        }
        throw new InvalidArgumentException('Format berkas harus XLSX atau CSV.');
    }

    public function buatTemplate(string $path): void {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP ZipArchive diperlukan untuk membuat template XLSX.');
        }
        if (file_exists($path)) {
            throw new RuntimeException('Lokasi tujuan template harus berupa berkas baru.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE) !== true) {
            throw new RuntimeException('Template XLSX gagal dibuat.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelationshipsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->dataSheetXml());
        $zip->addFromString('xl/worksheets/sheet2.xml', $this->instructionsSheetXml());

        if (!$zip->close()) {
            throw new RuntimeException('Template XLSX gagal diselesaikan.');
        }
    }

    private function bacaCsv(string $path): array {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new InvalidArgumentException('Berkas CSV tidak dapat dibaca.');
        }

        try {
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                throw new InvalidArgumentException('Berkas CSV kosong.');
            }
            $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
            $delimiter = $this->deteksiDelimiter($firstLine);
            rewind($handle);

            $rows = [];
            $headers = null;
            $line = 0;
            while (($cells = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
                $line++;
                if ($line === 1 && isset($cells[0])) {
                    $cells[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$cells[0]);
                }
                if ($this->barisKosong($cells)) {
                    continue;
                }
                if ($headers === null) {
                    $headers = $this->validasiHeader($cells);
                    continue;
                }
                if (count($rows) >= 5000) {
                    throw new InvalidArgumentException('Maksimal 5.000 baris data dapat diimpor sekaligus.');
                }
                $rows[] = $this->barisAsosiatif($headers, $cells, $line);
            }
            if ($headers === null) {
                throw new InvalidArgumentException('Header kolom pada berkas CSV tidak ditemukan.');
            }
            if ($rows === []) {
                throw new InvalidArgumentException('Berkas tidak memiliki baris data untuk diimpor.');
            }
            return $rows;
        } finally {
            fclose($handle);
        }
    }

    private function bacaXlsx(string $path): array {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP ZipArchive diperlukan untuk membaca XLSX.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new InvalidArgumentException('Berkas XLSX tidak valid atau rusak.');
        }

        try {
            $ukuranTotal = 0;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index);
                $ukuran = (int)($stat['size'] ?? 0);
                $ukuranTotal += $ukuran;
                if ($ukuran > 8 * 1024 * 1024 || $ukuranTotal > 20 * 1024 * 1024) {
                    throw new InvalidArgumentException('Isi XLSX terlalu besar untuk diproses.');
                }
            }

            $workbook = $this->bacaXmlZip($zip, 'xl/workbook.xml');
            $relationships = $this->bacaXmlZip($zip, 'xl/_rels/workbook.xml.rels');
            $sheetPath = $this->pathSheetDataImpor($workbook, $relationships);
            $worksheet = $this->bacaXmlZip($zip, $sheetPath);
            $sharedStrings = $this->bacaSharedStrings($zip);
            return $this->ekstrakBarisWorksheet($worksheet, $sharedStrings);
        } finally {
            $zip->close();
        }
    }

    private function deteksiDelimiter(string $line): string {
        $counts = [
            ',' => substr_count($line, ','),
            ';' => substr_count($line, ';'),
            "\t" => substr_count($line, "\t")
        ];
        arsort($counts);
        return (string)array_key_first($counts);
    }

    private function validasiHeader(array $cells): array {
        $headers = array_map(
            static fn($header): string => strtolower(trim((string)$header)),
            $cells
        );
        if ($headers !== self::COLUMNS) {
            throw new InvalidArgumentException(
                'Header tidak sesuai template. Gunakan berkas template dan jangan mengubah nama atau urutan kolom.'
            );
        }
        return $headers;
    }

    private function barisAsosiatif(array $headers, array $cells, int $line): array {
        if (count($cells) > count($headers)) {
            foreach (array_slice($cells, count($headers)) as $extraCell) {
                if (trim((string)$extraCell) !== '') {
                    throw new InvalidArgumentException("Baris {$line} memiliki data di luar kolom template.");
                }
            }
        }
        $cells = array_pad(array_slice($cells, 0, count($headers)), count($headers), '');
        $row = [];
        foreach ($headers as $index => $header) {
            $row[$header] = trim((string)$cells[$index]);
        }
        $row['_baris'] = $line;
        return $row;
    }

    private function barisKosong(array $cells): bool {
        foreach ($cells as $cell) {
            if (trim((string)$cell) !== '') {
                return false;
            }
        }
        return true;
    }

    private function bacaXmlZip(ZipArchive $zip, string $path): DOMDocument {
        $xml = $zip->getFromName($path);
        if ($xml === false) {
            throw new InvalidArgumentException("Struktur XLSX tidak lengkap ({$path}).");
        }
        $document = new DOMDocument();
        $document->resolveExternals = false;
        $document->substituteEntities = false;
        if (!$document->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS)) {
            throw new InvalidArgumentException('Struktur XML di dalam XLSX tidak valid.');
        }
        return $document;
    }

    private function pathSheetDataImpor(DOMDocument $workbook, DOMDocument $relationships): string {
        $workbookXPath = new DOMXPath($workbook);
        $workbookXPath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbookXPath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $sheet = $workbookXPath->query('//m:sheets/m:sheet[@name="Data Impor"]')->item(0);
        if (!$sheet) {
            throw new InvalidArgumentException('Sheet "Data Impor" tidak ditemukan di dalam XLSX.');
        }
        $relationshipId = $sheet->getAttributeNS(
            'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
            'id'
        );

        $relationshipXPath = new DOMXPath($relationships);
        $relationshipXPath->registerNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');
        $relationship = $relationshipXPath->query('//r:Relationship[@Id="' . $relationshipId . '"]')->item(0);
        if (!$relationship) {
            throw new InvalidArgumentException('Referensi sheet "Data Impor" tidak valid.');
        }
        $target = str_replace('\\', '/', $relationship->getAttribute('Target'));
        if (strpos($target, '/') === 0) {
            $target = ltrim($target, '/');
        } else {
            $target = 'xl/' . $target;
        }
        $parts = [];
        foreach (explode('/', $target) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($parts);
                continue;
            }
            $parts[] = $part;
        }
        $normalized = implode('/', $parts);
        if (strpos($normalized, 'xl/worksheets/') !== 0 || substr($normalized, -4) !== '.xml') {
            throw new InvalidArgumentException('Lokasi sheet impor XLSX tidak valid.');
        }
        return $normalized;
    }

    private function bacaSharedStrings(ZipArchive $zip): array {
        if ($zip->locateName('xl/sharedStrings.xml') === false) {
            return [];
        }
        $document = $this->bacaXmlZip($zip, 'xl/sharedStrings.xml');
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $values = [];
        foreach ($xpath->query('//m:si') as $item) {
            $text = '';
            foreach ($xpath->query('.//m:t', $item) as $textNode) {
                $text .= $textNode->textContent;
            }
            $values[] = $text;
        }
        return $values;
    }

    private function ekstrakBarisWorksheet(DOMDocument $worksheet, array $sharedStrings): array {
        $xpath = new DOMXPath($worksheet);
        $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $headers = null;
        $rows = [];
        foreach ($xpath->query('//m:sheetData/m:row') as $rowNode) {
            $line = (int)$rowNode->getAttribute('r');
            $cells = [];
            foreach ($xpath->query('./m:c', $rowNode) as $cellNode) {
                if ($xpath->query('./m:f', $cellNode)->length > 0) {
                    throw new InvalidArgumentException("Rumus tidak diizinkan pada XLSX (baris {$line}). Isi nilai tetap.");
                }
                $reference = $cellNode->getAttribute('r');
                if (!preg_match('/^([A-Z]+)/', $reference, $matches)) {
                    throw new InvalidArgumentException("Referensi sel XLSX tidak valid pada baris {$line}.");
                }
                $column = $this->indeksKolom($matches[1]);
                $valueNode = $xpath->query('./m:v', $cellNode)->item(0);
                $inlineNode = $xpath->query('./m:is//m:t', $cellNode);
                if ($cellNode->getAttribute('t') === 'inlineStr') {
                    $value = '';
                    foreach ($inlineNode as $textNode) {
                        $value .= $textNode->textContent;
                    }
                } elseif ($valueNode) {
                    $value = $valueNode->textContent;
                    if ($cellNode->getAttribute('t') === 's') {
                        $index = (int)$value;
                        if (!array_key_exists($index, $sharedStrings)) {
                            throw new InvalidArgumentException("Indeks shared string XLSX tidak valid pada baris {$line}.");
                        }
                        $value = $sharedStrings[$index];
                    }
                } else {
                    $value = '';
                }
                if (in_array($column, [9, 10], true) && is_numeric($value)
                    && (float)$value >= 0 && (float)$value < 1
                ) {
                    $seconds = (int)round((float)$value * 86400) % 86400;
                    $value = sprintf('%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
                }
                $cells[$column] = trim((string)$value);
            }
            if ($cells === [] || $this->barisKosong($cells)) {
                continue;
            }
            ksort($cells);
            $maxColumn = max(array_keys($cells));
            for ($column = 0; $column <= $maxColumn; $column++) {
                $cells[$column] = $cells[$column] ?? '';
            }
            ksort($cells);
            $cells = array_values($cells);
            if ($headers === null) {
                $headers = $this->validasiHeader($cells);
                continue;
            }
            if (count($rows) >= 5000) {
                throw new InvalidArgumentException('Maksimal 5.000 baris data dapat diimpor sekaligus.');
            }
            $rows[] = $this->barisAsosiatif($headers, $cells, $line);
        }
        if ($headers === null) {
            throw new InvalidArgumentException('Header kolom sheet "Data Impor" tidak ditemukan.');
        }
        if ($rows === []) {
            throw new InvalidArgumentException('Sheet "Data Impor" tidak memiliki baris data untuk diimpor.');
        }
        return $rows;
    }

    private function indeksKolom(string $letters): int {
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - ord('A') + 1);
        }
        return $index - 1;
    }

    private function contentTypesXml(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function rootRelationshipsXml(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbookXml(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Data Impor" sheetId="1" r:id="rId1"/><sheet name="Petunjuk" sheetId="2" r:id="rId2"/></sheets>'
            . '</workbook>';
    }

    private function workbookRelationshipsXml(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function stylesXml(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1D4ED8"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right><top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf><xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/><xf numFmtId="20" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function dataSheetXml(): string {
        $widths = [18, 14, 20, 18, 12, 20, 24, 22, 14, 14, 14, 22, 14];
        $columns = '';
        foreach ($widths as $index => $width) {
            $style = $index === 7 ? ' style="2"' : (in_array($index, [9, 10], true) ? ' style="3"' : '');
            $columns .= '<col min="' . ($index + 1) . '" max="' . ($index + 1) . '" width="' . $width . '" customWidth="1"' . $style . '/>';
        }
        $cells = '';
        foreach (self::COLUMNS as $index => $header) {
            $cell = $this->hurufKolom($index + 1) . '1';
            $cells .= '<c r="' . $cell . '" s="1" t="inlineStr"><is><t>' . $this->xml($header) . '</t></is></c>';
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="A1:M1"/>'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="18"/><cols>' . $columns . '</cols>'
            . '<sheetData><row r="1" ht="30" customHeight="1">' . $cells . '</row></sheetData>'
            . '<autoFilter ref="A1:M1"/></worksheet>';
    }

    private function instructionsSheetXml(): string {
        $instructions = [
            ['Template Impor Persiapan Tahun Ajaran'],
            ['Isi baris pada sheet Data Impor. Jangan mengubah urutan atau nama header. Satu baris mewakili satu data.'],
            ['Nilai tipe_data: wali_kelas, kurikulum, pengajar, atau jadwal.'],
            ['wali_kelas: isi nama_kelas dan nuptk. Satu guru hanya boleh menjadi wali satu kelas dalam satu tahun ajaran.'],
            ['kurikulum: isi semester, kode_jurusan, tingkat, kode_mapel, dan pertemuan_per_minggu (1-25).'],
            ['pengajar: isi semester, nama_kelas, kode_mapel, dan nuptk. Kurikulum mapel terkait harus tersedia.'],
            ['jadwal: isi semester, nama_kelas, kode_mapel, hari, jam_mulai, jam_selesai, ruangan, apakah_lab (0 atau 1). Pengajar harus sudah ditetapkan.'],
            ['Semester harus ganjil atau genap. Hari harus Senin, Selasa, Rabu, Kamis, atau Jumat. Format jam HH:MM.'],
            ['Gunakan kode kelas, kode jurusan, kode mapel, dan NUPTK yang sudah ada pada sistem.'],
            ['Simpan NUPTK sebagai teks agar angka nol di awal tidak hilang.'],
            ['Baris yang sudah ada akan diperbarui untuk wali, kurikulum, dan pengajar. Jadwal yang bentrok menggagalkan seluruh impor.'],
            ['Impor bersifat atomik: jika ada kesalahan, tidak ada baris yang disimpan. Maksimal 5.000 baris.'],
            ['Untuk CSV, gunakan header yang sama dengan sheet Data Impor pada template ini.']
        ];
        $rows = '';
        foreach ($instructions as $index => $instruction) {
            $number = $index + 1;
            $style = $index === 0 ? ' s="1"' : '';
            $height = $index === 0 ? ' ht="28" customHeight="1"' : '';
            $rows .= '<row r="' . $number . '"' . $height . '><c r="A' . $number . '"' . $style . ' t="inlineStr"><is><t>' . $this->xml($instruction[0]) . '</t></is></c></row>';
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="A1:A' . count($instructions) . '"/>'
            . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="22"/><cols><col min="1" max="1" width="115" customWidth="1"/></cols>'
            . '<sheetData>' . $rows . '</sheetData></worksheet>';
    }

    private function hurufKolom(int $index): string {
        $letters = '';
        while ($index > 0) {
            $index--;
            $letters = chr(($index % 26) + ord('A')) . $letters;
            $index = intdiv($index, 26);
        }
        return $letters;
    }

    private function xml(string $value): string {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
