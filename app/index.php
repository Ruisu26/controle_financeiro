<?php
// ── Guarda de autenticação ────────────────────────────────
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userName  = htmlspecialchars($_SESSION['user_nome']  ?? 'Usuário');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Orçamento Doméstico</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link rel=icon href="assets/Logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ══════════════════════════════════════════════════════════
   TOKENS
══════════════════════════════════════════════════════════ */
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

  --cat-fixos:       #60a5fa;
  --cat-conforto:    #34d399;
  --cat-metas:       #a78bfa;
  --cat-prazeres:    #f472b6;
  --cat-liberdade:   #f0b429;
  --cat-conhecimento:#fb923c;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  font-size: 14px;
}

/* ══════════════════════════════════════════════════════════
   HEADER
══════════════════════════════════════════════════════════ */
header {
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  padding: 0 28px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 100;
}
.header-left { display: flex; align-items: center; gap: 12px; }
.header-logo img { height: 32px; display: block; }
.header-logo-text {
  font-family: 'DM Serif Display', serif;
  font-size: 20px;
  color: var(--accent);
  letter-spacing: 0.5px;
}
.header-sep {
  width: 1px; height: 20px;
  background: var(--border);
}
.header-title { font-size: 13px; color: var(--muted); }

.header-right { display: flex; align-items: center; gap: 8px; }
.user-chip {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 999px;
  padding: 5px 12px 5px 5px;
  font-size: 13px;
}
.user-avatar {
  width: 26px; height: 26px;
  border-radius: 50%;
  background: var(--accent);
  color: #000;
  font-weight: 700;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.user-name { color: var(--text); max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.header-actions { display: flex; gap: 4px; }
.hdr-btn {
  background: none;
  border: 1px solid var(--border);
  border-radius: 8px;
  color: var(--muted);
  font-family: 'DM Sans', sans-serif;
  font-size: 12px;
  padding: 6px 10px;
  cursor: pointer;
  transition: all 0.15s;
  display: flex;
  align-items: center;
  gap: 5px;
  white-space: nowrap;
}
.hdr-btn:hover { border-color: var(--text); color: var(--text); }
.hdr-btn.danger:hover { border-color: var(--red); color: var(--red); }
.hdr-btn svg { width: 13px; height: 13px; flex-shrink: 0; }

/* ══════════════════════════════════════════════════════════
   LAYOUT
══════════════════════════════════════════════════════════ */
.container { max-width: 1100px; margin: 0 auto; padding: 32px 24px 80px; }

/* ══════════════════════════════════════════════════════════
   PAGE HEADER + MONTH NAV
══════════════════════════════════════════════════════════ */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
  flex-wrap: wrap;
  gap: 12px;
}
.page-title h1 {
  font-family: 'DM Serif Display', serif;
  font-size: 28px;
  font-weight: 400;
}
.page-title p { color: var(--muted); font-size: 13px; margin-top: 2px; }

.month-controls { display: flex; align-items: center; gap: 6px; }
.month-btn {
  background: none; border: none; color: var(--muted);
  cursor: pointer; padding: 6px; border-radius: 6px;
  transition: all 0.15s; display: flex; align-items: center;
}
.month-btn:hover { background: var(--surface2); color: var(--text); }
.month-btn svg { width: 16px; height: 16px; }
#month-label-btn {
  background: var(--surface2);
  border: 1px solid var(--border);
  color: var(--text);
  padding: 8px 16px;
  border-radius: 8px;
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  min-width: 148px;
  text-align: center;
}
#month-label-btn:hover { border-color: var(--accent); color: var(--accent); }

/* ══════════════════════════════════════════════════════════
   RENDA
══════════════════════════════════════════════════════════ */
.renda-bar {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  gap: 16px;
  flex-wrap: wrap;
}
.renda-bar .renda-label { color: var(--muted); font-size: 13px; }
.renda-input-wrap { display: flex; align-items: center; gap: 8px; }
.renda-input-wrap span { color: var(--muted); font-size: 13px; }
#renda-input {
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 8px;
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  font-size: 15px;
  font-weight: 600;
  padding: 8px 12px;
  width: 160px;
  transition: border-color 0.15s;
  text-align: right;
}
#renda-input:focus { outline: none; border-color: var(--accent); }
.btn-save-renda {
  background: var(--accent); border: none; border-radius: 8px;
  color: #000; font-family: 'DM Sans', sans-serif;
  font-size: 13px; font-weight: 600; padding: 8px 16px;
  cursor: pointer; transition: opacity 0.15s;
}
.btn-save-renda:hover { opacity: 0.85; }

/* ══════════════════════════════════════════════════════════
   TOP CARDS
══════════════════════════════════════════════════════════ */
.top-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}
@media (max-width: 640px) { .top-cards { grid-template-columns: 1fr; } }
.card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; }
.card-label { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
.card-value { font-family: 'DM Serif Display', serif; font-size: 26px; font-weight: 400; }
.card-value.green  { color: var(--green); }
.card-value.red    { color: var(--red); }
.card-value.accent { color: var(--accent); }

/* ══════════════════════════════════════════════════════════
   MAIN GRID (donut + tabela)
══════════════════════════════════════════════════════════ */
.main-grid {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 20px;
  margin-bottom: 24px;
  align-items: start;
}
@media (max-width: 768px) { .main-grid { grid-template-columns: 1fr; } }

.donut-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; }
.donut-card h3 { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; }
#donut-chart-wrap { position: relative; width: 180px; margin: 0 auto 16px; }
.donut-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); text-align: center; pointer-events: none; }
.donut-center .val { font-family: 'DM Serif Display', serif; font-size: 18px; }
.donut-center .sub { font-size: 11px; color: var(--muted); }

.legend { display: flex; flex-direction: column; gap: 6px; }
.legend-item { display: flex; align-items: center; gap: 8px; font-size: 12px; }
.legend-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.legend-name { flex: 1; color: var(--muted); }
.legend-pct { font-weight: 600; }

.summary-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; }
.summary-card h3 { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; }
table { width: 100%; border-collapse: collapse; }
thead th {
  font-size: 11px; color: var(--muted); text-align: left;
  padding: 0 0 10px; text-transform: uppercase; letter-spacing: 0.5px;
  border-bottom: 1px solid var(--border);
}
thead th:not(:first-child) { text-align: right; }
tbody tr { border-bottom: 1px solid var(--border); }
tbody tr:last-child { border-bottom: none; }
tbody td { padding: 10px 0; font-size: 13px; }
tbody td:not(:first-child) { text-align: right; }
.cat-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 8px; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }
.badge.ok   { background: rgba(52,211,153,0.15); color: var(--green); }
.badge.over { background: rgba(248,113,113,0.15); color: var(--red); }
.badge.zero { background: rgba(107,112,128,0.15); color: var(--muted); }

