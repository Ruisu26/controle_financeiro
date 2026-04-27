<?php
/**
 * export.php — Exporta todos os dados do usuário em JSON
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

    // Info do usuário
    $st = $pdo->prepare('SELECT nome, email, criado_em FROM usuarios WHERE id=?');
    $st->execute([$userId]);
    $user = $st->fetch();

    // Todos os meses
    $st = $pdo->prepare('SELECT ano, mes, renda, metas_json FROM meses WHERE usuario_id=? ORDER BY ano, mes');
    $st->execute([$userId]);
    $meses = $st->fetchAll();

    // Todos os gastos
    $st = $pdo->prepare('SELECT ano, mes, categoria, nome, valor, recorrente FROM gastos WHERE usuario_id=? ORDER BY ano, mes, categoria, id');
    $st->execute([$userId]);
    $gastos = $st->fetchAll();

    // Recorrentes
    $st = $pdo->prepare('SELECT categoria, nome, valor FROM recorrentes WHERE usuario_id=? ORDER BY categoria, id');
    $st->execute([$userId]);
    $recorrentes = $st->fetchAll();

    // Monta estrutura por mês
    $mesesMap = [];
    foreach ($meses as $m) {
        $key = $m['ano'] . '_' . str_pad($m['mes'], 2, '0', STR_PAD_LEFT);
        $mesesMap[$key] = [
            'ano'    => (int)$m['ano'],
            'mes'    => (int)$m['mes'],
            'renda'  => (float)$m['renda'],
            'metas'  => json_decode($m['metas_json'], true) ?? [],
            'gastos' => [],
        ];
    }
    foreach ($gastos as $g) {
        $key = $g['ano'] . '_' . str_pad($g['mes'], 2, '0', STR_PAD_LEFT);
        if (!isset($mesesMap[$key])) {
            $mesesMap[$key] = ['ano' => (int)$g['ano'], 'mes' => (int)$g['mes'], 'renda' => 0, 'metas' => [], 'gastos' => []];
        }
        $mesesMap[$key]['gastos'][$g['categoria']][] = [
            'name'       => $g['nome'],
            'value'      => (float)$g['valor'],
            'recorrente' => (bool)$g['recorrente'],
        ];
    }

    // Recorrentes por categoria
    $recMap = [];
    foreach ($recorrentes as $r) {
        $recMap[$r['categoria']][] = ['name' => $r['nome'], 'value' => (float)$r['valor']];
    }

    $export = [
        'versao'      => '1.0',
        'exportado_em'=> date('c'),
        'usuario'     => $user,
        'meses'       => array_values($mesesMap),
        'recorrentes' => $recMap,
    ];

    // Força download
    $filename = 'orcamento_' . ($user['email'] ?? 'export') . '_' . date('Ymd_His') . '.json';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
