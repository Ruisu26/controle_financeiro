<?php
session_start();
// Se já estiver logado, redireciona
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Orçamento Doméstico — Entrar</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --bg:       #0e0f11;
    --surface:  #16181c;
    --surface2: #1e2027;
    --border:   #2a2d35;
    --text:     #e8eaf0;
    --muted:    #6b7080;
    --accent:   #f0b429;
    --green:    #34d399;
    --red:      #f87171;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
  }

  /* Background subtle grid */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(var(--border) 1px, transparent 1px),
      linear-gradient(90deg, var(--border) 1px, transparent 1px);
    background-size: 48px 48px;
    opacity: 0.25;
    pointer-events: none;
  }

  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 40px 36px;
    width: 100%;
    max-width: 420px;
    position: relative;
    z-index: 1;
    animation: fadeUp 0.3s ease;
  }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .logo-wrap {
    text-align: center;
    margin-bottom: 28px;
  }
  .logo-wrap img {
    height: 48px;
    margin-bottom: 12px;
    display: block;
    margin-left: auto;
    margin-right: auto;
  }
  .logo-wrap .fallback-logo {
    font-family: 'DM Serif Display', serif;
    font-size: 26px;
    color: var(--accent);
    margin-bottom: 4px;
  }
  .logo-wrap p {
    font-size: 13px;
    color: var(--muted);
  }

  /* Tabs */
  .tabs {
    display: flex;
    background: var(--surface2);
    border-radius: 10px;
    padding: 3px;
    margin-bottom: 28px;
  }
  .tab-btn {
    flex: 1;
    padding: 9px;
    border: none;
    border-radius: 8px;
    background: none;
    color: var(--muted);
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
  }
  .tab-btn.active {
    background: var(--surface);
    color: var(--text);
    box-shadow: 0 1px 4px rgba(0,0,0,0.4);
  }

  /* Forms */
  .form-section { display: none; }
  .form-section.active { display: block; }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 16px;
  }
  .form-group label {
    font-size: 12px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.6px;
  }
  .form-input {
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    padding: 11px 14px;
    transition: border-color 0.15s;
    width: 100%;
  }
  .form-input:focus { outline: none; border-color: var(--accent); }
  .form-input::placeholder { color: var(--muted); }

  .btn-primary {
    width: 100%;
    background: var(--accent);
    border: none;
    border-radius: 10px;
    color: #000;
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    font-weight: 600;
    padding: 13px;
    cursor: pointer;
    transition: opacity 0.15s;
    margin-top: 4px;
  }
  .btn-primary:hover { opacity: 0.88; }
  .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

  /* Feedback */
  .alert {
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 13px;
    margin-bottom: 16px;
    display: none;
  }
  .alert.error { background: rgba(248,113,113,0.12); border: 1px solid rgba(248,113,113,0.3); color: var(--red); display: block; }
  .alert.success { background: rgba(52,211,153,0.12); border: 1px solid rgba(52,211,153,0.3); color: var(--green); display: block; }

  .spinner {
    display: inline-block;
    width: 16px; height: 16px;
    border: 2px solid rgba(0,0,0,0.3);
    border-top-color: #000;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
    vertical-align: middle;
    margin-right: 6px;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
</style>
</head>
<body>
<div class="card">

  <div class="logo-wrap">
    <?php if (file_exists(__DIR__ . '/assets/logo.png')): ?>
      <img src="assets/logo.png" alt="Logo" />
    <?php else: ?>
      <div class="fallback-logo">Orçamento</div>
    <?php endif; ?>
    <p>Controle financeiro doméstico</p>
  </div>

  <div class="tabs">
    <button class="tab-btn active" onclick="switchTab('login')">Entrar</button>
    <button class="tab-btn" onclick="switchTab('cadastro')">Criar conta</button>
  </div>

  <!-- LOGIN -->
  <div class="form-section active" id="sec-login">
    <div id="alert-login" class="alert"></div>
    <div class="form-group">
      <label>E-mail</label>
      <input type="email" class="form-input" id="login-email" placeholder="seu@email.com" autocomplete="email" />
    </div>
    <div class="form-group">
      <label>Senha</label>
      <input type="password" class="form-input" id="login-senha" placeholder="••••••••" autocomplete="current-password" />
    </div>
    <button class="btn-primary" id="btn-login" onclick="doLogin()">Entrar</button>
  </div>

  <!-- CADASTRO -->
  <div class="form-section" id="sec-cadastro">
    <div id="alert-cadastro" class="alert"></div>
    <div class="form-group">
      <label>Nome</label>
      <input type="text" class="form-input" id="cad-nome" placeholder="Seu nome" autocomplete="name" />
    </div>
    <div class="form-group">
      <label>E-mail</label>
      <input type="email" class="form-input" id="cad-email" placeholder="seu@email.com" autocomplete="email" />
    </div>
    <div class="form-group">
      <label>Senha (mínimo 6 caracteres)</label>
      <input type="password" class="form-input" id="cad-senha" placeholder="••••••••" autocomplete="new-password" />
    </div>
    <button class="btn-primary" id="btn-cad" onclick="doCadastro()">Criar conta</button>
  </div>

</div>

<script>
function switchTab(tab) {
  document.querySelectorAll('.tab-btn').forEach((b,i) => b.classList.toggle('active', (i===0) === (tab==='login')));
  document.getElementById('sec-login').classList.toggle('active', tab === 'login');
  document.getElementById('sec-cadastro').classList.toggle('active', tab === 'cadastro');
}

function setAlert(id, msg, type) {
  const el = document.getElementById(id);
  el.textContent = msg;
  el.className = 'alert ' + type;
}

function setLoading(btnId, loading) {
  const btn = document.getElementById(btnId);
  if (loading) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>Aguarde...';
  } else {
    btn.disabled = false;
    btn.innerHTML = btn.id === 'btn-login' ? 'Entrar' : 'Criar conta';
  }
}