/* ══════════════════════════════════════════════════════════
   CATEGORY TABS + SECTIONS
══════════════════════════════════════════════════════════ */
.section-tabs {
  display: flex; gap: 4px;
  overflow-x: auto; margin-bottom: 20px; padding-bottom: 2px;
}
.tab-btn {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 8px; color: var(--muted);
  font-family: 'DM Sans', sans-serif; font-size: 13px;
  padding: 7px 14px; cursor: pointer; white-space: nowrap;
  transition: all 0.15s; display: flex; align-items: center; gap: 6px;
}
.tab-btn .tab-dot { width: 7px; height: 7px; border-radius: 50%; }
.tab-btn:hover { border-color: var(--text); color: var(--text); }
.tab-btn.active { border-color: var(--accent); color: var(--accent); background: rgba(240,180,41,0.08); }

.cat-section { display: none; }
.cat-section.active { display: block; }

.cat-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; }
.cat-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 8px; }
.cat-header h3 { font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.cat-stats { display: flex; gap: 20px; font-size: 13px; flex-wrap: wrap; }
.cat-stat { display: flex; flex-direction: column; align-items: flex-end; }
.cat-stat .s-label { color: var(--muted); font-size: 11px; margin-bottom: 2px; }
.cat-stat .s-val { font-weight: 600; }

.progress-wrap { margin-bottom: 20px; }
.progress-bar-bg { height: 6px; background: var(--surface2); border-radius: 3px; overflow: hidden; }
.progress-bar-fill { height: 100%; border-radius: 3px; transition: width 0.4s ease; }
.progress-labels { display: flex; justify-content: space-between; font-size: 11px; color: var(--muted); margin-top: 4px; }

.expense-list { margin-bottom: 16px; }
.expense-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border); gap: 8px; }
.expense-row:last-child { border-bottom: none; }
.expense-name { flex: 1; font-size: 14px; }
.expense-val { font-weight: 600; font-size: 14px; margin-right: 8px; }
.icon-btn { background: none; border: none; cursor: pointer; padding: 4px; border-radius: 4px; transition: background 0.15s; color: var(--muted); display: flex; align-items: center; }
.icon-btn:hover { background: var(--surface2); color: var(--text); }
.icon-btn.del:hover { color: var(--red); }
.icon-btn svg { width: 14px; height: 14px; }

.add-expense-form { background: var(--surface2); border: 1px solid var(--border); border-radius: 10px; padding: 16px; margin-bottom: 16px; }
.add-expense-form h4 { font-size: 12px; color: var(--muted); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
.form-row { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
.form-group { display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 120px; }
.form-group label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; }
.form-input { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-family: 'DM Sans', sans-serif; font-size: 14px; padding: 8px 12px; transition: border-color 0.15s; width: 100%; }
.form-input:focus { outline: none; border-color: var(--accent); }
.btn-add { background: var(--accent); border: none; border-radius: 8px; color: #000; font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 600; padding: 9px 18px; cursor: pointer; transition: opacity 0.15s; white-space: nowrap; }
.btn-add:hover { opacity: 0.85; }

.autofill-section { border-top: 1px solid var(--border); padding-top: 16px; margin-top: 4px; }
.autofill-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.autofill-header h4 { font-size: 13px; font-weight: 600; }
.autofill-header p { font-size: 12px; color: var(--muted); margin-top: 2px; }
.btn-outline { background: none; border: 1px solid var(--border); border-radius: 8px; color: var(--muted); font-family: 'DM Sans', sans-serif; font-size: 12px; padding: 6px 12px; cursor: pointer; transition: all 0.15s; white-space: nowrap; }
.btn-outline:hover { border-color: var(--accent); color: var(--accent); }

.recurring-list { margin-top: 10px; display: flex; flex-direction: column; gap: 6px; }
.recurring-row { display: flex; align-items: center; justify-content: space-between; background: var(--surface2); border: 1px solid var(--border); border-radius: 8px; padding: 8px 12px; font-size: 13px; }
.recurring-row span { color: var(--muted); font-size: 12px; }
.btn-add-recurring { margin-top: 8px; background: none; border: 1px dashed var(--border); border-radius: 8px; color: var(--muted); font-family: 'DM Sans', sans-serif; font-size: 13px; padding: 8px; cursor: pointer; width: 100%; transition: all 0.15s; text-align: center; }
.btn-add-recurring:hover { border-color: var(--accent); color: var(--accent); }
.recurring-form { display: none; margin-top: 8px; }
.recurring-form.open { display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-end; }

/* ══════════════════════════════════════════════════════════
   METAS
══════════════════════════════════════════════════════════ */
.metas-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-top: 24px; }
.metas-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 8px; }
.metas-header h3 { font-family: 'DM Serif Display', serif; font-size: 20px; font-weight: 400; }
.metas-body { display: grid; grid-template-columns: 200px 1fr; gap: 24px; align-items: center; }
@media (max-width: 640px) { .metas-body { grid-template-columns: 1fr; } }
#metas-donut-wrap { position: relative; width: 160px; }
.metas-sliders { display: flex; flex-direction: column; gap: 18px; }
.slider-row label { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; }
input[type=range] { -webkit-appearance: none; appearance: none; width: 100%; height: 4px; border-radius: 2px; outline: none; cursor: pointer; }
input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; width: 16px; height: 16px; border-radius: 50%; background: var(--accent); cursor: pointer; border: 2px solid var(--bg); box-shadow: 0 0 0 1px var(--accent); }
.metas-total { font-size: 13px; font-weight: 600; margin-top: 4px; }
.metas-total.ok   { color: var(--green); }
.metas-total.over { color: var(--red); }
.metas-actions { display: flex; gap: 8px; margin-top: 16px; }
.btn-reset { background: none; border: 1px solid var(--border); border-radius: 8px; color: var(--muted); font-family: 'DM Sans', sans-serif; font-size: 13px; padding: 8px 16px; cursor: pointer; transition: all 0.15s; }
.btn-reset:hover { border-color: var(--red); color: var(--red); }
.btn-save-metas { background: var(--accent); border: none; border-radius: 8px; color: #000; font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 600; padding: 8px 20px; cursor: pointer; transition: opacity 0.15s; }
.btn-save-metas:hover { opacity: 0.85; }

/* ══════════════════════════════════════════════════════════
   MODAIS
══════════════════════════════════════════════════════════ */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 200; align-items: center; justify-content: center; padding: 16px; }
.modal-overlay.open { display: flex; }
.modal { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 24px; width: 340px; max-width: 100%; animation: fadeUp 0.2s ease; }
.modal-lg { width: 480px; }
@keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
.modal h3 { font-family: 'DM Serif Display', serif; font-size: 18px; font-weight: 400; margin-bottom: 6px; }
.modal p.modal-sub { font-size: 13px; color: var(--muted); margin-bottom: 16px; }
.modal-close { width: 100%; padding: 10px; background: var(--surface2); border: 1px solid var(--border); border-radius: 8px; color: var(--muted); cursor: pointer; font-family: 'DM Sans', sans-serif; font-size: 13px; margin-top: 8px; transition: all 0.15s; }
.modal-close:hover { color: var(--text); border-color: var(--text); }

