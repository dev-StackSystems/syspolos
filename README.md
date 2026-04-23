# Sistema de Auditoria de Leitura

Sistema web para digitalizar e organizar as auditorias de leitura que antes eram feitas manualmente no Excel (`AUDIÊNCIA DE LEITURA.xlsx`).

- **Backend:** PHP 8.2
- **Banco:** PostgreSQL (Neon)
- **Hospedagem:** Vercel (serverless)
- **Frontend:** Bootstrap 5 + jQuery (sem build-step)

---

## Funcionalidades

- CRUD completo de **Polos**, **Escolas** e **Auditorias**
- Formulário da auditoria com todos os critérios de leitura/escrita
- Ficha para visualização/impressão
- **Relatório consolidado** com totais por polo e % por critério
- **Importador XLSX** (com modo simulação) para migrar dados já existentes
- Filtros por polo, escola, turno e período

---

## 1. Criar o banco no Neon

1. Acesse <https://neon.tech> e crie um projeto (plano free é suficiente).
2. Copie a **connection string** (`postgres://usuario:senha@ep-xxxx.region.aws.neon.tech/dbname?sslmode=require`).
3. No painel SQL Editor do Neon, rode o conteúdo de [`schema.sql`](./schema.sql). Ele cria as tabelas `polos`, `escolas`, `auditorias` e já popula os 8 polos.

---

## 2. Rodar localmente (opcional)

Requisitos: PHP 8.1+ com as extensões `pdo_pgsql`, `zip`, `mbstring`, `simplexml`.

```bash
# 1. Copie o .env de exemplo
cp .env.example .env
# edite .env e cole sua DATABASE_URL do Neon

# 2. Suba o servidor
php -S localhost:8000 -t api
```

Depois abra <http://localhost:8000> — o entry point `api/index.php` é servido na raiz.

> Dica: em desenvolvimento local, o `.env` é lido automaticamente. Em produção (Vercel), use Environment Variables.

---

## 3. Deploy na Vercel

### 3.1. Instalar a CLI (opção 1)

```bash
npm i -g vercel
vercel login
vercel link          # associa a pasta a um projeto Vercel
vercel env add DATABASE_URL
# cole a connection string do Neon quando pedido (marcar Production + Preview + Development)
vercel --prod
```

### 3.2. Via dashboard (opção 2)

1. Suba o projeto para um repositório Git (GitHub/GitLab/Bitbucket).
2. Em <https://vercel.com/new>, importe o repositório.
3. Em **Environment Variables**, adicione:
   - `DATABASE_URL` → connection string do Neon
   - `APP_TZ` → `America/Fortaleza` (opcional)
4. Clique em **Deploy**. A Vercel usa o `vercel.json` automaticamente.

### Runtime usado

O arquivo [`vercel.json`](./vercel.json) declara:

```json
"runtime": "vercel-php@0.7.3"
```

Esse runtime comunitário (`juicyfx/vercel-php`) provê **PHP 8.2** com as extensões padrão (zip, mbstring, pdo_pgsql, openssl, etc.) e faz o rewrite de qualquer URL para `api/index.php`.

---

## 4. Estrutura

```
auditoria-leitura/
├── api/
│   └── index.php              # entry point (router)
├── src/
│   ├── config.php             # carrega .env / valida DATABASE_URL
│   ├── db.php                 # PDO (Postgres) + helpers
│   ├── helpers.php            # e(), url(), json_ok(), etc.
│   ├── layout_header.php      # navbar + flash
│   ├── layout_footer.php
│   ├── lib/
│   │   ├── XlsxReader.php     # leitor minimal de xlsx (zip+xml)
│   │   └── XlsxParser.php     # parser das fichas
│   ├── pages/
│   │   ├── home.php           # painel com KPIs
│   │   ├── polos.php          # CRUD polos
│   │   ├── escolas.php        # CRUD escolas
│   │   ├── auditorias.php     # lista com filtros
│   │   ├── auditoria_form.php # formulário add/edit
│   │   ├── auditoria_view.php # ficha readonly
│   │   ├── relatorio.php      # consolidado
│   │   └── importar.php       # upload xlsx
│   └── actions/               # endpoints POST (JSON)
│       ├── polo_salvar.php / polo_get.php / polo_excluir.php
│       ├── escola_salvar.php / escola_get.php / escola_excluir.php
│       ├── auditoria_salvar.php / auditoria_excluir.php
│       └── importar_xlsx.php
├── schema.sql                 # DDL + seed dos 8 polos
├── vercel.json                # config runtime PHP 8.2
├── composer.json
├── .env.example
└── .gitignore
```

---

## 5. Como usar

1. **Primeiro acesso** — entre em **Polos** e **Escolas** e ajuste os nomes (ou use o **Importar**).
2. **Importar do Excel** — em **Importar**, envie o `AUDIÊNCIA DE LEITURA.xlsx`. Sempre marque **"Simular apenas"** na primeira vez para revisar. Depois, desmarque e reprocesse.
3. **Nova auditoria** — botão **Nova auditoria** no topo. Preencha identificação, critérios de leitura/escrita (os totais aparecem em tempo real) e o parecer.
4. **Relatório** — em **Relatório**, filtre por polo e período. Use **Imprimir** pra gerar PDF pelo browser.

---

## Regras de duplicidade (importador)

O importador usa como chave lógica:

- **Escola:** `cod_polo + escola_nome` (case-sensitive)
- **Auditoria:** `cod_escola + dat_realizacao + turma`

Se a mesma ficha for importada de novo, ela é **atualizada**, não duplicada.

---

## Manutenção

- Adicionar novo campo de critério: incluir na `schema.sql` (ALTER TABLE), no form (`auditoria_form.php`), no view (`auditoria_view.php`), no save (`auditoria_salvar.php`) e no relatório (`relatorio.php`).
- Adicionar autenticação (se expor publicamente): middleware simples com usuário/senha no `api/index.php` ou integrar com Vercel Auth / Auth0.

---

## Licença

Uso interno — sem licença pública.
