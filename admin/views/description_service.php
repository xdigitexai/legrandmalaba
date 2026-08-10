<?php include 'header.php'; ?>
<style>
body {
    background: #f3f4f6;
    font-family: 'Inter', sans-serif;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Page container */
.container {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center; 
    align-items: center;     
    padding: 20px;
}

/* Card */
.generator-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    overflow: hidden;
    background: #fff;
    margin: 20px auto;
    width: 95%;
    max-width: 1200px;
    flex: 0 0 auto;
}

.generator-header {
    background: linear-gradient(135deg, #3b82f6, #06b6d4);
    color: #fff;
    padding: 22px 30px;
    font-size: 22px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
}

.generator-body {
    padding: 30px;
}

.form-label {
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
}

.form-select {
    width: 100%;
    border-radius: 14px;
    padding: 12px 15px;
    font-size: 15px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    margin-bottom: 20px;
    box-sizing: border-box;
}

#output {
    font-family: 'Courier New', monospace;
    background: #f9fafb;
    border-radius: 14px;
    padding: 18px;
    resize: none;
    border: 1px solid #e5e7eb;
    box-shadow: inset 0 1px 4px rgba(0,0,0,0.05);
    font-size: 14px;
    line-height: 1.6;
    width: 100%;
    box-sizing: border-box;
    min-height: 400px;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.btn-copy {
    background: linear-gradient(135deg, #3b82f6, #06b6d4);
    border: none;
    padding: 12px 30px;
    border-radius: 14px;
    color: #fff;
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 15px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
    cursor: pointer;
    text-align: center;
    width: 100%;
    justify-content: center;
}

.btn-copy:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.12);
}

.highlight-category {
    color: #3b82f6;
    font-weight: 700;
}

.action-buttons {
    margin: 15px 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-action {
    width: 100%;
    padding: 12px;
    font-size: 15px;
    font-weight: 600;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
}

.btn-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

/* Responsive */
@media (max-width: 768px) {
    .generator-body {
        padding: 18px;
    }
    .generator-header {
        font-size: 18px;
        padding: 14px 16px;
    }
    #output {
        min-height: 250px;
        font-size: 13px;
    }
    .btn-copy {
        font-size: 14px;
        padding: 10px 20px;
    }
}
</style>

<div class="container">
    <div class="card generator-card">
        <div class="generator-header">
            📋 Service Descriptions Generator
        </div>
        <div class="generator-body">
            <form method="get" class="mb-4">
                <label for="category_id" class="form-label">Select Category</label>
                <select name="category_id" id="category_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Choose Category --</option>
                    <?php foreach ($data['categories'] as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>" 
                            <?= $data['selected_category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($data['selected_category_id'] && $data['services']): ?>
                <h6 class="fw-bold mb-3">
                    Generated Description for 
                    <span class="highlight-category"><?= htmlspecialchars($data['selected_category_name']) ?></span>
                </h6>
                <textarea id="output" class="form-control" rows="16" readonly>
Exciting New Services Just Launched!

<?= htmlspecialchars($data['selected_category_name']) ?>


<?php foreach ($data['services'] as $s): ?>
[<?= $s['service_id'] ?>] - <?= $s['service_name'] ?> - ₱<?= number_format($s['service_price'], 2) ?> per 1,000

<?php endforeach; ?>
✔️ Smart Speed Scaling, Bigger orders unlock faster delivery.  
✔️ Smaller orders are delivered at a steady pace, while larger ones enjoy significantly faster speeds ideal for going viral quickly.  

Place your order now directly from <?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>!
                </textarea>
                <div class="text-center mt-4">
                    <button type="button" onclick="copyText()" class="btn-copy">📋 Copy Description</button>
                </div>
            <?php elseif ($data['selected_category_id']): ?>
                <div class="alert alert-warning">⚠️ No services found in this category.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function copyText() {
    const output = document.getElementById("output");
    output.select();
    output.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("Description copied to clipboard!");
}
</script>