/* Month picker */
.month-picker-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin-bottom: 16px; }
.mp-month { padding: 8px 4px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface2); color: var(--text); cursor: pointer; text-align: center; font-size: 13px; transition: all 0.15s; }
.mp-month:hover { border-color: var(--accent); color: var(--accent); }
.mp-month.active { background: var(--accent); border-color: var(--accent); color: #000; font-weight: 600; }
.mp-month.has-data::after { content: '•'; display: block; color: var(--green); font-size: 8px; margin-top: 2px; }
.mp-month.active.has-data::after { color: #000; }
.year-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.year-nav span { font-weight: 600; font-size: 15px; }

/* Edit modal */
.modal-actions { display: flex; gap: 8px; margin-top: 16px; }
.modal-actions .btn-cancel { flex:1; background:none; border:1px solid var(--border); border-radius:8px; color:var(--muted); font-family:'DM Sans',sans-serif; font-size:13px; padding:9px; cursor:pointer; transition:all 0.15s; }
.modal-actions .btn-cancel:hover { border-color:var(--text); color:var(--text); }
.modal-actions .btn-confirm { flex:1; background:var(--accent); border:none; border-radius:8px; color:#000; font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600; padding:9px; cursor:pointer; transition:opacity 0.15s; }
.modal-actions .btn-confirm:hover { opacity:0.85; }

/* Migração modal */
.migrate-options { display: flex; flex-direction: column; gap: 10px; margin-top: 16px; }
.migrate-opt { padding: 14px 16px; background: var(--surface2); border: 1px solid var(--border); border-radius: 10px; cursor: pointer; transition: all 0.15s; }
.migrate-opt:hover { border-color: var(--accent); }
.migrate-opt strong { display: block; font-size: 14px; margin-bottom: 3px; }
.migrate-opt span { font-size: 12px; color: var(--muted); }
.migrate-opt.selected { border-color: var(--accent); background: rgba(240,180,41,0.08); }

/* Import/Export panel */
.ie-panel { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }

/* ══════════════════════════════════════════════════════════
   TOAST
══════════════════════════════════════════════════════════ */
#toast { position: fixed; bottom: 24px; right: 24px; background: var(--green); color: #000; font-weight: 600; font-size: 13px; padding: 10px 18px; border-radius: 8px; z-index: 400; opacity: 0; transform: translateY(8px); transition: all 0.2s; pointer-events: none; max-width: 300px; }
#toast.show { opacity: 1; transform: translateY(0); }

/* Loading overlay */
#page-loader { position: fixed; inset: 0; background: var(--bg); z-index: 500; display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 16px; transition: opacity 0.3s; }
#page-loader.hidden { opacity: 0; pointer-events: none; }
.loader-ring { width: 36px; height: 36px; border: 3px solid var(--border); border-top-color: var(--accent); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.empty-state { text-align: center; color: var(--muted); font-size: 13px; padding: 20px 0; }

::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
</style>
</head>
<body>

<!-- ══ LOADING OVERLAY ══════════════════════════════════════ -->
<div id="page-loader">
  <div class="loader-ring"></div>
  <span style="color:var(--muted);font-size:13px;">Carregando dados…</span>
</div>

<!-- ══ HEADER ════════════════════════════════════════════════ -->
<header>
  <div class="header-left">
    <div class="header-logo">
      <?php if (file_exists(__DIR__ . '/assets/logo.png')): ?>
        <img src="assets/logo.png" alt="Logo">
      <?php else: ?>
        <span class="header-logo-text">Orçamento</span>
      <?php endif; ?>
    </div>
    <div class="header-sep"></div>
    <span class="header-title">Doméstico</span>
  </div>
  <div class="header-right">
    <div class="header-actions">
      <button class="hdr-btn" onclick="openImportExportModal()" title="Importar / Exportar dados">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Dados
      </button>
      <a href="api/export.php" class="hdr-btn" title="Exportar JSON" download>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Exportar
      </a>
    </div>
    <div class="user-chip">
      <div class="user-avatar"><?= strtoupper(mb_substr($userName, 0, 1)) ?></div>
      <span class="user-name"><?= $userName ?></span>
    </div>
    <button class="hdr-btn danger" onclick="doLogout()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Sair
    </button>
  </div>
</header>

<!-- ══ MAIN ══════════════════════════════════════════════════ -->
<div class="container">

  <!-- Page header -->
  <div class="page-header">
    <div class="page-title">
      <h1>Orçamento Doméstico</h1>
      <p>Controle seu orçamento com base em suas metas e rendimentos.</p>
    </div>
    <div class="month-controls">
      <button class="month-btn" id="prev-month">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <button id="month-label-btn"></button>
      <button class="month-btn" id="next-month">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>
  </div>

  <!-- Renda -->
  <div class="renda-bar">
    <div class="renda-label">Renda do mês</div>
    <div class="renda-input-wrap">
      <span>R$</span>
      <input type="text" id="renda-input" value="0,00" />
      <button class="btn-save-renda" onclick="saveRenda()">Salvar</button>
    </div>
  </div>

  <!-- Cards -->
  <div class="top-cards">
    <div class="card">
      <div class="card-label">Total Gasto</div>
      <div class="card-value red" id="card-gasto">R$ 0,00</div>
    </div>
    <div class="card">
      <div class="card-label">Total a Gastar (Renda)</div>
      <div class="card-value accent" id="card-orcamento">R$ 0,00</div>
    </div>
    <div class="card">
      <div class="card-label">Utilizado</div>
      <div class="card-value green" id="card-pct">0%</div>
    </div>
  </div>

  <!-- Donut + Tabela -->
  <div class="main-grid">
    <div class="donut-card">
      <h3>Gastos por Categoria</h3>
      <div id="donut-chart-wrap">
        <canvas id="donutChart" width="180" height="180"></canvas>
        <div class="donut-center">
          <div class="val" id="donut-total">R$ 0</div>
          <div class="sub">Total</div>
        </div>
      </div>
      <div class="legend" id="donut-legend"></div>
    </div>
    <div class="summary-card">
      <h3>Resumo</h3>
      <table>
        <thead>
          <tr>
            <th>Categoria</th>
            <th>Gasto</th>
            <th>Orçado</th>
            <th>Status</th>
            <th>% Renda</th>
          </tr>
        </thead>
        <tbody id="summary-tbody"></tbody>
      </table>
    </div>
  </div>

  <!-- Category tabs -->
  <div class="section-tabs" id="cat-tabs"></div>
  <div id="cat-sections"></div>

  <!-- Metas -->
  <div class="metas-card">
    <div class="metas-header">
      <div>
        <h3>Minhas Metas</h3>
        <p style="font-size:12px;color:var(--muted);margin-top:2px;">Percentuais deste mês. Ao criar um novo mês, herdam do anterior.</p>
      </div>
    </div>
    <div class="metas-body">
      <div id="metas-donut-wrap">
        <canvas id="metasDonut" width="160" height="160"></canvas>
      </div>
      <div>
        <div class="metas-sliders" id="metas-sliders"></div>
        <div class="metas-total" id="metas-total"></div>
        <div class="metas-actions">
          <button class="btn-reset" onclick="resetMetas()">Resetar</button>
          <button class="btn-save-metas" onclick="saveMetas()">Salvar Metas</button>
        </div>
      </div>
    </div>
  </div>

</div><!-- /container -->

<!-- ══ MODAL: Seletor de mês ════════════════════════════════ -->
<div class="modal-overlay" id="month-modal">
  <div class="modal">
    <h3>Selecionar Mês</h3>
    <div class="year-nav">
      <button class="month-btn" id="mp-prev-year">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <span id="mp-year"></span>
      <button class="month-btn" id="mp-next-year">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>
    <div class="month-picker-grid" id="mp-grid"></div>
    <button class="modal-close" onclick="closeModal('month-modal')">Fechar</button>
  </div>
</div>

<!-- ══ MODAL: Editar gasto ══════════════════════════════════ -->
<div class="modal-overlay" id="edit-modal">
  <div class="modal">
    <h3>Editar Gasto</h3>
    <div style="display:flex;flex-direction:column;gap:12px;margin-top:12px;">
      <div class="form-group">
        <label>Nome</label>
        <input type="text" class="form-input" id="edit-name" />
      </div>
      <div class="form-group">
        <label>Valor (R$)</label>
        <input type="text" class="form-input val-input" id="edit-value" />
      </div>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeModal('edit-modal')">Cancelar</button>
      <button class="btn-confirm" onclick="confirmEdit()">Salvar</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Migrar localStorage ══════════════════════════ -->
<div class="modal-overlay" id="migrate-modal">
  <div class="modal modal-lg">
    <h3>Dados locais encontrados</h3>
    <p class="modal-sub">Você tem dados salvos neste navegador de uma versão anterior. Deseja importá-los para o servidor?</p>
    <div class="migrate-options">
      <div class="migrate-opt" id="opt-merge" onclick="selectMigrateOpt('merge')">
        <strong>✦ Mesclar (recomendado)</strong>
        <span>Importa os dados locais sem apagar o que já existe no servidor.</span>
      </div>
      <div class="migrate-opt" id="opt-replace" onclick="selectMigrateOpt('replace')">
        <strong>↺ Substituir</strong>
        <span>Apaga todos os dados do servidor e importa apenas os dados locais.</span>
      </div>
      <div class="migrate-opt" id="opt-skip" onclick="selectMigrateOpt('skip')">
        <strong>✕ Ignorar</strong>
        <span>Não importar. Os dados locais continuarão no navegador.</span>
      </div>
    </div>
    <div class="modal-actions" style="margin-top:20px;">
      <button class="btn-confirm" onclick="executeMigration()" style="flex:2;">Confirmar</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Import/Export ═════════════════════════════════ -->
<div class="modal-overlay" id="ie-modal">
  <div class="modal modal-lg">
    <h3>Importar / Exportar</h3>
    <p class="modal-sub">Faça backup dos seus dados ou restaure a partir de um arquivo JSON.</p>
    <div style="display:flex;flex-direction:column;gap:16px;margin-top:4px;">
      <div>
        <p style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.6px;margin-bottom:8px;">Exportar</p>
        <a href="api/export.php" class="btn-add" style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;" download>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Baixar backup JSON
        </a>
      </div>
      <div>
        <p style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.6px;margin-bottom:8px;">Importar arquivo JSON</p>
        <div style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
          <div class="form-group" style="flex:1;min-width:180px;">
            <label>Arquivo</label>
            <input type="file" class="form-input" id="import-file" accept=".json" />
          </div>
          <div class="form-group" style="min-width:130px;">
            <label>Modo</label>
            <select class="form-input" id="import-mode">
              <option value="merge">Mesclar</option>
              <option value="replace">Substituir tudo</option>
            </select>
          </div>
          <button class="btn-add" onclick="doImportFile()">Importar</button>
        </div>
      </div>
      <div>
        <p style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.6px;margin-bottom:8px;">Migrar do localStorage (este navegador)</p>
        <button class="btn-outline" onclick="checkAndMigrate()">Verificar dados locais</button>
      </div>
    </div>
    <button class="modal-close" onclick="closeModal('ie-modal')">Fechar</button>
  </div>
</div>

<!-- Toast -->
<div id="toast"></div>

<!-- ══════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════ -->
<script>
// ── Categorias ────────────────────────────────────────────
const CATEGORIES = [
  { id:'fixos',        name:'Custos Fixos',        color:'#60a5fa' },
  { id:'conforto',     name:'Conforto',             color:'#34d399' },
  { id:'metas',        name:'Metas',                color:'#a78bfa' },
  { id:'prazeres',     name:'Prazeres',             color:'#f472b6' },
  { id:'liberdade',    name:'Liberdade Financeira', color:'#f0b429' },
  { id:'conhecimento', name:'Conhecimento',         color:'#fb923c' },
];
const DEFAULT_METAS = {fixos:10,conforto:0,metas:5,prazeres:18,liberdade:50,conhecimento:17};
const MONTH_NAMES  = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
const MONTH_SHORT  = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];

// ── Estado ────────────────────────────────────────────────
let currentYear, currentMonth;
let monthData   = { renda:0, metas:{...DEFAULT_METAS}, expenses:{} };
let recurringData = {};
let mesesComDados = new Set();
let donutChart  = null;
let metasChart  = null;
let tempMetas   = {};
let editCtx     = null;
let mpYear;
let migrateMode = 'merge';

// ── Init ──────────────────────────────────────────────────
async function init() {
  const now = new Date();
  currentYear  = now.getFullYear();
  currentMonth = now.getMonth() + 1; // API usa 1–12
  mpYear = currentYear;

  buildCategorySections();
  bindEvents();

  await Promise.all([loadMesesComDados(), loadRecorrentes()]);
  await loadCurrentMonth();

  hideLoader();
  checkMigration();
}

function hideLoader() {
  document.getElementById('page-loader').classList.add('hidden');
  setTimeout(() => { document.getElementById('page-loader').style.display='none'; }, 400);
}

// ── Eventos globais ───────────────────────────────────────
function bindEvents() {
  document.getElementById('prev-month').onclick = () => navigateMonth(-1);
  document.getElementById('next-month').onclick = () => navigateMonth(1);
  document.getElementById('month-label-btn').onclick = openMonthModal;
  document.getElementById('mp-prev-year').onclick = () => { mpYear--; renderMonthPicker(); };
  document.getElementById('mp-next-year').onclick = () => { mpYear++; renderMonthPicker(); };

  // Fechar modais clicando fora
  document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) closeModal(o.id); });
  });

  // Máscara BRL
  document.addEventListener('input', e => {
    if (e.target.classList.contains('val-input')) formatInputBRL(e.target);
  });
  const ri = document.getElementById('renda-input');
  ri.addEventListener('input', () => formatInputBRL(ri));
  ri.addEventListener('keydown', e => { if (e.key==='Enter') saveRenda(); });

  // Enter nos campos de gasto
  document.addEventListener('keydown', e => {
    if (e.key !== 'Enter') return;
    CATEGORIES.forEach(cat => {
      if ([`new-name-${cat.id}`,`new-val-${cat.id}`].includes(e.target.id)) addExpense(cat.id);
    });
  });
}

