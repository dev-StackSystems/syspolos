<?php
declare(strict_types=1);

/**
 * Interpreta as abas POLO X do arquivo "AUDIÊNCIA DE LEITURA.xlsx".
 * Cada aba contém N fichas (blocos) com identificação + critérios + conclusão.
 * O layout do arquivo coloca o rótulo e o valor juntos na mesma célula
 * (ex: "ESCOLA: Antonio Pinheiro"), então usamos regex sobre o texto.
 */
class XlsxParser
{
    public static function parsear(array $sheets): array
    {
        $polos = [];
        foreach ($sheets as $sheetName => $rows) {
            $nomePolo = trim($sheetName);
            if ($nomePolo === '') continue;

            $fichas = self::extrairFichas($rows);
            $polos[] = [
                'polo_nome' => $nomePolo,
                'fichas'    => $fichas,
            ];
        }
        return $polos;
    }

    /**
     * Extrai fichas (blocos) de uma aba.
     * Um bloco começa quando encontra a linha-cabeçalho "AUDIÊNCIA DE LEITURA"
     * OU quando encontra um "ESCOLA:" e não há bloco aberto.
     */
    private static function extrairFichas(array $rows): array
    {
        $fichas   = [];
        $atual    = null;
        $modoConcl = false;
        $conclBuf  = '';

        foreach ($rows as $cells) {
            $linha = trim((string)($cells['A'] ?? ''));
            if ($linha === '') continue;

            $lnorm = mb_strtoupper(self::removeAcentos($linha), 'UTF-8');

            // início de nova ficha
            if (str_starts_with($lnorm, 'AUDIENCIA DE LEITURA')) {
                if ($atual) {
                    if ($modoConcl) $atual['txt_conclusao'] = trim($conclBuf);
                    $fichas[] = $atual;
                }
                $atual = self::novaFicha();
                $modoConcl = false; $conclBuf = '';
                continue;
            }

            if (!$atual) $atual = self::novaFicha();

            // fim: conclusão (texto livre até próximo bloco)
            if ($modoConcl) {
                $conclBuf .= ($conclBuf ? "\n" : '') . $linha;
                continue;
            }

            // campos de identificação
            if (preg_match('/^ESCOLA\s*:\s*(.+)$/iu', $linha, $m))            { $atual['escola_nome']   = trim($m[1]); continue; }
            if (preg_match('/^LOCALIDADE\s*:\s*(.+)$/iu', $linha, $m))        { $atual['localidade']    = trim($m[1]); continue; }
            if (preg_match('/^DIRETOR\s*:\s*(.+)$/iu', $linha, $m))           { $atual['diretor']       = trim($m[1]); continue; }
            if (preg_match('/^COORDENADOR\s*:\s*(.+)$/iu', $linha, $m))       { $atual['coordenador']   = trim($m[1]); continue; }
            if (preg_match('/^DATA DE REALIZA[CÇ][AÃ]O\s*:\s*(.+)$/iu', $linha, $m)) { $atual['dat_realizacao'] = self::parseDataBr(trim($m[1])); continue; }
            if (preg_match('/^TURNO\s*:\s*(.+)$/iu', $linha, $m))             { $atual['ies_turno']     = self::normalizaTurno(trim($m[1])); continue; }
            if (preg_match('/^TURMA\s*:\s*(.+)$/iu', $linha, $m))             { $atual['turma']         = trim($m[1]); continue; }
            if (preg_match('/QUANTIDADE DE ALUNOS\s*:\s*(\d+)?.*PCD\s*:\s*(\d+)?/iu', $linha, $m)) {
                $atual['qtd_alunos'] = (int)($m[1] ?? 0);
                $atual['qtd_pcd']    = (int)($m[2] ?? 0);
                continue;
            }
            if (preg_match('/^T[EÉ]CNICO RESPONS[AÁ]VEL\s*:\s*(.+)$/iu', $linha, $m)) { $atual['tecnico_responsavel'] = trim($m[1]); continue; }

            // critérios de leitura (só label, valores geralmente em branco no template)
            if (preg_match('/^LEITURA DE TEXTO COM FLU[EÊ]NCIA\s*:\s*(\d+)?/iu', $linha, $m))   { $atual['lei_fluencia']      = (int)($m[1] ?? 0); continue; }
            if (preg_match('/^LEITURA DE TEXTO SEM FLU[EÊ]NCIA\s*:\s*(\d+)?/iu', $linha, $m))   { $atual['lei_sem_fluencia']  = (int)($m[1] ?? 0); continue; }
            if (preg_match('/^LEITURA DE FRASES\s*:\s*(\d+)?/iu', $linha, $m))                   { $atual['lei_frases']        = (int)($m[1] ?? 0); continue; }
            if (preg_match('/^LEITURA DE PALAVRAS\s*:\s*(\d+)?/iu', $linha, $m))                 { $atual['lei_palavras']      = (int)($m[1] ?? 0); continue; }
            if (preg_match('/^LEITURA DE S[IÍ]LABAS\s*:\s*(\d+)?/iu', $linha, $m))               { $atual['lei_silabas']       = (int)($m[1] ?? 0); continue; }
            if (preg_match('/^N[AÃ]O LEITOR\s*:\s*(\d+)?/iu', $linha, $m))                       { $atual['lei_nao_leitor']    = (int)($m[1] ?? 0); continue; }

            // critérios de escrita
            if (preg_match('/^ORTOGR[AÁ]FICO\s*:\s*(\d+)?/iu', $linha, $m))                      { $atual['esc_ortografico']         = (int)($m[1] ?? 0); continue; }
            if (preg_match('/^ALFAB[EÉ]TICO\s*:\s*(\d+)?/iu', $linha, $m))                       { $atual['esc_alfabetico']          = (int)($m[1] ?? 0); continue; }
            if (preg_match('/^SIL[AÁ]BICO-ALFAB[EÉ]TICO\s*:\s*(\d+)?/iu', $linha, $m))           { $atual['esc_silabico_alfabetico'] = (int)($m[1] ?? 0); continue; }
            if (preg_match('/^SIL[AÁ]BICO\s*:\s*(\d+)?/iu', $linha, $m))                         { $atual['esc_silabico']            = (int)($m[1] ?? 0); continue; }
            if (preg_match('/^PR[EÉ]-SIL[AÁ]BICO\s*:\s*(\d+)?/iu', $linha, $m))                  { $atual['esc_pre_silabico']        = (int)($m[1] ?? 0); continue; }

            if (preg_match('/^CONCLUS[AÃ]O E PARECER T[EÉ]CNICO/iu', $linha)) {
                $modoConcl = true;
                continue;
            }

            // se não casou em nada e é um label conhecido (vazio), ignora
        }

        // fecha a última ficha
        if ($atual) {
            if ($modoConcl) $atual['txt_conclusao'] = trim($conclBuf);
            $fichas[] = $atual;
        }

        // mantém só fichas com escola_nome
        $fichas = array_values(array_filter($fichas, fn($f) => !empty($f['escola_nome'])));
        return $fichas;
    }