async function doLogin() {
  const email = document.getElementById('login-email').value.trim();
  const senha = document.getElementById('login-senha').value;
  if (!email || !senha) { setAlert('alert-login', 'Preencha todos os campos.', 'error'); return; }

  setLoading('btn-login', true);
  try {
    const fd = new FormData();
    fd.append('action', 'login');
    fd.append('email', email);
    fd.append('senha', senha);
    const res = await fetch('api/auth.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (!data.ok) { setAlert('alert-login', data.error, 'error'); return; }

    // Verifica localStorage antes de redirecionar
    if (data.primeiroAcesso && hasLocalStorageData()) {
      sessionStorage.setItem('migrar', '1');
    }
    window.location.href = 'index.php';
  } catch (e) {
    setAlert('alert-login', 'Erro de conexão.', 'error');
  } finally {
    setLoading('btn-login', false);
  }
}

async function doCadastro() {
  const nome  = document.getElementById('cad-nome').value.trim();
  const email = document.getElementById('cad-email').value.trim();
  const senha = document.getElementById('cad-senha').value;
  if (!nome || !email || !senha) { setAlert('alert-cadastro', 'Preencha todos os campos.', 'error'); return; }

  setLoading('btn-cad', true);
  try {
    const fd = new FormData();
    fd.append('action', 'cadastro');
    fd.append('nome', nome);
    fd.append('email', email);
    fd.append('senha', senha);
    const res = await fetch('api/auth.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (!data.ok) { setAlert('alert-cadastro', data.error, 'error'); return; }

    if (data.primeiroAcesso && hasLocalStorageData()) {
      sessionStorage.setItem('migrar', '1');
    }
    window.location.href = 'index.php';
  } catch (e) {
    setAlert('alert-cadastro', 'Erro de conexão.', 'error');
  } finally {
    setLoading('btn-cad', false);
  }
}

function hasLocalStorageData() {
  for (let i = 0; i < localStorage.length; i++) {
    if (localStorage.key(i).startsWith('orcamento_2')) return true;
  }
  return false;
}

// Enter key support
document.addEventListener('keydown', e => {
  if (e.key !== 'Enter') return;
  if (document.getElementById('sec-login').classList.contains('active')) doLogin();
  else doCadastro();
});
</script>
</body>
</html>