// ── Navegação de mês ──────────────────────────────────────
function navigateMonth(delta) {
  currentMonth += delta;
  if (currentMonth > 12) { currentMonth = 1;  currentYear++; }
  if (currentMonth < 1)  { currentMonth = 12; currentYear--; }
  loadCurrentMonth();
}

// ── Carrega mês do servidor ───────────────────────────────
async function loadCurrentMonth() {
  document.getElementById('month-label-btn').textContent =
    MONTH_NAMES[currentMonth-1] + '/' + currentYear;

  try {
    const res  = await apiFetch(`api/dados.php?action=mes&ano=${currentYear}&mes=${currentMonth}`);
    monthData  = res;
    tempMetas  = { ...res.metas };

    // Atualiza campo de renda
    const ri = document.getElementById('renda-input');
    ri.value = (res.renda||0).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});

    renderMetasSliders();
    renderMetasDonut();
    renderAllExpenses();
    updateSummary();
    await loadMesesComDados();
  } catch(e) {
    toast('Erro ao carregar dados: ' + e.message, true);
  }
}

// ── Meses com dados ───────────────────────────────────────
async function loadMesesComDados() {
  try {
    const res = await apiFetch('api/dados.php?action=meses_com_dados');
    mesesComDados = new Set(res.meses);
  } catch {}
}

