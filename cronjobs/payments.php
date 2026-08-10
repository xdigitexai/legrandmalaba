<?php
ini_set('max_execution_time', '600');

define("BASEPATH",TRUE);

require $_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php";
require $_SERVER["DOCUMENT_ROOT"]."/app/init.php";

$currencies_array = get_currencies_array("enabled");

$payment_method = $conn->prepare("SELECT method_extras FROM payment_methods WHERE id=:id");

$payment_method->execute(array("id"=>39));
$payment_method = $payment_method->fetch(PDO::FETCH_ASSOC);
$access_key = json_decode($payment_method["method_extras"],true)["access_key"];

$pending_transactions = $conn->prepare("SELECT * FROM payments WHERE payment_method=:method_id AND payment_status=:status AND payment_delivery=:delivery");
$pending_transactions->execute(array(
  "method_id" => 39,
  "status" => 2,
  "delivery" => 1
));

$pending_transactions = $pending_transactions->fetchAll(PDO::FETCH_ASSOC);


$max_results = 25;
$ENCRYPTION_KEY = 'H,!5VuxK%9JBtXsg^uM7';
$access_key = json_decode(decrypt(base64_decode($access_key),$ENCRYPTION_KEY), true)["data"]["access_key"];

$url = "https://mails.dukesmm.com/emails?domain=".$_SERVER["HTTP_HOST"]."&access_key=".$access_key."&max_results=".$max_results;
$array_of_transactions = HTTP_REQUEST($url,"",array(""),"GET",0);
for($i = 0;$i < count($pending_transactions);$i++){
if(!is_null($array_of_transactions)){

$user = $conn->prepare("SELECT * FROM clients WHERE client_id=:id");

$user->execute(array("id"=> $pending_transactions[$i]["client_id"]));
$user = $user->fetch(PDO::FETCH_ASSOC);

$tx_orderID = $pending_transactions[$i]["payment_extra"];
$payment_id = $pending_transactions[$i]["payment_id"];
$amount = $pending_transactions[$i]["payment_amount"];

$array_of_transactions = json_decode($array_of_transactions, true);
$array_of_transactions = $array_of_transactions[$tx_orderID];

if(!empty($array_of_transactions)){
$transaction_id = $array_of_transactions["transaction_id"];
$txn_amount = $array_of_transactions["amount"];
$txn_status = $array_of_transactions["status"];


if($transaction_id == $tx_orderID && $txn_amount == $amount && $txn_status == "Successful"){

$payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_id=:id ');

$payment->execute(['id' => $payment_id]);

$payment = $payment->fetch(PDO::FETCH_ASSOC);

$payment['payment_amount'] = from_to($currencies_array,"INR",$settings["site_base_currency"],$payment['payment_amount']);

$update = $conn->prepare('UPDATE payments SET client_balance=:balance,payment_amount=:amount, payment_status=:status, payment_delivery=:delivery WHERE payment_id=:id');

$update->execute(['balance' => $user['balance'],'amount' =>$amount, 'status' => 3, 'delivery' => 2, 'id' => $payment_id]);

$balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id ');

$balance->execute(['id' => $payment['client_id'], 'balance' => $user['balance'] + $amount]);

$insert_log = $conn->prepare('INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ');
$insert_log->execute(['c_id' => $payment['client_id'], 'action' => 'New '.get_currency_symbol_by_code("INR")." ".$amount.' payment has been made with '. $payment_method['method_name'], 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s')]);
} else {
// INVALID AMOUNT
$update = $conn->prepare('UPDATE payments SET client_balance=:balance,payment_amount=:amount, payment_status=:status, payment_delivery=:delivery WHERE payment_id=:id');

$update->execute([
  'balance' => $user['balance'],
  'amount' =>$amount,
  'status' => 2,
  'delivery' => 2,
  'id' => $payment_id
]);
}

} else {
 // TRANSACTION ID NOT FOUND
$update = $conn->prepare('UPDATE payments SET client_balance=:balance,payment_amount=:amount, payment_status=:status, payment_delivery=:delivery WHERE payment_id=:id');

$update->execute([
  'balance' => $user['balance'],
  'amount' =>$amount,
  'status' => 2,
  'delivery' => 2,
  'id' => $payment_id
]);

}
} else {
// RESPONSE IS NULL

exit();

}

}

?>