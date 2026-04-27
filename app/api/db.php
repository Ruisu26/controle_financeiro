<?php
/**
 * db.php — Conexão SQLite e criação de tabelas
 * Banco fica em /var/www/dados_salvos/banco.sqlite (fora do webroot)
 */

define('DB_PATH', '/var/www/dados_salvos/banco.sqlite');

function getDB(): PDO {
    $isNew = !file_exists(DB_PATH);

    // Garante que o diretório existe
    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA journal_mode=WAL;');
    $pdo->exec('PRAGMA foreign_keys=ON;');

    if ($isNew) {
        criarTabelas($pdo);
    }

    return $pdo;
}

function criarTabelas(PDO $pdo): void {
    $pdo->exec("
        -- Usuários
        CREATE TABLE IF NOT EXISTS usuarios (
            id        INTEGER PRIMARY KEY AUTOINCREMENT,
            nome      TEXT    NOT NULL,
            email     TEXT    NOT NULL UNIQUE,
            senha     TEXT    NOT NULL,
            criado_em TEXT    NOT NULL DEFAULT (datetime('now'))
        );

        -- Dados mensais (renda + metas por mês/usuário)
        CREATE TABLE IF NOT EXISTS meses (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            ano        INTEGER NOT NULL,
            mes        INTEGER NOT NULL,  -- 1–12
            renda      REAL    NOT NULL DEFAULT 0,
            metas_json TEXT    NOT NULL DEFAULT '{}',
            UNIQUE(usuario_id, ano, mes),
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        );

        -- Gastos lançados por mês/categoria
        CREATE TABLE IF NOT EXISTS gastos (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            ano        INTEGER NOT NULL,
            mes        INTEGER NOT NULL,
            categoria  TEXT    NOT NULL,
            nome       TEXT    NOT NULL,
            valor      REAL    NOT NULL,
            recorrente INTEGER NOT NULL DEFAULT 0,  -- 1 = veio do auto preenchimento
            criado_em  TEXT    NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        );

        -- Gastos recorrentes globais (por usuário/categoria)
        CREATE TABLE IF NOT EXISTS recorrentes (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            categoria  TEXT    NOT NULL,
            nome       TEXT    NOT NULL,
            valor      REAL    NOT NULL,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        );

        -- Índices para performance
        CREATE INDEX IF NOT EXISTS idx_meses_user    ON meses(usuario_id, ano, mes);
        CREATE INDEX IF NOT EXISTS idx_gastos_user   ON gastos(usuario_id, ano, mes);
        CREATE INDEX IF NOT EXISTS idx_rec_user      ON recorrentes(usuario_id);
    ");
}

/**
 * Garante que as tabelas existam mesmo em bancos já criados
 * (chamado a cada request, é idempotente por causa do IF NOT EXISTS)
 */
function ensureTables(PDO $pdo): void {
    criarTabelas($pdo);
}