// ── Recorrentes ───────────────────────────────────────────
async function loadRecorrentes() {
  try {
    const res = await apiFetch('api/recorrentes.php?action=listar');
    recurringData = res.recorrentes || {};
    CATEGORIES.forEach(cat => renderRecurringList(cat));
  } catch {}
}

// ── Renda ─────────────────────────────────────────────────
async function saveRenda() {
  const v = parseBRL(document.getElementById('renda-input').value);
  try {
    await apiPost('api/dados.php', { action:'salvar_renda', ano:currentYear, mes:currentMonth, renda:v });
    monthData.renda = v;
    updateSummary();
    toast('Renda salva!');
  } catch(e) { toast(e.message, true); }
}

// ── Metas ─────────────────────────────────────────────────
async function saveMetas() {
  const total = Object.values(tempMetas).reduce((s,v)=>s+v,0);
  if (Math.abs(total-100) > 0.01) { toast('As metas precisam somar 100%!', true); return; }
  try {
    await apiPost('api/dados.php', { action:'salvar_metas', ano:currentYear, mes:currentMonth, metas:JSON.stringify(tempMetas) });
    monthData.metas = { ...tempMetas };
    renderAllExpenses();
    updateSummary();
    toast('Metas salvas!');
  } catch(e) { toast(e.message, true); }
}

function resetMetas() {
  tempMetas = { ...DEFAULT_METAS };
  renderMetasSliders();
  renderMetasDonut();
}

// ── Gastos ────────────────────────────────────────────────
async function addExpense(catId) {
  const nameEl = document.getElementById(`new-name-${catId}`);
  const valEl  = document.getElementById(`new-val-${catId}`);
  const nome   = nameEl.value.trim();
  const valor  = parseBRL(valEl.value);
  if (!nome || valor <= 0) { toast('Preencha nome e valor!', true); return; }

  try {
    const res = await apiPost('api/dados.php', { action:'add_gasto', ano:currentYear, mes:currentMonth, categoria:catId, nome, valor });
    if (!monthData.expenses[catId]) monthData.expenses[catId] = [];
    monthData.expenses[catId].push({ id:res.id, name:nome, value:valor });
    nameEl.value = ''; valEl.value = '';
    renderAllExpenses();
    updateSummary();
    await loadMesesComDados();
    toast('Gasto adicionado!');
  } catch(e) { toast(e.message, true); }
}

function openEditModal(catId, expId) {
  const cat = monthData.expenses[catId] || [];
  const exp = cat.find(e => e.id === expId);
  if (!exp) return;
  editCtx = { catId, expId };
  document.getElementById('edit-name').value  = exp.name;
  document.getElementById('edit-value').value = exp.value.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});
  openModal('edit-modal');
}

async function confirmEdit() {
  if (!editCtx) return;
  const nome  = document.getElementById('edit-name').value.trim();
  const valor = parseBRL(document.getElementById('edit-value').value);
  if (!nome || valor <= 0) { toast('Preencha nome e valor!', true); return; }
  try {
    await apiPost('api/dados.php', { action:'edit_gasto', id:editCtx.expId, nome, valor });
    const cat = monthData.expenses[editCtx.catId];
    const exp = cat.find(e => e.id === editCtx.expId);
    if (exp) { exp.name = nome; exp.value = valor; }
    closeModal('edit-modal');
    renderAllExpenses();
    updateSummary();
    toast('Gasto atualizado!');
  } catch(e) { toast(e.message, true); }
}

