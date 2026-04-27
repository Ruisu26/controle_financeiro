<?php
/**
 * recorrentes.php — CRUD de gastos recorrentes globais
 *
 * GET  ?action=listar              → todos os recorrentes do usuário
 * POST action=add                  → adiciona recorrente
 * POST action=del  id=N            → remove recorrente
 * POST action=aplicar ano=X mes=Y  → copia recorrentes de uma categoria para o mês
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

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

        case 'listar':
            $st = $pdo->prepare('SELECT id, categoria, nome, valor FROM recorrentes WHERE usuario_id=? ORDER BY categoria, id');
            $st->execute([$userId]);
            $rows = $st->fetchAll();

            $result = [];
            foreach ($rows as $r) {
                $result[$r['categoria']][] = ['id' => (int)$r['id'], 'name' => $r['nome'], 'value' => (float)$r['valor']];
            }
            jsonOk(['recorrentes' => $result]);

        case 'add':
            $categoria = trim($_POST['categoria'] ?? '');
            $nome      = trim($_POST['nome']      ?? '');
            $valor     = (float)str_replace(',', '.', $_POST['valor'] ?? '0');

            if (!$categoria || !$nome || $valor <= 0) jsonError('Dados inválidos.');

            $st = $pdo->prepare('INSERT INTO recorrentes (usuario_id, categoria, nome, valor) VALUES (?,?,?,?)');
            $st->execute([$userId, $categoria, $nome, $valor]);

            jsonOk(['id' => (int)$pdo->lastInsertId(), 'message' => 'Recorrente salvo.']);

        case 'del':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) jsonError('ID inválido.');

            $st = $pdo->prepare('DELETE FROM recorrentes WHERE id=? AND usuario_id=?');
            $st->execute([$id, $userId]);

            if ($st->rowCount() === 0) jsonError('Recorrente não encontrado.', 404);
            jsonOk(['message' => 'Recorrente removido.']);

        case 'aplicar':
            $ano       = (int)($_POST['ano']       ?? 0);
            $mes       = (int)($_POST['mes']        ?? 0);
            $categoria = trim($_POST['categoria']   ?? '');

            if (!$ano || !$mes || !$categoria) jsonError('Dados inválidos.');

            $st = $pdo->prepare('SELECT nome, valor FROM recorrentes WHERE usuario_id=? AND categoria=?');
            $st->execute([$userId, $categoria]);
            $items = $st->fetchAll();

            if (!$items) jsonOk(['adicionados' => 0, 'message' => 'Nenhum recorrente nesta categoria.']);

            $ins = $pdo->prepare('INSERT INTO gastos (usuario_id, ano, mes, categoria, nome, valor, recorrente) VALUES (?,?,?,?,?,?,1)');
            foreach ($items as $item) {
                $ins->execute([$userId, $ano, $mes, $categoria, $item['nome'], $item['valor']]);
            }

            jsonOk(['adicionados' => count($items), 'message' => count($items) . ' gasto(s) adicionado(s).']);

        default:
            jsonError('Ação desconhecida.', 400);
    }

} catch (Exception $e) {
    jsonError('Erro interno: ' . $e->getMessage(), 500);
}

function jsonOk(array $data): never  { echo json_encode(['ok' => true] + $data); exit; }
function jsonError(string $msg, int $code = 422): never {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}
