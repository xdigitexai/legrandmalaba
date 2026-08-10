<style>
/* 3D Card Container */
.card {
    background: #fefefe;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12), 0 6px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 12px 40px rgba(0,0,0,0.18), 0 8px 20px rgba(0,0,0,0.12);
}

/* Labels */
.control-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

/* Inputs & Selects 3D style */
.form-control, .form-select {
    border-radius: 12px;
    border: 1px solid #ddd;
    padding: 12px 15px;
    background: #fdfdfd;
    box-shadow: inset 2px 2px 5px rgba(0,0,0,0.03), inset -2px -2px 5px rgba(255,255,255,0.8);
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: inset 2px 2px 5px rgba(0,0,0,0.05), inset -2px -2px 5px rgba(255,255,255,0.85),
                0 0 10px rgba(0,123,255,0.3);
    outline: none;
}

/* Buttons 3D */
.btn-primary {
    background: linear-gradient(145deg, #4e9cff, #187bff);
    border: none;
    border-radius: 12px;
    color: #fff;
    font-weight: 600;
    padding: 14px 0;
    box-shadow: 0 6px 15px rgba(0,123,255,0.4);
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(145deg, #187bff, #4e9cff);
    box-shadow: 0 10px 20px rgba(0,123,255,0.5);
}

/* Alerts / Section Containers */
.alert {
    background: #f6f8fc;
    border-radius: 12px;
    padding: 20px;
    box-shadow: inset 2px 2px 5px rgba(0,0,0,0.03), inset -2px -2px 5px rgba(255,255,255,0.9);
    margin-bottom: 20px;
}

/* Image Preview 3D style */
.setting-block__image {
    position: relative;
    display: inline-block;
    margin-left: 10px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 2px 2px 12px rgba(0,0,0,0.1), -2px -2px 12px rgba(255,255,255,0.6);
}

.setting-block__image img {
    border-radius: 12px;
}

/* Tooltip 3D */
.tooltip5 {
    position: relative;
    display: inline-block;
    cursor: pointer;
}

.tooltip5 .tooltiptext5 {
    visibility: hidden;
    width: 200px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 8px;
    padding: 6px 10px;
    position: absolute;
    z-index: 1;
    bottom: 130%;
    left: 50%;
    transform: translateX(-50%);
    opacity: 0;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.tooltip5:hover .tooltiptext5 {
    visibility: visible;
    opacity: 1;
}

/* Divider */
hr {
    border: 0;
    height: 1px;
    background: #e0e0e0;
    margin: 30px 0;
}

/* Textarea 3D */
textarea.form-control {
    border-radius: 12px;
    border: 1px solid #ddd;
    padding: 12px;
    background: #fdfdfd;
    box-shadow: inset 2px 2px 6px rgba(0,0,0,0.03), inset -2px -2px 6px rgba(255,255,255,0.8);
    transition: all 0.3s ease;
}

textarea.form-control:focus {
    border-color: #007bff;
    box-shadow: inset 2px 2px 6px rgba(0,0,0,0.05), inset -2px -2px 6px rgba(255,255,255,0.85),
                0 0 10px rgba(0,123,255,0.3);
    outline: none;
}
</style>
<div class="col-md-8">
  <div class="panel panel-default">
    <div class="panel-body">
      <form action="" method="post">
        <div class="form-group">
          <label for="cron_job">Autolike :</label>
          <input type="text" class="form-control" id="cron_job" name="cron_job" value="curl -s https://Your_Domin/cronjobs/autolike.php > /dev/null 2>&1" readonly required>
        </div>
        <div class="form-group">
          <label for="cron_job">Orders :</label>
          <input type="text" class="form-control" id="cron_job" name="cron_job" value="curl -s https://Your_Domin/cronjobs/orders.php > /dev/null 2>&1" readonly required>
        </div>
        <div class="form-group">
          <label for="cron_job">Auto-Reply :</label>
          <input type="text" class="form-control" id="cron_job" name="cron_job" value="curl -s https://Your_Domin/cronjobs/autoreply.php > /dev/null 2>&1" readonly required>
        </div>
        <div class="form-group">
          <label for="cron_job">Drip-Feed :</label>
          <input type="text" class="form-control" id="cron_job" name="cron_job" value="curl -s https://Your_Domin/cronjobs/dripfeed.php > /dev/null 2>&1" readonly required>
        </div>
        <div class="form-group">
          <label for="cron_job">Payments :</label>
          <input type="text" class="form-control" id="cron_job" name="cron_job" value="curl -s https://Your_Domin/cronjobs/payments.php > /dev/null 2>&1" readonly required>
        </div>
        <div class="form-group">
          <label for="cron_job">Refill :</label>
          <input type="text" class="form-control" id="cron_job" name="cron_job" value="curl -s https://Your_Domin/cronjobs/refill.php > /dev/null 2>&1" readonly required>
        </div>
        <div class="form-group">
          <label for="cron_job">Seller Sync :</label>
          <input type="text" class="form-control" id="cron_job" name="cron_job" value="curl -s https://Your_Domin/cronjobs/seller-sync.php > /dev/null 2>&1" readonly required>
        </div>
        <div class="form-group">
          <label for="cron_job">Average Time :</label>
          <input type="text" class="form-control" id="cron_job" name="cron_job" value="curl -s https://Your_Domin/cronjobs/average.php > /dev/null 2>&1" readonly required>
        </div>
        <script>
  // Get the current domain name
  const currentDomain = window.location.hostname;

  // Get all input fields with the name 'cron_job'
  const inputs = document.querySelectorAll('input[name="cron_job"]');

  // Loop through all the inputs and update the value with the current domain
  inputs.forEach(input => {
    input.value = input.value.replace(/https:\/\/(yourdomain\.com|Your_Domin)/, `https://${currentDomain}`);
  });
</script>
      </form>
    </div>
  </div>
</div>
