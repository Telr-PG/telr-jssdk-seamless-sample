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

echo "Transaction is failed: <br/><br/><pre>"; print_r($results); exit;

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