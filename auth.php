<?php
session_start();

$telr_order_ref = $_SESSION['telr_ref'];
$telr_store_id = $_SESSION['telr_store_id'];
$telr_auth_key = $_SESSION['telr_auth_key'];

$params = array(
	'ivp_method'   => 'check',
	'ivp_store'    => $telr_store_id,
	'ivp_authkey'  => $telr_auth_key,
	'order_ref'    => $telr_order_ref
);

$results = requestGateway($params);

echo "Transaction is successful: <br/><br/>"; 

$objOrder='';
$objError='';
if (isset($results['order'])) { $objOrder = $results['order']; }
if (isset($results['error'])) { $objError = $results['error']; }
if (is_array($objError)) { // Failed
	echo "Transaction is failed"; exit;
}
if (!isset(
	$objOrder['cartid'],
	$objOrder['status']['code'],
	$objOrder['transaction']['status'],
	$objOrder['transaction']['ref'])) {
// Missing fields
	echo "Invalid Transaction Response"; exit;
}

$new_tx = $objOrder['transaction']['ref'];
$ordStatus = $objOrder['status']['code'];
$txStatus = $objOrder['transaction']['status'];
$txMessage = $objOrder['transaction']['message'];
$cart_id = $objOrder['cartid'];

if (($ordStatus==-1) || ($ordStatus==-2) || ($ordStatus==-3) || ($ordStatus==-4)) {
// Order status EXPIRED (-1) or CANCELLED (-2)
	echo "Transaction is cancelled"; exit;
}
if ($ordStatus==4) {
// Order status PAYMENT_REQUESTED (4)
	echo "Transaction is pending"; exit;
}
if ($ordStatus==1) {
	$validateResponse['message'] = 'Payment Pending';
	return $validateResponse;
}
if ($ordStatus==2) {
// Order status AUTH (2)
	echo "Transaction is Authorised: " . $new_tx; exit;
}
if ($ordStatus==3) {
// Order status PAID (3)
	if ($txStatus=='P') {
// Transaction status of pending or held
		echo "Transaction is pending: " . $new_tx; exit;
	}
	if ($txStatus=='H') {
// Transaction status of pending or held
		echo "Transaction is on hold: " . $new_tx; exit;
	}
	if ($txStatus=='A') {
// Transaction status = authorised
		echo "Transaction is Authorised: " . $new_tx; exit;
	}
}

function requestGateway($params) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://secure.telr.com/gateway/order.json');
	curl_setopt($ch, CURLOPT_POST, count($params));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
	$returnData = json_decode(curl_exec($ch),true);
	curl_close($ch);
	return $returnData;
}


?>