    private static function novaFicha(): array
    {
        return [
            'escola_nome'             => null,
            'localidade'              => null,
            'diretor'                 => null,
            'coordenador'             => null,
            'dat_realizacao'          => null,
            'ies_turno'               => null,
            'turma'                   => null,
            'qtd_alunos'              => 0,
            'qtd_pcd'                 => 0,
            'tecnico_responsavel'     => null,
            'lei_fluencia'            => 0,
            'lei_sem_fluencia'        => 0,
            'lei_frases'              => 0,
            'lei_palavras'            => 0,
            'lei_silabas'             => 0,
            'lei_nao_leitor'          => 0,
            'esc_ortografico'         => 0,
            'esc_alfabetico'          => 0,
            'esc_silabico_alfabetico' => 0,
            'esc_silabico'            => 0,
            'esc_pre_silabico'        => 0,
            'txt_conclusao'           => null,
        ];
    }

    private static function removeAcentos(string $s): string
    {
        $de = ['á','à','ã','â','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','õ','ô','ö','ú','ù','û','ü','ç','Á','À','Ã','Â','Ä','É','È','Ê','Ë','Í','Ì','Î','Ï','Ó','Ò','Õ','Ô','Ö','Ú','Ù','Û','Ü','Ç'];
        $pa = ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c','A','A','A','A','A','E','E','E','E','I','I','I','I','O','O','O','O','O','U','U','U','U','C'];
        return str_replace($de, $pa, $s);
    }

    private static function parseDataBr(?string $s): ?string
    {
        if (!$s) return null;
        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $s, $m)) {
            return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
        }
        return null;
    }

    private static function normalizaTurno(?string $s): ?string
    {
        if (!$s) return null;
        $u = mb_strtoupper(self::removeAcentos($s), 'UTF-8');
        if (str_contains($u, 'MANHA'))    return 'Manhã';
        if (str_contains($u, 'TARDE'))    return 'Tarde';
        if (str_contains($u, 'NOITE'))    return 'Noite';
        if (str_contains($u, 'INTEGRAL')) return 'Integral';
        return $s;
    }
}
