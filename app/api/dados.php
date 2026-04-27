<?php
/**
 * dados.php — CRUD de meses, metas e gastos
 *
 * GET  ?action=mes&ano=2026&mes=4          → carrega dados do mês
 * POST action=salvar_renda                 → salva renda do mês
 * POST action=salvar_metas                 → salva metas do mês
 * POST action=add_gasto                    → adiciona gasto
 * POST action=edit_gasto                   → edita gasto
 * POST action=del_gasto                    → remove gasto
 * GET  ?action=meses_com_dados             → lista meses que têm dados
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

// Protege todas as rotas
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Não autenticado.']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $pdo = getDB();

    switch ($action) {

        // ── CARREGAR MÊS COMPLETO ─────────────────────────────
        case 'mes':
            $ano = (int)($_GET['ano'] ?? date('Y'));
            $mes = (int)($_GET['mes'] ?? date('n'));

            // Renda + metas
            $st = $pdo->prepare('SELECT renda, metas_json FROM meses WHERE usuario_id=? AND ano=? AND mes=?');
            $st->execute([$userId, $ano, $mes]);
            $row = $st->fetch();

            $renda = $row ? (float)$row['renda'] : 0.0;
            $metas = $row ? json_decode($row['metas_json'], true) : null;

            // Herda metas do mês anterior se for mês novo
            if ($metas === null) {
                $metas = metasHerdadas($pdo, $userId, $ano, $mes);
            }

            // Gastos
            $st2 = $pdo->prepare('SELECT id, categoria, nome, valor, recorrente FROM gastos WHERE usuario_id=? AND ano=? AND mes=? ORDER BY id');
            $st2->execute([$userId, $ano, $mes]);
            $rows = $st2->fetchAll();

            $expenses = [];
            foreach ($rows as $g) {
                $expenses[$g['categoria']][] = [
                    'id'         => (int)$g['id'],
                    'name'       => $g['nome'],
                    'value'      => (float)$g['valor'],
                    'recorrente' => (bool)$g['recorrente'],
                ];
            }

            jsonOk(['renda' => $renda, 'metas' => $metas, 'expenses' => $expenses]);

        // ── SALVAR RENDA ──────────────────────────────────────
        case 'salvar_renda':
            $ano   = (int)($_POST['ano']   ?? 0);
            $mes   = (int)($_POST['mes']   ?? 0);
            $renda = (float)str_replace(',', '.', $_POST['renda'] ?? '0');

            upsertMes($pdo, $userId, $ano, $mes, ['renda' => $renda]);
            jsonOk(['message' => 'Renda salva.']);

        // ── SALVAR METAS ──────────────────────────────────────
        case 'salvar_metas':
            $ano   = (int)($_POST['ano'] ?? 0);
            $mes   = (int)($_POST['mes'] ?? 0);
            $metas = json_decode($_POST['metas'] ?? '{}', true);

            if (!is_array($metas)) jsonError('Metas inválidas.');
            $total = array_sum($metas);
            if (abs($total - 100) > 0.01) jsonError('As metas precisam somar 100%.');

            upsertMes($pdo, $userId, $ano, $mes, ['metas_json' => json_encode($metas)]);
            jsonOk(['message' => 'Metas salvas.']);

        // ── ADICIONAR GASTO ───────────────────────────────────
        case 'add_gasto':
            $ano       = (int)($_POST['ano'] ?? 0);
            $mes       = (int)($_POST['mes'] ?? 0);
            $categoria = trim($_POST['categoria'] ?? '');
            $nome      = trim($_POST['nome'] ?? '');
            $valor     = (float)str_replace(',', '.', $_POST['valor'] ?? '0');
            $recorrente= (int)($_POST['recorrente'] ?? 0);

            if (!$categoria || !$nome || $valor <= 0) jsonError('Dados inválidos.');

            $st = $pdo->prepare('INSERT INTO gastos (usuario_id, ano, mes, categoria, nome, valor, recorrente) VALUES (?,?,?,?,?,?,?)');
            $st->execute([$userId, $ano, $mes, $categoria, $nome, $valor, $recorrente]);
            $newId = (int)$pdo->lastInsertId();

            jsonOk(['id' => $newId, 'message' => 'Gasto adicionado.']);

        // ── EDITAR GASTO ──────────────────────────────────────
        case 'edit_gasto':
            $id    = (int)($_POST['id']    ?? 0);
            $nome  = trim($_POST['nome']   ?? '');
            $valor = (float)str_replace(',', '.', $_POST['valor'] ?? '0');

            if (!$id || !$nome || $valor <= 0) jsonError('Dados inválidos.');

            // Garante que o gasto pertence ao usuário
            $st = $pdo->prepare('UPDATE gastos SET nome=?, valor=? WHERE id=? AND usuario_id=?');
            $st->execute([$nome, $valor, $id, $userId]);

            if ($st->rowCount() === 0) jsonError('Gasto não encontrado.', 404);
            jsonOk(['message' => 'Gasto atualizado.']);

        // ── DELETAR GASTO ─────────────────────────────────────
        case 'del_gasto':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) jsonError('ID inválido.');

            $st = $pdo->prepare('DELETE FROM gastos WHERE id=? AND usuario_id=?');
            $st->execute([$id, $userId]);

            if ($st->rowCount() === 0) jsonError('Gasto não encontrado.', 404);
            jsonOk(['message' => 'Gasto removido.']);

        // ── MESES COM DADOS ───────────────────────────────────
        case 'meses_com_dados':
            $st = $pdo->prepare('SELECT DISTINCT ano, mes FROM meses WHERE usuario_id=? UNION SELECT DISTINCT ano, mes FROM gastos WHERE usuario_id=? ORDER BY ano, mes');
            $st->execute([$userId, $userId]);
            $rows = $st->fetchAll();
            $keys = array_map(fn($r) => sprintf('%04d_%02d', $r['ano'], $r['mes']), $rows);
            jsonOk(['meses' => $keys]);

        default:
            jsonError('Ação desconhecida.', 400);
    }

} catch (Exception $e) {
    jsonError('Erro interno: ' . $e->getMessage(), 500);
}

// ── Helpers ───────────────────────────────────────────────

function upsertMes(PDO $pdo, int $userId, int $ano, int $mes, array $fields): void {
    // Garante que o registro exista
    $pdo->prepare('INSERT OR IGNORE INTO meses (usuario_id, ano, mes, metas_json) VALUES (?,?,?,?)')->execute([$userId, $ano, $mes, '{}']);

    $sets  = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
    $vals  = array_values($fields);
    $vals[] = $userId; $vals[] = $ano; $vals[] = $mes;

    $pdo->prepare("UPDATE meses SET $sets WHERE usuario_id=? AND ano=? AND mes=?")->execute($vals);
}

function metasHerdadas(PDO $pdo, int $userId, int $ano, int $mes): array {
    $default = ['fixos'=>10,'conforto'=>0,'metas'=>5,'prazeres'=>18,'liberdade'=>50,'conhecimento'=>17];

    // Busca o mês anterior mais recente com metas
    $st = $pdo->prepare("
        SELECT metas_json FROM meses
        WHERE usuario_id=?
          AND (ano < ? OR (ano=? AND mes < ?))
          AND metas_json != '{}'
        ORDER BY ano DESC, mes DESC
        LIMIT 1
    ");
    $st->execute([$userId, $ano, $ano, $mes]);
    $row = $st->fetch();

    if ($row) {
        $m = json_decode($row['metas_json'], true);
        if (is_array($m) && count($m) > 0) return $m;
    }
    return $default;
}

function jsonOk(array $data): never {
    echo json_encode(['ok' => true] + $data);
    exit;
}
function jsonError(string $msg, int $code = 422): never {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}