async function deleteExpense(catId, expId) {
  try {
    await apiPost('api/dados.php', { action:'del_gasto', id:expId });
    monthData.expenses[catId] = (monthData.expenses[catId]||[]).filter(e=>e.id!==expId);
    renderAllExpenses();
    updateSummary();
    toast('Gasto removido!');
  } catch(e) { toast(e.message, true); }
}

// ── Recorrentes ───────────────────────────────────────────
function toggleRecurringForm(catId) {
  document.getElementById(`rec-form-${catId}`).classList.toggle('open');
}

async function addRecurring(catId) {
  const nameEl = document.getElementById(`rec-name-${catId}`);
  const valEl  = document.getElementById(`rec-val-${catId}`);
  const nome   = nameEl.value.trim();
  const valor  = parseBRL(valEl.value);
  if (!nome || valor <= 0) { toast('Preencha nome e valor!', true); return; }

  try {
    const res = await apiPost('api/recorrentes.php', { action:'add', categoria:catId, nome, valor });
    if (!recurringData[catId]) recurringData[catId] = [];
    recurringData[catId].push({ id:res.id, name:nome, value:valor });
    nameEl.value = ''; valEl.value = '';
    toggleRecurringForm(catId);
    renderRecurringList(CATEGORIES.find(c=>c.id===catId));
    toast('Recorrente salvo!');
  } catch(e) { toast(e.message, true); }
}

async function deleteRecurring(catId, recId) {
  try {
    await apiPost('api/recorrentes.php', { action:'del', id:recId });
    recurringData[catId] = (recurringData[catId]||[]).filter(r=>r.id!==recId);
    renderRecurringList(CATEGORIES.find(c=>c.id===catId));
    toast('Recorrente removido!');
  } catch(e) { toast(e.message, true); }
}

async function applyRecurring(catId) {
  const items = recurringData[catId] || [];
  if (!items.length) { toast('Nenhum recorrente nesta categoria!', true); return; }
  try {
    const res = await apiPost('api/recorrentes.php', { action:'aplicar', ano:currentYear, mes:currentMonth, categoria:catId });
    // Recarrega o mês inteiro para ter os IDs corretos
    const data = await apiFetch(`api/dados.php?action=mes&ano=${currentYear}&mes=${currentMonth}`);
    monthData = data;
    renderAllExpenses();
    updateSummary();
    toast(`${res.adicionados} recorrente(s) adicionado(s)!`);
  } catch(e) { toast(e.message, true); }
}

// ── Render: lista de gastos ───────────────────────────────
function renderAllExpenses() {
  CATEGORIES.forEach(cat => renderCatExpenses(cat));
}

function renderCatExpenses(cat) {
  const listEl = document.getElementById(`list-${cat.id}`);
  if (!listEl) return;
  const expenses = (monthData.expenses||{})[cat.id] || [];
  const renda    = monthData.renda || 0;
  const metas    = monthData.metas || DEFAULT_METAS;
  const budget   = renda * (metas[cat.id]||0) / 100;
  const total    = expenses.reduce((s,e)=>s+e.value,0);
  const overBudget = budget > 0 && total > budget;
  const pct = budget > 0 ? Math.min((total/budget)*100,100) : 0;

  // Lista
  listEl.innerHTML = expenses.length === 0
    ? '<div class="empty-state">Nenhum gasto lançado nesta categoria.</div>'
    : expenses.map(e => `
      <div class="expense-row">
        <span class="expense-name">${escHtml(e.name)}${e.recorrente ? ' <span style="font-size:10px;color:var(--muted);background:var(--surface2);padding:1px 6px;border-radius:4px;margin-left:4px;">recorrente</span>' : ''}</span>
        <span class="expense-val">${fmtBRL(e.value)}</span>
        <button class="icon-btn" onclick="openEditModal('${cat.id}',${e.id})" title="Editar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </button>
        <button class="icon-btn del" onclick="deleteExpense('${cat.id}',${e.id})" title="Remover">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
        </button>
      </div>`).join('');

  // Progress
  const fill = document.getElementById(`prog-${cat.id}`);
  if (fill) { fill.style.width = pct+'%'; fill.style.background = overBudget ? 'var(--red)' : cat.color; }
  const lbl = document.getElementById(`prog-label-${cat.id}`);
  const bud = document.getElementById(`prog-budget-${cat.id}`);
  if (lbl) lbl.textContent = fmtBRL(total) + ' gastos';
  if (bud) bud.textContent = fmtBRL(budget) + ' orçados';

  // Stats
  const statsEl = document.getElementById(`stats-${cat.id}`);
  if (statsEl) {
    const util  = budget > 0 ? ((total/budget)*100).toFixed(0)+'%' : '–';
    const color = overBudget ? 'var(--red)' : total===0 ? 'var(--muted)' : 'var(--green)';
    statsEl.innerHTML = `
      <div class="cat-stat"><span class="s-label">Gasto</span><span class="s-val" style="color:var(--red)">${fmtBRL(total)}</span></div>
      <div class="cat-stat"><span class="s-label">Orçado</span><span class="s-val" style="color:var(--accent)">${fmtBRL(budget)}</span></div>
      <div class="cat-stat"><span class="s-label">Utilizado</span><span class="s-val" style="color:${color}">${util}</span></div>`;
  }

  renderRecurringList(cat);
}

function renderRecurringList(cat) {
  const el = document.getElementById(`rec-list-${cat.id}`);
  if (!el) return;
  const items = (recurringData[cat.id] || []);
  el.innerHTML = items.length === 0 ? '' : items.map(r => `
    <div class="recurring-row">
      <span style="color:var(--text)">${escHtml(r.name)}</span>
      <span>${fmtBRL(r.value)}</span>
      <button class="icon-btn del" onclick="deleteRecurring('${cat.id}',${r.id})" title="Remover">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
      </button>
    </div>`).join('');
}

