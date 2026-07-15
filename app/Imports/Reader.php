<?php
namespace App\Imports;

/**
 * Reads tabular data from CSV, XLSX, pasted text, or a public Google Sheet.
 * Returns ['headers' => [...], 'rows' => [[...], ...]]. Pure PHP - the XLSX
 * reader uses ZipArchive + SimpleXML, no external library needed.
 */
class Reader
{
    /** @return array{headers:array,rows:array} */
    public static function read(string $path, string $ext): array
    {
        $ext = strtolower($ext);
        if ($ext === 'xlsx') { return self::xlsx($path); }
        return self::csv($path);
    }

    public static function fromText(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($text));
        $delim = (substr_count($lines[0] ?? '', "\t") >= substr_count($lines[0] ?? '', ',')) ? "\t" : ',';
        $grid = array_map(fn($l) => str_getcsv($l, $delim), $lines);
        return self::split($grid);
    }

    /** Fetch a published/shared Google Sheet as CSV (SSRF-guarded). */
    public static function fromGoogleSheet(string $url): array
    {
        if (!preg_match('#^https://docs\.google\.com/spreadsheets/#', $url)) {
            throw new \RuntimeException('Only https://docs.google.com/spreadsheets/ links are allowed.');
        }
        // Normalise an /edit link to a CSV export.
        if (preg_match('#/d/([a-zA-Z0-9-_]+)#', $url, $m)) {
            $gid = preg_match('#[#&?]gid=(\d+)#', $url, $g) ? $g[1] : '0';
            $url = "https://docs.google.com/spreadsheets/d/{$m[1]}/export?format=csv&gid={$gid}";
        }
        $ctx = stream_context_create(['http' => ['timeout' => 15, 'follow_location' => 1]]);
        $csv = @file_get_contents($url, false, $ctx);
        if ($csv === false) { throw new \RuntimeException('Could not fetch the Google Sheet. Make sure it is shared as "Anyone with the link".'); }
        return self::fromText($csv);
    }

    private static function csv(string $path): array
    {
        $grid = [];
        if (($fh = fopen($path, 'r')) !== false) {
            while (($r = fgetcsv($fh)) !== false) { $grid[] = $r; }
            fclose($fh);
        }
        return self::split($grid);
    }

    private static function xlsx(string $path): array
    {
        if (!class_exists('ZipArchive')) { throw new \RuntimeException('XLSX needs the PHP zip extension. Please upload a CSV instead.'); }
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) { throw new \RuntimeException('Could not open the Excel file.'); }

        $shared = [];
        if (($s = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $xml = simplexml_load_string($s);
            foreach ($xml->si as $si) {
                $text = '';
                if (isset($si->t)) { $text = (string) $si->t; }
                foreach ($si->r as $r) { $text .= (string) $r->t; }
                $shared[] = $text;
            }
        }
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            // find the first worksheet
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $n = $zip->getNameIndex($i);
                if (preg_match('#xl/worksheets/sheet\d+\.xml$#', $n)) { $sheetXml = $zip->getFromName($n); break; }
            }
        }
        $zip->close();
        if (!$sheetXml) { throw new \RuntimeException('No worksheet found in the Excel file.'); }

        $xml = simplexml_load_string($sheetXml);
        $grid = [];
        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            $max = 0;
            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                $col = self::colIndex(preg_replace('/\d+/', '', $ref));
                $v = isset($c->v) ? (string) $c->v : '';
                if ((string) $c['t'] === 's') { $v = $shared[(int) $v] ?? ''; }
                $cells[$col] = $v;
                $max = max($max, $col);
            }
            $line = [];
            for ($i = 0; $i <= $max; $i++) { $line[] = $cells[$i] ?? ''; }
            $grid[] = $line;
        }
        return self::split($grid);
    }

    private static function colIndex(string $letters): int
    {
        $n = 0;
        foreach (str_split($letters) as $ch) { $n = $n * 26 + (ord(strtoupper($ch)) - 64); }
        return $n - 1;
    }

    /** First non-empty row = headers, rest = data rows. */
    private static function split(array $grid): array
    {
        $grid = array_values(array_filter($grid, fn($r) => count(array_filter($r, fn($v) => trim((string) $v) !== '')) > 0));
        if (!$grid) { return ['headers' => [], 'rows' => []]; }
        $headers = array_map(fn($h) => trim((string) $h), array_shift($grid));
        return ['headers' => $headers, 'rows' => $grid];
    }
}
