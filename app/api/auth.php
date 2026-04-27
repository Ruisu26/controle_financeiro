<?php
/**
 * auth.php — Login, cadastro e verificação de sessão
 * POST /api/auth.php  action=login|cadastro|logout|check
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $pdo = getDB();
    ensureTables($pdo);

    switch ($action) {

        // ── CADASTRO ──────────────────────────────────────────
        case 'cadastro':
            $nome  = trim($_POST['nome']  ?? '');
            $email = trim(strtolower($_POST['email'] ?? ''));
            $senha = $_POST['senha'] ?? '';

            if (!$nome || !$email || !$senha) {
                jsonError('Preencha todos os campos.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                jsonError('E-mail inválido.');
            }
            if (strlen($senha) < 6) {
                jsonError('A senha deve ter ao menos 6 caracteres.');
            }

            // Verifica duplicidade
            $st = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
            $st->execute([$email]);
            if ($st->fetch()) {
                jsonError('E-mail já cadastrado.');
            }

            $hash = password_hash($senha, PASSWORD_BCRYPT);
            $st = $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)');
            $st->execute([$nome, $email, $hash]);
            $userId = $pdo->lastInsertId();

            $_SESSION['user_id']   = $userId;
            $_SESSION['user_nome'] = $nome;
            $_SESSION['user_email']= $email;

            jsonOk(['message' => 'Conta criada com sucesso!', 'nome' => $nome, 'primeiroAcesso' => true]);

        // ── LOGIN ─────────────────────────────────────────────
        case 'login':
            $email = trim(strtolower($_POST['email'] ?? ''));
            $senha = $_POST['senha'] ?? '';

            if (!$email || !$senha) {
                jsonError('Preencha e-mail e senha.');
            }

            $st = $pdo->prepare('SELECT id, nome, senha FROM usuarios WHERE email = ?');
            $st->execute([$email]);
            $user = $st->fetch();

            if (!$user || !password_verify($senha, $user['senha'])) {
                jsonError('E-mail ou senha incorretos.');
            }

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_email']= $email;

            // É primeiro acesso? (sem nenhum mês salvo)
            $st2 = $pdo->prepare('SELECT COUNT(*) as c FROM meses WHERE usuario_id = ?');
            $st2->execute([$user['id']]);
            $count = $st2->fetch()['c'];

            jsonOk([
                'message'      => 'Login realizado!',
                'nome'         => $user['nome'],
                'primeiroAcesso' => ($count === 0)
            ]);

        // ── LOGOUT ────────────────────────────────────────────
        case 'logout':
            session_destroy();
            jsonOk(['message' => 'Sessão encerrada.']);

        // ── CHECK (verifica sessão ativa) ─────────────────────
        case 'check':
            if (!empty($_SESSION['user_id'])) {
                jsonOk([
                    'logado' => true,
                    'nome'   => $_SESSION['user_nome'],
                    'email'  => $_SESSION['user_email']
                ]);
            } else {
                jsonOk(['logado' => false]);
            }

        default:
            jsonError('Ação desconhecida.', 400);
    }

} catch (Exception $e) {
    jsonError('Erro interno: ' . $e->getMessage(), 500);
}

// ── Helpers ───────────────────────────────────────────────
function jsonOk(array $data): never {
    echo json_encode(['ok' => true] + $data);
    exit;
}
function jsonError(string $msg, int $code = 422): never {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}
