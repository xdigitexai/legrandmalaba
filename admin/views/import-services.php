<?php require admin_view('header'); ?>

<style>
.import-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e3e8ef;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
body.dark-mode .import-card {
    background: #1e2130;
    border-color: #2e3347;
}
.import-hero {
    text-align: center;
    padding: 16px 0 28px;
}
.import-hero h2 {
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 6px;
}
.import-hero p {
    color: #6c757d;
    margin-bottom: 0;
}
.stat-row {
    display: flex;
    gap: 16px;
    justify-content: center;
    margin: 20px 0 28px;
    flex-wrap: wrap;
}
.stat-box {
    background: #f4f6fb;
    border-radius: 10px;
    padding: 16px 28px;
    text-align: center;
    min-width: 130px;
}
body.dark-mode .stat-box { background: #252a3b; }
.stat-box .stat-num {
    font-size: 2rem;
    font-weight: 800;
    color: #3b82f6;
    line-height: 1;
}
.stat-box .stat-label {
    font-size: 0.78rem;
    color: #6c757d;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.options-row {
    display: flex;
    align-items: center;
    gap: 24px;
    justify-content: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.markup-group {
    display: flex;
    align-items: center;
    gap: 10px;
}
.markup-group label {
    font-weight: 600;
    margin: 0;
    white-space: nowrap;
}
.markup-group input[type=number] {
    width: 90px;
    border-radius: 8px;
    border: 1px solid #ced4da;
    padding: 6px 10px;
    font-size: 1rem;
    text-align: center;
}
.clear-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.92rem;
    cursor: pointer;
}
.clear-toggle input { cursor: pointer; width: 16px; height: 16px; }
.btn-import {
    display: block;
    width: 100%;
    max-width: 380px;
    margin: 0 auto;
    padding: 14px 0;
    font-size: 1.15rem;
    font-weight: 700;
    border-radius: 10px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    border: none;
    cursor: pointer;
    transition: opacity .2s, transform .1s;
    letter-spacing: 0.3px;
}
.btn-import:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }
.btn-import:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.progress-wrap {
    display: none;
    margin-top: 24px;
}
.progress-bar-outer {
    background: #e3e8ef;
    border-radius: 8px;
    height: 10px;
    overflow: hidden;
    margin-bottom: 16px;
}
body.dark-mode .progress-bar-outer { background: #2e3347; }
.progress-bar-inner {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #2563eb);
    border-radius: 8px;
    width: 0%;
    transition: width 0.4s ease;
}
.progress-bar-inner.indeterminate {
    width: 100%;
    animation: indeterminate 1.2s ease-in-out infinite;
}
@keyframes indeterminate {
    0%   { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.log-box {
    background: #0d1117;
    border-radius: 8px;
    padding: 14px 16px;
    font-family: monospace;
    font-size: 0.85rem;
    color: #58d68d;
    min-height: 80px;
    max-height: 220px;
    overflow-y: auto;
    line-height: 1.7;
}
.log-box .log-err { color: #e74c3c; }
.result-banner {
    border-radius: 10px;
    padding: 18px 22px;
    margin-top: 20px;
    display: none;
}
.result-banner.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}
.result-banner.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}
body.dark-mode .result-banner.success { background:#1a3a28; border-color:#2a5c3a; color:#6fcf97; }
body.dark-mode .result-banner.error   { background:#3a1a1a; border-color:#5c2a2a; color:#e74c3c; }
.result-stats {
    display: flex;
    gap: 18px;
    flex-wrap: wrap;
    margin-top: 10px;
}
.result-stat {
    text-align: center;
}
.result-stat strong {
    display: block;
    font-size: 1.5rem;
    font-weight: 800;
}
.provider-badge {
    display: inline-block;
    background: #d4edda;
    color: #155724;
    border-radius: 20px;
    padding: 2px 10px;
    font-size: 0.78rem;
    font-weight: 600;
}
body.dark-mode .provider-badge { background:#1a3a28; color:#6fcf97; }
.divider { border: none; border-top: 1px solid #e3e8ef; margin: 28px 0; }
body.dark-mode .divider { border-color: #2e3347; }
</style>

<div class="col-md-12">
<div class="import-card">

  <div class="import-hero">
    <h2>⚡ One-Click Service Import</h2>
    <p>Import all categories and services from FasterSMM in one click — no steps, no hassle.</p>
  </div>

  <!-- Current stats -->
  <div class="stat-row">
    <div class="stat-box">
      <div class="stat-num" id="stat-services"><?= number_format($totalServices) ?></div>
      <div class="stat-label">Services</div>
    </div>
    <div class="stat-box">
      <div class="stat-num" id="stat-cats"><?= number_format($totalCategories) ?></div>
      <div class="stat-label">Categories</div>
    </div>
    <div class="stat-box">
      <div class="stat-num"><?= count($providers) ?></div>
      <div class="stat-label">Providers</div>
    </div>
  </div>

  <!-- Provider pills -->
  <div style="text-align:center; margin-bottom:20px;">
    <?php foreach ($providers as $p): ?>
      <?php if ($p['status'] == '1'): ?>
        <span class="provider-badge">✓ <?= htmlspecialchars($p['api_name']) ?></span>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <hr class="divider">

  <!-- Controls -->
  <div class="options-row">
    <div class="markup-group">
      <label for="markup-input">Markup %</label>
      <input type="number" id="markup-input" value="30" min="0" max="500">
      <span style="font-size:0.82rem;color:#6c757d;">Applied on top of supplier rate</span>
    </div>
    <label class="clear-toggle" title="Delete all existing services and categories before importing">
      <input type="checkbox" id="clear-first-chk">
      <span>Clear existing first</span>
    </label>
  </div>

  <!-- One-click button -->
  <button class="btn-import" id="importBtn" onclick="startImport()">
    ⬇&nbsp; Import All Services &amp; Categories
  </button>

  <!-- Progress -->
  <div class="progress-wrap" id="progressWrap">
    <div class="progress-bar-outer">
      <div class="progress-bar-inner indeterminate" id="progressBar"></div>
    </div>
    <div class="log-box" id="logBox">Connecting to FasterSMM API…</div>
  </div>

  <!-- Result -->
  <div class="result-banner" id="resultBanner">
    <div id="resultMsg"></div>
    <div class="result-stats" id="resultStats"></div>
  </div>

</div><!-- /.import-card -->
</div>

<script>
function startImport() {
  var btn      = document.getElementById('importBtn');
  var wrap     = document.getElementById('progressWrap');
  var logBox   = document.getElementById('logBox');
  var bar      = document.getElementById('progressBar');
  var banner   = document.getElementById('resultBanner');
  var markup   = parseFloat(document.getElementById('markup-input').value) || 30;
  var clearFirst = document.getElementById('clear-first-chk').checked;

  if (clearFirst && !confirm('This will DELETE all existing services and categories before importing. Continue?')) return;

  btn.disabled = true;
  btn.textContent = '⏳  Importing… please wait';
  banner.style.display = 'none';
  banner.className = 'result-banner';
  wrap.style.display = 'block';
  bar.className = 'progress-bar-inner indeterminate';
  logBox.innerHTML = '<span>⟳ Connecting to FasterSMM API…</span>';

  var fd = new FormData();
  fd.append('action', 'import_all');
  fd.append('markup', markup);
  if (clearFirst) fd.append('clear_first', '1');

  fetch(window.location.href, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      bar.className = 'progress-bar-inner';
      bar.style.width = '100%';

      if (data.ok) {
        var lines = (data.log || []).map(function(l) { return '<span>✓ ' + escHtml(l) + '</span>'; });
        logBox.innerHTML = lines.join('<br>');

        banner.className = 'result-banner success';
        banner.style.display = 'block';
        document.getElementById('resultMsg').innerHTML =
          '<strong>✅ Import complete!</strong> ' + data.inserted + ' services imported with ' + data.markup + '% markup.';

        document.getElementById('resultStats').innerHTML =
          '<div class="result-stat"><strong>' + data.inserted + '</strong>Services</div>' +
          '<div class="result-stat"><strong>' + data.cats + '</strong>Categories</div>' +
          '<div class="result-stat"><strong>' + data.skipped + '</strong>Skipped</div>' +
          '<div class="result-stat"><strong>' + data.markup + '%</strong>Markup</div>';

        document.getElementById('stat-services').textContent = data.inserted.toLocaleString();
        document.getElementById('stat-cats').textContent = data.cats.toLocaleString();

        btn.textContent = '✅ Done — click to import again';
        btn.disabled = false;
      } else {
        logBox.innerHTML = '<span class="log-err">✗ ' + escHtml(data.error || 'Unknown error') + '</span>';
        banner.className = 'result-banner error';
        banner.style.display = 'block';
        document.getElementById('resultMsg').innerHTML =
          '<strong>❌ Import failed:</strong> ' + escHtml(data.error || 'Unknown error');
        document.getElementById('resultStats').innerHTML = '';
        btn.textContent = '⬇ Import All Services & Categories';
        btn.disabled = false;
      }
    })
    .catch(function(err) {
      bar.className = 'progress-bar-inner';
      bar.style.width = '100%';
      logBox.innerHTML = '<span class="log-err">✗ Network error: ' + escHtml(String(err)) + '</span>';
      banner.className = 'result-banner error';
      banner.style.display = 'block';
      document.getElementById('resultMsg').innerHTML = '<strong>❌ Network error.</strong> Check your connection and try again.';
      document.getElementById('resultStats').innerHTML = '';
      btn.textContent = '⬇ Import All Services & Categories';
      btn.disabled = false;
    });
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php require admin_view('footer'); ?>