// ── Render: sumário + donut ───────────────────────────────
function updateSummary() {
  const renda    = monthData.renda || 0;
  const metas    = monthData.metas || DEFAULT_METAS;
  const expenses = monthData.expenses || {};

  const totals = {};
  CATEGORIES.forEach(cat => { totals[cat.id] = (expenses[cat.id]||[]).reduce((s,e)=>s+e.value,0); });

  const totalGasto = Object.values(totals).reduce((s,v)=>s+v,0);
  const pctUsado   = renda > 0 ? Math.round((totalGasto/renda)*100) : 0;

  document.getElementById('card-gasto').textContent     = fmtBRL(totalGasto);
  document.getElementById('card-orcamento').textContent = fmtBRL(renda);
  document.getElementById('card-pct').textContent       = pctUsado+'%';
  document.getElementById('card-pct').className = 'card-value ' + (pctUsado>100?'red':pctUsado>80?'accent':'green');

  document.getElementById('donut-total').textContent = 'R$ '+Math.round(totalGasto).toLocaleString('pt-BR');
  const catValues = CATEGORIES.map(cat => totals[cat.id]||0);
  const hasAny    = catValues.some(v=>v>0);

  if (donutChart) donutChart.destroy();
  donutChart = new Chart(document.getElementById('donutChart').getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: CATEGORIES.map(c=>c.name),
      datasets: [{ data: hasAny?catValues:[1], backgroundColor: hasAny?CATEGORIES.map(c=>c.color):['#2a2d35'], borderWidth:0, hoverOffset:4 }]
    },
    options: { cutout:'72%', plugins:{ legend:{display:false}, tooltip:{enabled:hasAny} }, animation:{duration:400} }
  });

  document.getElementById('donut-legend').innerHTML = CATEGORIES.map(cat => `
    <div class="legend-item">
      <span class="legend-dot" style="background:${cat.color}"></span>
      <span class="legend-name">${cat.name}</span>
      <span class="legend-pct">${renda>0?((totals[cat.id]/renda)*100).toFixed(1):0}%</span>
    </div>`).join('');

  document.getElementById('summary-tbody').innerHTML = CATEGORIES.map(cat => {
    const gasto  = totals[cat.id]||0;
    const budget = renda*(metas[cat.id]||0)/100;
    const util   = budget>0?(gasto/budget)*100:(gasto>0?Infinity:0);
    const rendaPct = renda>0?((gasto/renda)*100).toFixed(1):'0.0';
    let badge, cls;
    if (util===0&&gasto===0) { badge='–'; cls='zero'; }
    else if (util>100)       { badge=util.toFixed(0)+'%'; cls='over'; }
    else                     { badge=util.toFixed(0)+'%'; cls='ok'; }
    return `<tr>
      <td><span class="cat-dot" style="background:${cat.color}"></span>${cat.name}</td>
      <td>${fmtBRL(gasto)}</td>
      <td>${fmtBRL(budget)}</td>
      <td><span class="badge ${cls}">${badge}</span></td>
      <td>${rendaPct}%</td>
    </tr>`;
  }).join('');
}

// ── Render: sliders de metas ──────────────────────────────
function renderMetasSliders() {
  const container = document.getElementById('metas-sliders');
  container.innerHTML = '';
  CATEGORIES.forEach(cat => {
    const val = tempMetas[cat.id] ?? 0;
    const row = document.createElement('div');
    row.className = 'slider-row';
    row.innerHTML = `
      <label>
        <span style="display:flex;align-items:center;gap:6px;">
          <span style="width:8px;height:8px;border-radius:50%;background:${cat.color};display:inline-block;flex-shrink:0;"></span>
          ${cat.name}
        </span>
        <span id="spct-${cat.id}">${val}%</span>
      </label>
      <input type="range" min="0" max="100" value="${val}" id="slider-${cat.id}"
        style="background:linear-gradient(to right,${cat.color} ${val}%,var(--surface2) ${val}%)"
        oninput="onSliderInput('${cat.id}',this)" />`;
    container.appendChild(row);
  });
  updateMetasTotal();
}

function onSliderInput(catId, el) {
  const v = parseInt(el.value);
  tempMetas[catId] = v;
  document.getElementById(`spct-${catId}`).textContent = v+'%';
  el.style.background = `linear-gradient(to right,${CATEGORIES.find(c=>c.id===catId).color} ${v}%,var(--surface2) ${v}%)`;
  updateMetasTotal();
  renderMetasDonut();
}

function updateMetasTotal() {
  const total = Object.values(tempMetas).reduce((s,v)=>s+v,0);
  const el = document.getElementById('metas-total');
  el.textContent = `Total: ${total}%`;
  el.className = 'metas-total ' + (Math.abs(total-100)<0.01?'ok':'over');
}

function renderMetasDonut() {
  if (metasChart) metasChart.destroy();
  const values = CATEGORIES.map(c=>tempMetas[c.id]||0);
  const hasAny = values.some(v=>v>0);
  metasChart = new Chart(document.getElementById('metasDonut').getContext('2d'), {
    type:'doughnut',
    data:{ labels:CATEGORIES.map(c=>c.name), datasets:[{ data:hasAny?values:[1], backgroundColor:hasAny?CATEGORIES.map(c=>c.color):['#2a2d35'], borderWidth:0 }] },
    options:{ cutout:'65%', plugins:{legend:{display:false},tooltip:{enabled:hasAny}}, animation:{duration:300} }
  });
}

