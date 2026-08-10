<?php 
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}
 
$amountField = '<div class="form-group">
<label class="control-label">Amount</label>
<input type="number" id="paymentAmount" class="form-control" name="payment_amount" step="0.01" required />
</div>';
$feeField = '<div id="fee_fields"></div>';
$paymentBtn = '<button type="submit" class="btn btn-block btn-primary">[text]</button>';

if($selectedMethod == 1){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 2){
    $formData .= '<div class="form-group">
    <label class="control-label">Order ID</label>
    <input type="text" class="form-control" name="payTMOrderId"  required />
    </div>';
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Verify Transaction");
}

if($selectedMethod == 3){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 4){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 5){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 6){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 7){
    $formData .= '<div class="form-group">
    <label class="control-label">Transaction ID</label>
    <input type="text" class="form-control" name="PhonePeTransactionId"  required />
    </div>';
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Verify Transaction");
}

if($selectedMethod == 8){
    $formData .= '<div class="form-group">
    <label class="control-label">Transaction ID</label>
    <input type="text" class="form-control" name="EasypaisaTransactionId"  required />
    </div>';
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Verify Transaction");
}

if($selectedMethod == 9){
    $formData .= '<div class="form-group">
    <label class="control-label">Transaction ID</label>
    <input type="text" class="form-control" name="JazzcashTransactionId"  required />
    </div>';
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Verify Transaction");
}

if($selectedMethod == 10){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}


if($selectedMethod == 11){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 12){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 13){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 14){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 15){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 16){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 17){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 18){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 33){
    $formData .= '<div class="form-group">
    <label class="control-label">Transaction ID</label>
    <input type="text" class="form-control" name="JazzcashTransactionId"  required />
    </div>';
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Verify Transaction");
}

if($selectedMethod == 34){
    $formData .= '<div class="form-group">
    <label class="control-label">Transaction ID</label>
    <input type="text" class="form-control" name="JazzcashTransactionId"  required />
    </div>';
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Verify Transaction");
}

if($selectedMethod == 35){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 82){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Pay Now ");
}

if($selectedMethod == 110){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}
if($selectedMethod == 111){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}
if($selectedMethod == 112){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}
if($selectedMethod == 113){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}
if($selectedMethod == 114){
    $formData .= $amountField;
    $formData .= $feeField;
    $formData .= replaceText($paymentBtn,"Initiate Payment");
}

