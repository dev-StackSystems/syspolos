<?php
declare(strict_types=1);

/**
 * Leitor minimalista de XLSX (zip de XMLs) usando ZipArchive + SimpleXML.
 * Retorna o conteúdo de cada aba como matriz de linhas (apenas texto/valor).
 */
class XlsxReader
{
    /** @return array<string, array<int, array<string, string>>> sheetName => rows => colLetter => value */
    public static function read(string $filePath): array
    {
        if (!is_file($filePath)) {
            throw new RuntimeException('Arquivo não encontrado.');
        }
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException('Não foi possível abrir o XLSX (arquivo corrompido?).');
        }

        // 1) shared strings
        $shared = [];
        $ss = $zip->getFromName('xl/sharedStrings.xml');
        if ($ss !== false) {
            $xml = simplexml_load_string($ss);
            if ($xml !== false) {
                foreach ($xml->si as $si) {
                    // texto simples ou com runs
                    if (isset($si->t)) {
                        $shared[] = (string)$si->t;
                    } else {
                        $buf = '';
                        foreach ($si->r as $r) $buf .= (string)$r->t;
                        $shared[] = $buf;
                    }
                }
            }
        }

        // 2) workbook: lista de abas (name → rId)
        $wb = $zip->getFromName('xl/workbook.xml');
        if ($wb === false) { $zip->close(); throw new RuntimeException('workbook.xml ausente.'); }
        $xmlWb = simplexml_load_string($wb);
        $ns    = $xmlWb->getNamespaces(true);
        $sheets = [];
        foreach ($xmlWb->sheets->sheet as $s) {
            $attrs = $s->attributes();
            $r     = $s->attributes($ns['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $sheets[] = [
                'name' => (string)$attrs['name'],
                'rid'  => (string)$r['id'],
            ];
        }

        // 3) _rels do workbook: rId → target
        $rels = [];
        $rx = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($rx !== false) {
            $xmlR = simplexml_load_string($rx);
            foreach ($xmlR->Relationship as $r) {
                $a = $r->attributes();
                $rels[(string)$a['Id']] = (string)$a['Target'];
            }
        }

        // 4) ler cada sheet
        $result = [];
        foreach ($sheets as $s) {
            $target = $rels[$s['rid']] ?? null;
            if (!$target) continue;
            $path = 'xl/' . ltrim($target, '/');
            $xmlStr = $zip->getFromName($path);
            if ($xmlStr === false) continue;
            $result[$s['name']] = self::parseSheet($xmlStr, $shared);
        }
        $zip->close();
        return $result;
    }

    /** @return array<int, array<string, string>> */
    private static function parseSheet(string $xmlStr, array $shared): array
    {
        $xml = simplexml_load_string($xmlStr);
        if ($xml === false || !isset($xml->sheetData)) return [];
        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rAttr = (int)$row->attributes()['r'];
            $cells = [];
            foreach ($row->c as $c) {
                $ref  = (string)$c->attributes()['r'];
                $type = (string)$c->attributes()['t'];
                $col  = preg_replace('/\d+/', '', $ref);
                $val  = isset($c->v) ? (string)$c->v : '';
                if ($val === '') continue;
                if ($type === 's') {
                    $idx = (int)$val;
                    $val = $shared[$idx] ?? '';
                } elseif ($type === 'inlineStr') {
                    $val = isset($c->is->t) ? (string)$c->is->t : '';
                }
                $cells[$col] = $val;
            }
            if ($cells) $rows[$rAttr] = $cells;
        }
        return $rows;
    }
}
