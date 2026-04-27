<?php
/**
 * import.php — Importa JSON (vindo de export.php ou do migrador de localStorage)
 *
 * POST multipart: arquivo JSON no campo "arquivo"
 *   OU
 * POST application/json: corpo direto com os dados
 *
 * Modo: "merge" (padrão) = não sobrescreve dados existentes
 *       "replace"        = limpa tudo antes de importar
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

try {
    $pdo = getDB();

    // Lê o JSON (multipart ou raw body)
    $json = null;
    if (!empty($_FILES['arquivo']['tmp_name'])) {
        $json = file_get_contents($_FILES['arquivo']['tmp_name']);
    } else {
        $raw = file_get_contents('php://input');
        if ($raw) $json = $raw;
        elseif (!empty($_POST['dados'])) $json = $_POST['dados'];
    }

    if (!$json) {
        jsonError('Nenhum dado recebido.');
    }

    $data = json_decode($json, true);
    if (!$data) {
        jsonError('JSON inválido.');
    }

    $modo = $_GET['modo'] ?? $_POST['modo'] ?? 'merge';

    $pdo->beginTransaction();

    if ($modo === 'replace') {
        $pdo->prepare('DELETE FROM gastos      WHERE usuario_id=?')->execute([$userId]);
        $pdo->prepare('DELETE FROM meses       WHERE usuario_id=?')->execute([$userId]);
        $pdo->prepare('DELETE FROM recorrentes WHERE usuario_id=?')->execute([$userId]);
    }

    $countMeses  = 0;
    $countGastos = 0;
    $countRec    = 0;

    // ── Importa meses ─────────────────────────────────────
    $meses = $data['meses'] ?? [];

    // Compatibilidade com formato legado do localStorage
    // { "orcamento_2026_04": { renda, metas, expenses, recurring } }
    if (isset($data['tipo']) && $data['tipo'] === 'localStorage') {
        $meses = converterLocalStorage($data['dados'] ?? []);
        if (!empty($data['recurring'])) {
            $data['recorrentes'] = $data['recurring'];
        }
    }

    foreach ($meses as $m) {
        $ano  = (int)($m['ano'] ?? 0);
        $mes  = (int)($m['mes'] ?? 0);
        if (!$ano || !$mes) continue;

        $renda = (float)($m['renda'] ?? 0);
        $metas = json_encode($m['metas'] ?? []);

        if ($modo === 'merge') {
            $pdo->prepare('INSERT OR IGNORE INTO meses (usuario_id, ano, mes, renda, metas_json) VALUES (?,?,?,?,?)')->execute([$userId, $ano, $mes, $renda, $metas]);
        } else {
            $pdo->prepare('INSERT OR REPLACE INTO meses (usuario_id, ano, mes, renda, metas_json) VALUES (?,?,?,?,?)')->execute([$userId, $ano, $mes, $renda, $metas]);
        }
        $countMeses++;

        // Gastos do mês
        $gastos = $m['gastos'] ?? $m['expenses'] ?? [];
        foreach ($gastos as $cat => $items) {
            foreach ($items as $g) {
                $nome  = $g['name'] ?? $g['nome'] ?? '';
                $valor = (float)($g['value'] ?? $g['valor'] ?? 0);
                $rec   = (int)(bool)($g['recorrente'] ?? $g['fromRecurring'] ?? false);
                if (!$nome || $valor <= 0) continue;

                $pdo->prepare('INSERT INTO gastos (usuario_id, ano, mes, categoria, nome, valor, recorrente) VALUES (?,?,?,?,?,?,?)')->execute([$userId, $ano, $mes, $cat, $nome, $valor, $rec]);
                $countGastos++;
            }
        }
    }

    // ── Importa recorrentes ───────────────────────────────
    $recMap = $data['recorrentes'] ?? [];
    if ($modo === 'replace') {
        $pdo->prepare('DELETE FROM recorrentes WHERE usuario_id=?')->execute([$userId]);
    }
    foreach ($recMap as $cat => $items) {
        foreach ($items as $r) {
            $nome  = $r['name'] ?? $r['nome'] ?? '';
            $valor = (float)($r['value'] ?? $r['valor'] ?? 0);
            if (!$nome || $valor <= 0) continue;

            if ($modo === 'merge') {
                // Evita duplicatas por nome+categoria
                $st = $pdo->prepare('SELECT id FROM recorrentes WHERE usuario_id=? AND categoria=? AND nome=?');
                $st->execute([$userId, $cat, $nome]);
                if ($st->fetch()) continue;
            }
            $pdo->prepare('INSERT INTO recorrentes (usuario_id, categoria, nome, valor) VALUES (?,?,?,?)')->execute([$userId, $cat, $nome, $valor]);
            $countRec++;
        }
    }

    $pdo->commit();

    jsonOk([
        'message'       => 'Importação concluída.',
        'meses'         => $countMeses,
        'gastos'        => $countGastos,
        'recorrentes'   => $countRec,
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    jsonError('Erro na importação: ' . $e->getMessage(), 500);
}

// ── Converte formato legado do localStorage ────────────────
function converterLocalStorage(array $dados): array {
    $meses = [];
    foreach ($dados as $key => $val) {
        // Chave: orcamento_2026_04
        if (!preg_match('/(\d{4})_(\d{2})$/', $key, $m)) continue;
        $meses[] = [
            'ano'      => (int)$m[1],
            'mes'      => (int)$m[2],
            'renda'    => (float)($val['renda'] ?? 0),
            'metas'    => $val['metas'] ?? [],
            'expenses' => $val['expenses'] ?? [],
        ];
    }
    return $meses;
}

function jsonOk(array $data): never  { echo json_encode(['ok' => true] + $data); exit; }
function jsonError(string $msg, int $code = 422): never {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}