if($selectedMethod == 200){
    $formData .= '
<!-- xdigitex: Mobile Money + Card tabs -->
<ul class="nav nav-tabs mb-3" id="xdgTabs" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="xdg-mobile-tab" data-toggle="tab" href="#xdg-mobile" role="tab">
      &#128242; Mobile Money
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="xdg-card-tab" data-toggle="tab" href="#xdg-card" role="tab">
      &#128179; Card / Visa / M-Pesa Online
    </a>
  </li>
</ul>

<div class="tab-content" id="xdgTabContent">

  <!-- ── MOBILE MONEY TAB ─────────────────────────────── -->
  <div class="tab-pane fade show active" id="xdg-mobile" role="tabpanel">

    <input type="hidden" name="xdg_gateway" id="xdg_gateway_input" value="mobile">

    <div class="form-group">
      <label class="control-label">Country / Currency</label>
      <select class="form-control" name="xdg_currency" id="xdg_currency">
        <option value="KES">&#127472;&#127466; Kenya — KES (M-Pesa)</option>
        <option value="UGX">&#127482;&#127468; Uganda — UGX (MTN / Airtel)</option>
        <option value="CDF">&#127464;&#127465; DR Congo — CDF (Vodacom / Airtel)</option>
        <option value="TZS">&#127481;&#127487; Tanzania — TZS (M-Pesa TZ / Tigo)</option>
        <option value="RWF">&#127479;&#127484; Rwanda — RWF (MTN Rwanda)</option>
        <option value="GHS">&#127468;&#127469; Ghana — GHS (MTN / Vodafone / AirtelTigo)</option>
        <option value="NGN">&#127475;&#127468; Nigeria — NGN (Opay / Palmpay / banks)</option>
        <option value="ZMW">&#127487;&#127474; Zambia — ZMW (MTN / Airtel ZM)</option>
        <option value="XAF">&#127464;&#127474; Central Africa — XAF (MTN CM / Orange)</option>
        <option value="XOF">&#127480;&#127475; West Africa — XOF (Orange / MTN SN/CI)</option>
        <option value="SLE">&#127480;&#127473; Sierra Leone — SLE (Orange / Africell)</option>
      </select>
    </div>

    <div class="form-group">
      <label class="control-label">Phone Number <small class="text-muted">(with country code)</small></label>
      <input type="tel" class="form-control" name="xdg_phone" id="xdg_phone"
             placeholder="+254712345678" />
      <small class="text-muted">
        KE +254 &middot; UG +256 &middot; CD +243 &middot; TZ +255 &middot; RW +250 &middot;
        GH +233 &middot; NG +234 &middot; ZM +260 &middot; CM +237 &middot; SN +221
      </small>
    </div>

    <div class="form-group">
      <label class="control-label">Amount <small class="text-muted">(USD, min $1.00)</small></label>
      <input type="number" id="paymentAmount" class="form-control" name="payment_amount"
             step="0.01" min="1" placeholder="1.00" />
    </div>

    <div class="form-group">
      <label class="control-label">You will be charged</label>
      <input type="text" class="form-control" id="xdg_local_equiv" disabled placeholder="—" />
    </div>

  </div><!-- /mobile tab -->

  <!-- ── CARD / VISA TAB ──────────────────────────────── -->
  <div class="tab-pane fade" id="xdg-card" role="tabpanel">

    <div class="alert alert-info py-2">
      <small>Pay with Visa, Mastercard, or M-Pesa Online (Pesapal). You will be redirected to a secure checkout page.</small>
    </div>

    <div class="form-group">
      <label class="control-label">Amount <small class="text-muted">(USD, min $1.00)</small></label>
      <input type="number" class="form-control" name="xdg_card_amount" id="xdg_card_amount"
             step="0.01" min="1" placeholder="1.00" />
    </div>

    <div class="form-group">
      <label class="control-label">Email Address</label>
      <input type="email" class="form-control" name="xdg_email" id="xdg_email"
             placeholder="you@example.com" />
      <small class="text-muted">Used for your payment receipt.</small>
    </div>

  </div><!-- /card tab -->

</div><!-- /tab-content -->

' . $feeField . '
<button type="submit" class="btn btn-block btn-primary" id="xdg_submit_btn">
  &#128242; Send Payment Prompt
</button>

<script>
(function(){
  var rates   = {KES:130,UGX:3800,CDF:2500,TZS:2600,RWF:1350,GHS:15,NGN:1600,ZMW:27,XAF:600,XOF:600,SLE:22};
  var symbols = {KES:"KSh",UGX:"USh",CDF:"FC",TZS:"TSh",RWF:"Frw",GHS:"GH\u20b5",NGN:"\u20a6",ZMW:"K",XAF:"FCFA",XOF:"CFA",SLE:"Le"};

  function updateEquiv(){
    var cur = (document.getElementById("xdg_currency")||{}).value||"KES";
    var amt = parseFloat((document.getElementById("paymentAmount")||{}).value)||0;
    var el  = document.getElementById("xdg_local_equiv");
    if(!el) return;
    if(amt > 0){
      var lc = (amt * rates[cur]).toLocaleString(undefined,{maximumFractionDigits:0});
      el.value = (symbols[cur]||cur) + " " + lc;
    } else {
      el.value = "\u2014";
    }
  }

  var curSel = document.getElementById("xdg_currency");
  var amtIn  = document.getElementById("paymentAmount");
  if(curSel) curSel.addEventListener("change", updateEquiv);
  if(amtIn)  amtIn.addEventListener("input",   updateEquiv);

  // Tab switching: swap gateway value + button label + sync card amount → mobile amount
  var tabs = document.querySelectorAll(\'#xdgTabs .nav-link\');
  var gInput = document.getElementById("xdg_gateway_input");
  var btn    = document.getElementById("xdg_submit_btn");
  var cardAmt = document.getElementById("xdg_card_amount");

  tabs.forEach(function(tab){
    tab.addEventListener("click", function(){
      var target = this.getAttribute("href");
      if(target === "#xdg-card"){
        if(gInput) gInput.value = "card";
        if(btn) btn.innerHTML = "\u{1F4B3} Pay with Card";
        // sync card amount → payment_amount field so addfunds min/max check works
        if(cardAmt && amtIn){ amtIn.value = cardAmt.value; }
      } else {
        if(gInput) gInput.value = "mobile";
        if(btn) btn.innerHTML = "\u{1F4F2} Send Payment Prompt";
      }
    });
  });

  // Keep payment_amount in sync when user types in card amount
  if(cardAmt && amtIn){
    cardAmt.addEventListener("input", function(){ amtIn.value = this.value; });
  }
})();
</script>';
}
?>