// ── Build: seções de categoria ────────────────────────────
function buildCategorySections() {
  const tabsEl     = document.getElementById('cat-tabs');
  const sectionsEl = document.getElementById('cat-sections');
  tabsEl.innerHTML = ''; sectionsEl.innerHTML = '';

  CATEGORIES.forEach((cat, idx) => {
    // Tab
    const tab = document.createElement('button');
    tab.className = 'tab-btn' + (idx===0?' active':'');
    tab.dataset.cat = cat.id;
    tab.innerHTML = `<span class="tab-dot" style="background:${cat.color}"></span>${cat.name}`;
    tab.onclick = () => {
      document.querySelectorAll('.tab-btn').forEach(t=>t.classList.remove('active'));
      document.querySelectorAll('.cat-section').forEach(s=>s.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById('sec-'+cat.id).classList.add('active');
    };
    tabsEl.appendChild(tab);

    // Section
    const sec = document.createElement('div');
    sec.className = 'cat-section' + (idx===0?' active':'');
    sec.id = 'sec-' + cat.id;
    sec.innerHTML = `
    <div class="cat-card">
      <div class="cat-header">
        <h3><span style="width:10px;height:10px;border-radius:50%;background:${cat.color};display:inline-block;"></span>${cat.name}</h3>
        <div class="cat-stats" id="stats-${cat.id}"></div>
      </div>
      <div class="progress-wrap">
        <div class="progress-bar-bg"><div class="progress-bar-fill" id="prog-${cat.id}" style="background:${cat.color};width:0%"></div></div>
        <div class="progress-labels"><span id="prog-label-${cat.id}">R$ 0,00 gastos</span><span id="prog-budget-${cat.id}">R$ 0,00 orçados</span></div>
      </div>
      <div class="expense-list" id="list-${cat.id}"></div>
      <div class="add-expense-form">
        <h4>Adicionar gasto</h4>
        <div class="form-row">
          <div class="form-group"><label>Nome</label><input type="text" class="form-input" id="new-name-${cat.id}" placeholder="Ex: Conta de luz" /></div>
          <div class="form-group" style="max-width:160px"><label>Valor (R$)</label><input type="text" class="form-input val-input" id="new-val-${cat.id}" placeholder="0,00" /></div>
          <button class="btn-add" onclick="addExpense('${cat.id}')">Adicionar</button>
        </div>
      </div>
      <div class="autofill-section">
        <div class="autofill-header">
          <div><h4>Auto Preenchimento</h4><p>Gastos recorrentes que se repetem todo mês.</p></div>
          <button class="btn-outline" onclick="applyRecurring('${cat.id}')">Preencher</button>
        </div>
        <div class="recurring-list" id="rec-list-${cat.id}"></div>
        <button class="btn-add-recurring" onclick="toggleRecurringForm('${cat.id}')">+ Adicionar recorrente</button>
        <div class="recurring-form" id="rec-form-${cat.id}">
          <div class="form-group"><label>Nome</label><input type="text" class="form-input" id="rec-name-${cat.id}" placeholder="Nome" /></div>
          <div class="form-group" style="max-width:140px"><label>Valor (R$)</label><input type="text" class="form-input val-input" id="rec-val-${cat.id}" placeholder="0,00" /></div>
          <button class="btn-add" onclick="addRecurring('${cat.id}')">Salvar</button>
          <button class="btn-outline" onclick="toggleRecurringForm('${cat.id}')">Cancelar</button>
        </div>
      </div>
    </div>`;
    sectionsEl.appendChild(sec);
  });
}

// ── Month picker modal ────────────────────────────────────
function openMonthModal() {
  mpYear = currentYear;
  renderMonthPicker();
  openModal('month-modal');
}
function renderMonthPicker() {
  document.getElementById('mp-year').textContent = mpYear;
  const grid = document.getElementById('mp-grid');
  grid.innerHTML = '';
  MONTH_SHORT.forEach((name, i) => {
    const mes = i + 1;
    const btn = document.createElement('button');
    btn.className = 'mp-month';
    btn.textContent = name;
    const key = `${mpYear}_${String(mes).padStart(2,'0')}`;
    if (mesesComDados.has(key)) btn.classList.add('has-data');
    if (mpYear===currentYear && mes===currentMonth) btn.classList.add('active');
    btn.onclick = () => { currentYear=mpYear; currentMonth=mes; loadCurrentMonth(); closeModal('month-modal'); };
    grid.appendChild(btn);
  });
}

// ── Modal helpers ─────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function openImportExportModal() { openModal('ie-modal'); }

// ── Logout ────────────────────────────────────────────────
async function doLogout() {
  const fd = new FormData();
  fd.append('action', 'logout');
  await fetch('api/auth.php', { method:'POST', body:fd });
  window.location.href = 'login.php';
}

// ── Migração localStorage → servidor ──────────────────────
function checkMigration() {
  if (sessionStorage.getItem('migrar') === '1') {
    sessionStorage.removeItem('migrar');
    if (hasLocalStorageData()) openModal('migrate-modal');
  }
}

function checkAndMigrate() {
  closeModal('ie-modal');
  if (!hasLocalStorageData()) { toast('Nenhum dado encontrado no localStorage.', true); return; }
  openModal('migrate-modal');
}

function hasLocalStorageData() {
  for (let i=0;i<localStorage.length;i++) {
    if (localStorage.key(i).startsWith('orcamento_2')) return true;
  }
  return false;
}

function selectMigrateOpt(opt) {
  migrateMode = opt;
  document.querySelectorAll('.migrate-opt').forEach(el => el.classList.remove('selected'));
  document.getElementById('opt-'+opt).classList.add('selected');
}

async function executeMigration() {
  if (migrateMode === 'skip') { closeModal('migrate-modal'); toast('Dados locais mantidos no navegador.'); return; }

  // Monta payload no formato esperado pelo import.php
  const dados = {};
  for (let i=0;i<localStorage.length;i++) {
    const k = localStorage.key(i);
    if (k.startsWith('orcamento_2')) {
      try { dados[k] = JSON.parse(localStorage.getItem(k)); } catch {}
    }
  }
  const recorrente = JSON.parse(localStorage.getItem('orcamento_recurring')||'{}');

  const payload = JSON.stringify({ tipo:'localStorage', dados, recurring:recorrente });

  try {
    const res = await fetch(`api/import.php?modo=${migrateMode}`, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: payload,
    });
    const data = await res.json();
    if (!data.ok) throw new Error(data.error);

    closeModal('migrate-modal');
    toast(`Migração concluída! ${data.meses} meses, ${data.gastos} gastos importados.`);
    await loadMesesComDados();
    await loadCurrentMonth();
  } catch(e) { toast('Erro na migração: '+e.message, true); }
}

// ── Import arquivo ────────────────────────────────────────
async function doImportFile() {
  const file = document.getElementById('import-file').files[0];
  if (!file) { toast('Selecione um arquivo JSON.', true); return; }
  const modo = document.getElementById('import-mode').value;

  const fd = new FormData();
  fd.append('arquivo', file);
  fd.append('modo', modo);

  try {
    const res  = await fetch('api/import.php', { method:'POST', body:fd });
    const data = await res.json();
    if (!data.ok) throw new Error(data.error);
    closeModal('ie-modal');
    toast(`Importado! ${data.meses} meses, ${data.gastos} gastos.`);
    await loadMesesComDados();
    await loadCurrentMonth();
  } catch(e) { toast('Erro na importação: '+e.message, true); }
}

// ── API helpers ───────────────────────────────────────────
async function apiFetch(url) {
  const res  = await fetch(url);
  const data = await res.json();
  if (!data.ok) throw new Error(data.error || 'Erro na API');
  return data;
}

async function apiPost(url, params) {
  const fd = new FormData();
  Object.entries(params).forEach(([k,v]) => fd.append(k, v));
  const res  = await fetch(url, { method:'POST', body:fd });
  const data = await res.json();
  if (!data.ok) throw new Error(data.error || 'Erro na API');
  return data;
}

// ── Formatação ────────────────────────────────────────────
function fmtBRL(v) {
  return 'R$ '+v.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});
}
function parseBRL(s) {
  return parseFloat(s.replace(/[^0-9,]/g,'').replace(',','.')) || 0;
}
function formatInputBRL(input) {
  let v = input.value.replace(/[^0-9]/g,'');
  if (!v) v='0';
  input.value = (parseInt(v,10)/100).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});
}
function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Toast ─────────────────────────────────────────────────
let toastTimer;
function toast(msg, isError=false) {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.style.background = isError ? 'var(--red)' : 'var(--green)';
  el.style.color = isError ? '#fff' : '#000';
  el.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(()=>el.classList.remove('show'), 3000);
}

// ── Start ─────────────────────────────────────────────────
init();
</script>
</body>
</html>
