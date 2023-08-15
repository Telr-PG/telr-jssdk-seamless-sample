<?php

session_start();

   $returnAuth = get_current_file_url("https://") . "/auth.php";
   $returnCan = get_current_file_url("https://") . "/cancel.php";
   $returnDecl = get_current_file_url("https://") . "/decline.php";

   $data = array(
            'ivp_method'      => "create",
            'ivp_source'      => 'Transparent JS SDK Test Page',
            'ivp_store'       => $_POST['store_id'],
            'ivp_authkey'     => $_POST['auth_key'],
            'ivp_cart'        => rand(100,999) . rand(100,999) . rand(100,999),
            'ivp_test'        => 1,
            'ivp_framed'      => 2,
            'ivp_amount'      => $_POST['amount'],
            'ivp_lang'        => 'en',
            'ivp_currency'    => $_POST['currency'],
            'ivp_desc'        => "Transaction from Transparent SDK test Link",
            'return_auth'     => $returnAuth,
            'return_can'      => $returnCan,
            'return_decl'     => $returnDecl,
            'bill_fname'      => $_POST['bill_fname'],
            'bill_sname'      => $_POST['bill_sname'],
            'bill_addr1'      => $_POST['bill_addr1'],
            'bill_addr2'      => $_POST['bill_addr2'],
            'bill_city'       => $_POST['bill_city'],
            'bill_region'     => $_POST['bill_region'],
            'bill_zip'        => $_POST['bill_zip'],
            'bill_country'    => $_POST['bill_country'],
            'bill_email'      => $_POST['bill_email'],
            'bill_tel'        => $_POST['bill_tel'],
            'ivp_paymethod'        => 'card',
            'card_token'      => isset($_POST['telr_token']) ? $_POST['telr_token'] : ""
        );

        $data['repeat_amount'] = $_POST['repeat_amount'];
        $data['repeat_period'] = $_POST['repeat_period'];
        $data['repeat_interval'] = $_POST['repeat_interval'];
        $data['repeat_start'] = 'next';
        $data['repeat_term'] = $_POST['repeat_term'];
        $data['repeat_final'] = $_POST['repeat_final'];    

   $results = api_request($data);

   if (isset($results['order']['ref']) && isset($results['order']['url'])) {
        $ref = trim($results['order']['ref']);
        $url = trim($results['order']['url']);

        $_SESSION['telr_ref'] = $ref;
        $_SESSION['telr_store_id'] = $_POST['store_id'];
        $_SESSION['telr_auth_key'] = $_POST['auth_key'];

        $response = ['redirect_link' => $url];
        echo json_encode($response); exit;
    }else{
      echo "Error Occured in processing transaction"; 
      echo "<pre>"; print_r($results); exit;
    }



function api_request($data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://secure.telr.com/gateway/order.json');
    curl_setopt($ch, CURLOPT_POST, count($data));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
    $results = curl_exec($ch);
    curl_close($ch);
    $results = json_decode($results, true);
    return $results;
}

function get_current_file_url($Protocol='http://') {
   return $Protocol.$_SERVER['HTTP_HOST'].str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(__DIR__)); 
}

?>