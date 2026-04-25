<?php
declare(strict_types=1);

require __DIR__ . '/../lib/XlsxReader.php';
require __DIR__ . '/../lib/XlsxParser.php';

if (empty($_FILES['arquivo']) || ($_FILES['arquivo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    json_err('Envie o arquivo .xlsx.');
}
$tmp = $_FILES['arquivo']['tmp_name'];
if (!is_uploaded_file($tmp)) json_err('Upload inválido.');

$dryRun = !empty($_POST['dry_run']);

try {
    $sheets = XlsxReader::read($tmp);
    $polos  = XlsxParser::parsear($sheets);
} catch (Throwable $e) {
    json_err('Falha ao ler XLSX: ' . $e->getMessage());
}

$resumo = [
    'polos' => 0, 'escolas_novas' => 0, 'escolas_atualizadas' => 0,
    'audiencias_novas' => 0, 'audiencias_atualizadas' => 0,
    'avisos' => [],
];
$detalhes = [];

$pdo = db();
if (!$dryRun) $pdo->beginTransaction();

try {
    foreach ($polos as $polo) {
        $poloNome = $polo['polo_nome'];
        $codPolo  = null;

        if ($dryRun) {
            $codPolo = (int)(db_val("SELECT cod_polo FROM polos WHERE polo_nome = :n", [':n' => $poloNome]) ?: 0);
        } else {
            $row = db_one("SELECT cod_polo FROM polos WHERE polo_nome = :n", [':n' => $poloNome]);
            if ($row) {
                $codPolo = (int)$row['cod_polo'];
            } else {
                $codPolo = (int)db_insert_returning(
                    "INSERT INTO polos (polo_nome) VALUES (:n) RETURNING cod_polo",
                    [':n' => $poloNome]
                );
            }
        }
        if (!$codPolo && $dryRun) {
            // em dry-run, se o polo não existe no banco, seguimos sem escrever
            $codPolo = -1; // marcador
        }
        $resumo['polos']++;

        foreach ($polo['fichas'] as $f) {
            $nomeEsc = (string)$f['escola_nome'];
            if ($nomeEsc === '') continue;

            // resolve escola (por nome + polo)
            $codEscola = null;
            if ($codPolo > 0) {
                $r = db_one("SELECT cod_escola FROM escolas WHERE cod_polo = :p AND escola_nome = :n",
                            [':p' => $codPolo, ':n' => $nomeEsc]);
                if ($r) {
                    $codEscola = (int)$r['cod_escola'];
                    if (!$dryRun) {
                        db_exec("UPDATE escolas SET localidade = COALESCE(:l, localidade),
                                      diretor = COALESCE(:d, diretor), coordenador = COALESCE(:c, coordenador)
                                  WHERE cod_escola = :ce",
                                [':l'=>$f['localidade'], ':d'=>$f['diretor'], ':c'=>$f['coordenador'], ':ce'=>$codEscola]);
                        $resumo['escolas_atualizadas']++;
                    }
                } else {
                    if (!$dryRun) {
                        $codEscola = (int)db_insert_returning(
                            "INSERT INTO escolas (cod_polo, escola_nome, localidade, diretor, coordenador)
                             VALUES (:p, :n, :l, :d, :c) RETURNING cod_escola",
                            [':p'=>$codPolo, ':n'=>$nomeEsc, ':l'=>$f['localidade'], ':d'=>$f['diretor'], ':c'=>$f['coordenador']]
                        );
                    }
                    $resumo['escolas_novas']++;
                }
            }

            // cria/atualiza audiencia apenas se tiver data + turma (obrigatórios)
            $data  = $f['dat_realizacao'];
            $turma = $f['turma'];
            $turno = $f['ies_turno'];
            if (!$data)  { $resumo['avisos'][] = "Ficha ignorada (sem data): $poloNome / $nomeEsc"; continue; }
            if (!$turma) { $resumo['avisos'][] = "Ficha ignorada (sem turma): $poloNome / $nomeEsc"; continue; }
            if (!$turno) $turno = 'Manhã';

            $status = 'nova';
            if (!$dryRun && $codEscola) {
                // chave lógica: cod_escola + data + turma
                $r = db_one("SELECT cod_audiencia FROM audiencias
                              WHERE cod_escola = :ce AND dat_realizacao = :dt AND turma = :tm",
                            [':ce'=>$codEscola, ':dt'=>$data, ':tm'=>$turma]);

                $params = [
                    ':ce'  => $codEscola, ':cp' => $codPolo, ':dt' => $data, ':tu' => $turno, ':tm' => $turma,
                    ':qa'  => (int)$f['qtd_alunos'], ':qp' => (int)$f['qtd_pcd'], ':tr' => $f['tecnico_responsavel'],
                    ':lf'  => (int)$f['lei_fluencia'], ':lsf' => (int)$f['lei_sem_fluencia'], ':lfr' => (int)$f['lei_frases'],
                    ':lpa' => (int)$f['lei_palavras'], ':lsi' => (int)$f['lei_silabas'], ':lnl' => (int)$f['lei_nao_leitor'],
                    ':eo'  => (int)$f['esc_ortografico'], ':ea' => (int)$f['esc_alfabetico'],
                    ':esa' => (int)$f['esc_silabico_alfabetico'], ':es' => (int)$f['esc_silabico'], ':eps' => (int)$f['esc_pre_silabico'],
                    ':con' => $f['txt_conclusao'],
                ];
                if ($r) {
                    $params[':c'] = (int)$r['cod_audiencia'];
                    db_exec("
                        UPDATE audiencias SET
                            ies_turno=:tu, qtd_alunos=:qa, qtd_pcd=:qp, tecnico_responsavel=:tr,
                            lei_fluencia=:lf, lei_sem_fluencia=:lsf, lei_frases=:lfr,
                            lei_palavras=:lpa, lei_silabas=:lsi, lei_nao_leitor=:lnl,
                            esc_ortografico=:eo, esc_alfabetico=:ea, esc_silabico_alfabetico=:esa,
                            esc_silabico=:es, esc_pre_silabico=:eps,
                            txt_conclusao=:con, dat_alteracao=NOW(),
                            cod_polo=:cp, cod_escola=:ce, dat_realizacao=:dt, turma=:tm
                         WHERE cod_audiencia=:c
                    ", $params);
                    $resumo['audiencias_atualizadas']++;
                    $status = 'atualizada';
                } else {
                    db_exec("
                        INSERT INTO audiencias (
                            cod_escola, cod_polo, dat_realizacao, ies_turno, turma,
                            qtd_alunos, qtd_pcd, tecnico_responsavel,
                            lei_fluencia, lei_sem_fluencia, lei_frases, lei_palavras, lei_silabas, lei_nao_leitor,
                            esc_ortografico, esc_alfabetico, esc_silabico_alfabetico, esc_silabico, esc_pre_silabico,
                            txt_conclusao
                        ) VALUES (
                            :ce, :cp, :dt, :tu, :tm,
                            :qa, :qp, :tr,
                            :lf, :lsf, :lfr, :lpa, :lsi, :lnl,
                            :eo, :ea, :esa, :es, :eps,
                            :con
                        )
                    ", $params);
                    $resumo['audiencias_novas']++;
                }
            } else {
                // dry-run: só conta
                $resumo['audiencias_novas']++;
            }

            $detalhes[] = [
                'polo'   => $poloNome,
                'escola' => $nomeEsc,
                'turma'  => $turma,
                'data'   => fmt_date_br($data),
                'status' => $dryRun ? 'simulada' : $status,
            ];
        }
    }

    if (!$dryRun) $pdo->commit();
} catch (Throwable $e) {
    if (!$dryRun && $pdo->inTransaction()) $pdo->rollBack();
    json_err('Erro durante import: ' . $e->getMessage(), 500);
}

json_ok([
    'dry_run'  => $dryRun ? 1 : 0,
    'resumo'   => $resumo,
    'detalhes' => $detalhes,
]);
