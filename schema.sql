-- =====================================================================
-- Schema: Sistema de Auditoria de Leitura
-- Banco : PostgreSQL (Neon)
-- =====================================================================

CREATE TABLE IF NOT EXISTS polos (
    cod_polo      SERIAL PRIMARY KEY,
    polo_nome     VARCHAR(100) NOT NULL UNIQUE,
    ies_ativo     CHAR(1) NOT NULL DEFAULT 'S',
    dat_cadastro  TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS escolas (
    cod_escola    SERIAL PRIMARY KEY,
    cod_polo      INTEGER NOT NULL REFERENCES polos(cod_polo) ON DELETE RESTRICT,
    escola_nome   VARCHAR(200) NOT NULL,
    localidade    VARCHAR(200),
    diretor       VARCHAR(200),
    coordenador   VARCHAR(200),
    ies_ativo     CHAR(1) NOT NULL DEFAULT 'S',
    dat_cadastro  TIMESTAMP NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_escolas_polo ON escolas(cod_polo);
CREATE INDEX IF NOT EXISTS idx_escolas_nome ON escolas(escola_nome);

CREATE TABLE IF NOT EXISTS auditorias (
    cod_auditoria            SERIAL PRIMARY KEY,
    cod_escola               INTEGER NOT NULL REFERENCES escolas(cod_escola) ON DELETE RESTRICT,
    cod_polo                 INTEGER NOT NULL REFERENCES polos(cod_polo) ON DELETE RESTRICT,
    dat_realizacao           DATE NOT NULL,
    ies_turno                VARCHAR(20) NOT NULL,
    turma                    VARCHAR(80) NOT NULL,
    qtd_alunos               INTEGER NOT NULL DEFAULT 0,
    qtd_pcd                  INTEGER NOT NULL DEFAULT 0,
    tecnico_responsavel      VARCHAR(300),

    -- Critérios de leitura
    lei_fluencia             INTEGER NOT NULL DEFAULT 0,
    lei_sem_fluencia         INTEGER NOT NULL DEFAULT 0,
    lei_frases               INTEGER NOT NULL DEFAULT 0,
    lei_palavras             INTEGER NOT NULL DEFAULT 0,
    lei_silabas              INTEGER NOT NULL DEFAULT 0,
    lei_nao_leitor           INTEGER NOT NULL DEFAULT 0,

    -- Critérios de escrita
    esc_ortografico          INTEGER NOT NULL DEFAULT 0,
    esc_alfabetico           INTEGER NOT NULL DEFAULT 0,
    esc_silabico_alfabetico  INTEGER NOT NULL DEFAULT 0,
    esc_silabico             INTEGER NOT NULL DEFAULT 0,
    esc_pre_silabico         INTEGER NOT NULL DEFAULT 0,

    txt_conclusao            TEXT,

    dat_cadastro             TIMESTAMP NOT NULL DEFAULT NOW(),
    dat_alteracao            TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_audit_escola ON auditorias(cod_escola);
CREATE INDEX IF NOT EXISTS idx_audit_polo   ON auditorias(cod_polo);
CREATE INDEX IF NOT EXISTS idx_audit_data   ON auditorias(dat_realizacao);

-- Seed dos 8 polos do arquivo Excel
INSERT INTO polos (polo_nome) VALUES
    ('POLO 1'), ('POLO 2'), ('POLO 3'), ('POLO 4'),
    ('POLO 5'), ('POLO 6'), ('POLO 7'), ('POLO 8')
ON CONFLICT (polo_nome) DO NOTHING;

-- ========== Usuários do sistema (login) ==========
CREATE TABLE IF NOT EXISTS sys_usuarios (
    cod_usuario       SERIAL PRIMARY KEY,
    email             VARCHAR(200) NOT NULL UNIQUE,
    senha_hash        VARCHAR(255) NOT NULL,
    nome              VARCHAR(200) NOT NULL,
    ies_ativo         CHAR(1) NOT NULL DEFAULT 'S',
    dat_cadastro      TIMESTAMP NOT NULL DEFAULT NOW(),
    dat_ultimo_login  TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_sys_usuarios_email ON sys_usuarios(email);
