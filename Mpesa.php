<?php
/**
 * Copyright 2022-2024 FOSSBilling
 * Copyright 2011-2021 BoxBilling, Inc.
 * @author    Sive.Host <randd@sive.host>
 * @copyright Copyright (c) 2025 Sive Setfu ICT Solutions (Pty) Ltd. <randd@sive.host>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use FOSSBilling\Environment;

class Payment_Adapter_Mpesa extends Payment_AdapterAbstract implements FOSSBilling\InjectionAwareInterface
{
    protected ?Pimple\Container $di = null;

    public function setDi(Pimple\Container $di): void
    {
        $this->di = $di;
    }

    public function getDi(): ?Pimple\Container
    {
        return $this->di;
    }

    public function __construct(private $config)
    {

        if ($this->config['test_mode']) {
            if (!isset($this->config['test_consumer_key'])) {
                throw new Payment_Exception('The ":pay_gateway" payment gateway is not fully configured. Please configure the :missing', [':pay_gateway' => 'Mpesa', ':missing' => 'Test Consumer Key'], 4001);
            }
            if (!isset($this->config['test_consumer_secret'])) {
                throw new Payment_Exception('The ":pay_gateway" payment gateway is not fully configured. Please configure the :missing', [':pay_gateway' => 'Mpesa', ':missing' => 'Test Consumer Secret'], 4001);
            }

        } else {
            if (!isset($this->config['consumer_key'])) {
                throw new Payment_Exception('The ":pay_gateway" payment gateway is not fully configured. Please configure the :missing', [':pay_gateway' => 'Mpesa', ':missing' => 'Consumer key'], 4001);
            }
            if (!isset($this->config['consumer_secret'])) {
                throw new Payment_Exception('The ":pay_gateway" payment gateway is not fully configured. Please configure the :missing', [':pay_gateway' => 'Mpesa', ':missing' => 'Consumer Secret'], 4001);
            }
            if (!isset($this->config['country_code'])) {
                throw new Payment_Exception('The ":pay_gateway" payment gateway is not fully configured. Please configure the :missing', [':pay_gateway' => 'Mpesa', ':missing' => 'Country code, 2 characters long country code in [ISO 3166-1](https://en.wikipedia.org/wiki/ISO_3166-1)'], 4001);
            }          
            

        }
    }

    public static function getConfig()
    {
            return [
            'supports_one_time_payments' => true,
            'description' => 'You authenticate to the Mpesa API by providing one of your API keys in the request. You can manage your API keys from your account.',
            'logo' => [
                'logo' => 'mpesasafari.png',
                'height' => '30px',
                'width' => '65px',
            ],
            'form' => [
                'consumer_key' => [
                    'text', [
                        'label' => 'Live Consumer key:',
                        'required' => true,                        
                    ],
                ],
                'consumer_secret' => [
                    'text', [
                        'label' => 'Live Consumer Secret:',
                        'required' => true,
                    ],
                ],
                'test_consumer_key' => [
                    'text', [
                        'label' => 'Sandbox Consumer key:',
                        'required' => false,
                    ],
                ],
                'test_consumer_secret' => [
                    'text', [
                        'label' => 'Sandbox Consumer Secret:',
                        'required' => false,
                    ],
                ],
                'country_code' => [
                    'text', [
                        'label' => 'Country Code:',
                        'required' => true,
                    ],
                ],                
            ],
        ];
        
    }

    public function getHtml($api_admin, $invoice_id, $subscription)
    {
        $invoice = $api_admin->invoice_get(['id' => $invoice_id]);

        $data = [];

        if ($subscription) {
            $data = $this->getSubscriptionFields($invoice);
        } else {
            $data = $this->getOneTimePaymentFields($invoice);
        }

        $url = $this->serviceUrl();

        return $this->_generateForm($url, $data);
    }

    public function processTransaction($api_admin, $id, $data, $gateway_id)
    {
    
    file_put_contents("/var/www/USERNAME/public_html/ipnerror.txt",date(DATE_ATOM).print_r($data,true).PHP_EOL, FILE_APPEND | LOCK_EX);
        if (!$this->_isIpnValid($data)) {
            throw new Payment_Exception('IPN is invalid');
        }

        $order_tracking_id = $data['OrderTrackingId'];

$req = $url;
$consumerK = $this->serviceKey();
$consumerS = $this->serviceSecret();

$curl = curl_init();

curl_setopt_array($curl, array(
//  CURLOPT_URL => '$req/api/Auth/RequestToken',
  CURLOPT_URL => $req.'/api/Auth/RequestToken',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
  "consumer_key": "'.$consumerK.'",
  "consumer_secret": "'.$consumerS.'"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Accept: application/json'
  ),
));

$response = curl_exec($curl);

if ($response === false) {
    throw new Payment_Exception('cURL error: ' . curl_error($curl));
}
curl_close($curl);
$tjso = json_decode($response, true);
$access_token  = $tjso['token'];

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $req.'/api/Transactions/GetTransactionStatus?orderTrackingId='.$order_tracking_id,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json',
    'Content-Type: application/json',
    'authorization: Bearer '.$access_token 
  ),
));

$responsed = curl_exec($curl);

if ($responsed === false) {
    throw new Payment_Exception('cURL error: ' . curl_error($curl));
}

curl_close($curl);
$st = json_decode($responsed, true);
$pstatus = $st['payment_status_description'];

$invoice = $api_admin->invoice_get(['id' => $id]);
         if ($invoice['status'] == "paid") {
              throw new Payment_Exception('Invoice $id already marked paid may be duplicate IPN');
                    }
                    

if($pstatus == "COMPLETED"){
	$api_admin->invoice_transaction_update(['id' => $id, 'txn_status' => $pstatus]);
	
        if (isset($order_tracking_id)) {
            $api_admin->invoice_transaction_update(['id' => $id, 'txn_id' => $order_tracking_id]);
        }

        if (isset($st['amount'])) {
            $api_admin->invoice_transaction_update(['id' => $id, 'amount' => $st['amount']]);
        }
        
        if (isset($st['currency'])) {
            $api_admin->invoice_transaction_update(['id' => $id, 'currency' => $st['currency']]);
        }   
        
        if (isset($st['payment_method'])) {
            $api_admin->invoice_transaction_update(['id' => $id, 'note' => $st['payment_method']]);
        }   
        
        if (isset($responsed)) {
            $api_admin->invoice_transaction_update(['id' => $id, 'ipn' => $responsed]);
        }           

        $client_id = $invoice['client']['id'];
	$bd = [
                'id' => $client_id,
                'amount' => $st['amount'],
                'description' => 'Mpesa transaction ' . $order_tracking_id,
                'type' => 'Pesapal',
                'rel_id' => $order_tracking_id,
        	];

                    $api_admin->client_balance_add_funds($bd);
                    if ($invoice['id']) {
                        $api_admin->invoice_pay_with_credits(['id' => $invoice['id']]);
                    } else {
                        $api_admin->invoice_batch_pay_with_credits(['client_id' => $client_id]);
                    }

}else if($pstatus == "REVERSED"){
            $refd = [
                'id' => $invoice['id'],
                'note' => 'Mpesa refund ' . $responsed,
            ];
            $api_admin->invoice_refund($refd);
        }

        $d = [
            'id' => $id,
            'error' => '',
            'error_code' => '',
            'status' => 'processed',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $api_admin->invoice_transaction_update($d);
    }

    private function serviceUrl()
    {
        if ($this->config['test_mode']) {
            return 'https://cybqa.pesapal.com/pesapalv3';
        } else {
            return 'https://pay.pesapal.com/v3';
        }
    }

    private function serviceKey()
    {
        if ($this->config['test_mode']) {
            return $this->config['test_consumer_key'];
        } else {
            return $this->config['consumer_key'];
        }
    }
    
    private function serviceSecret()
    {
        if ($this->config['test_mode']) {
            return $this->config['test_consumer_secret'];
        } else {
            return $this->config['consumer_secret'];
        }
    }    
    
    private function _isIpnValid($data)
    {
    
// Initialize the request data variable
$data = [];

// Check if the request method is POST or GET and retrieve the data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read and parse raw POST data
    $rawPostData = file_get_contents("php://input");
    parse_str($rawPostData, $data);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Directly assign GET data
    $data = $_GET;
}

// Check if data is empty or missing required fields
    if (empty($data) || !isset($data['OrderTrackingId']) || !isset($data['payment_status_description'])) {
        return false;
    }
    return $data;
    }

    public function moneyFormat($amount, $currency = null)
    {
        // HUF currency do not accept decimal values
        if ($currency == 'HUF') {
            return number_format($amount, 0);
        }

        return number_format($amount, 2, '.', '');
    }

    /**
     * @param string $url
     */
    private function download($url, $post_vars = false)
    {
        $post_contents = '';
        if ($post_vars) {
            if (is_array($post_vars)) {
                foreach ($post_vars as $key => $val) {
                    $post_contents .= ($post_contents ? '&' : '') . urlencode($key) . '=' . urlencode($val);
                }
            } else {
                $post_contents = $post_vars;
            }
        }

        $client = $this->getHttpClient()->withOptions([
            'verify_peer' => false,
            'verify_host' => false,
            'timeout' => 600,
        ]);
        $response = $client->request('POST', $url, [
            'body' => $post_contents,
        ]);

        return $response->getContent();
    }

    /**
     * @param string $url
     */
private function _generateForm($url, $data, $method = 'get')
{
$req = $url;
$consumerK = $this->serviceKey();
$consumerS = $this->serviceSecret();
$currentipn = $data['notification_url'];

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $req.'/api/Auth/RequestToken',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
  "consumer_key": "'.$consumerK.'",
  "consumer_secret": "'.$consumerS.'"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Accept: application/json'
  ),
));

$response = curl_exec($curl);

if ($response === false) {
    throw new Payment_Exception('cURL error: ' . curl_error($curl));
}
curl_close($curl);
$tjso = json_decode($response, true);
$access_token  = $tjso['token'];
//echo $access_token.PHP_EOL.' IPNLIST ';



$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $req.'/api/URLSetup/GetIpnList',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json',
    'Content-Type: application/json',
    'authorization: Bearer '.$access_token
  ),
));

$ipnlist = curl_exec($curl);

curl_close($curl);
//echo $ipnlist.PHP_EOL;

$pipnlist = json_decode($ipnlist, true);

$notfound = true;
$ipnid = '';
foreach ($pipnlist as $pipn){

if($currentipn == $pipn['url'] AND $pipn['ipn_status'] == 1){
$notfound = false;
$ipnid = $pipn['ipn_id'];
//print_r($pipn).PHP_EOL;
}

}

if($notfound){


$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $req.'/api/URLSetup/RegisterIPN',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "url": "'.$currentipn.'",
    "ipn_notification_type": "'.$method.'" 
}',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json',
    'Content-Type: application/json',
    'authorization: Bearer '.$access_token
  ),
));

$ripn = curl_exec($curl);

curl_close($curl);
//echo 'NEW IPN: '.PHP_EOL.$ripn.PHP_EOL;
$lipn = json_decode($ripn, true);
$currentipn = $lipn['url'];
if(isset($lipn['ipn_id']) AND $lipn['ipn_status'] == 1){$ipnid = $lipn['ipn_id'];}else{exit('IPN Status is not Active');}

}

$baddr = $data['billing_address'];
$idinv = $data['id'];        
$cren = $data['currency'];
$amnt = $data['amount'];
$descr  = $data['description'];
$calurl = $data['callback_url'];        
$cancurl = $data['cancellation_url'];    

$curl = curl_init();  
curl_setopt_array($curl, array(
  CURLOPT_URL => $req.'/api/Transactions/SubmitOrderRequest',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "id": "'.$idinv.'",
    "currency": "'.$cren.'",
    "amount": "'.$amnt.'",
    "description": "'.$descr.'",
    "callback_url": "'.$calurl.'",
    "cancellation_url": "'.$cancurl.'",
    "notification_id": "'.$ipnid.'",
    "billing_address": '.$baddr.'
}',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json',
    'Content-Type: application/json',
    'authorization: Bearer '.$access_token 
  ),
));

$response = curl_exec($curl);

if ($response === false) {
    throw new Payment_Exception('cURL error: ' . curl_error($curl));
}
curl_close($curl);
$rsp = json_decode($response, true);

if(isset($rsp['redirect_url'])){
$order_tracking_id = $rsp['order_tracking_id'];
//echo PHP_EOL.'Order Tracking ID: '.$order_tracking_id;
$merchant_reference = $rsp['merchant_reference'];
//echo PHP_EOL.'Merchant Reference: '.$merchant_reference;

$redirect_url = $rsp['redirect_url'];

$form = '<iframe src="' . $redirect_url . '" style="width:100%; height:500px; border:none;"></iframe>';

}else if($rsp['error']['error_type'] == "duplicate_order_reference"){
$timestamp = time(); $formattedDate = date('YmdHis', $timestamp); //generating random id
$idinv = $idinv.$formattedDate;
$curl = curl_init();  
curl_setopt_array($curl, array(
  CURLOPT_URL => $req.'/api/Transactions/SubmitOrderRequest',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "id": "'.$idinv.'",
    "currency": "'.$cren.'",
    "amount": "'.$amnt.'",
    "description": "'.$descr.'",
    "callback_url": "'.$calurl.'",
    "cancellation_url": "'.$cancurl.'",
    "notification_id": "'.$ipnid.'",
    "billing_address": '.$baddr.'
}',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json',
    'Content-Type: application/json',
    'authorization: Bearer '.$access_token 
  ),
));

$response = curl_exec($curl);

if ($response === false) {
    throw new Payment_Exception('cURL error: ' . curl_error($curl));
}
curl_close($curl);
$rsp = json_decode($response, true);

if(isset($rsp['redirect_url'])){
$order_tracking_id = $rsp['order_tracking_id'];
//echo PHP_EOL.'Order Tracking ID: '.$order_tracking_id;
$merchant_reference = $rsp['merchant_reference'];
//echo PHP_EOL.'Merchant Reference: '.$merchant_reference;

$redirect_url = $rsp['redirect_url'];

$form = '<iframe src="' . $redirect_url . '" style="width:100%; height:500px; border:none;"></iframe>';

}else{
$err = $rsp['error']['message'];
$ern = $rsp['status'];
$form = '<p> Please contact support for assistance in processing your payment and share with them this error code: '. $ern.': '.$err . '</p>';
file_put_contents("/var/www/USERNAME/public_html/zerror.txt",date(DATE_ATOM).print_r($response,true).PHP_EOL, FILE_APPEND | LOCK_EX);
	}



}else{
$err = $rsp['error']['message'];
$ern = $rsp['status'];
$form = '<p> Please contact support for assistance in processing your payment and share with them this error code: '. $ern.': '.$err . '</p>';
file_put_contents("/var/www/USERNAME/public_html/zerror.txt",date(DATE_ATOM).print_r($response,true).PHP_EOL, FILE_APPEND | LOCK_EX);
	}


    return $form;
}
     

  
        
    public function getInvoiceTitle(array $invoice)
    {
        $p = [
            ':id' => sprintf('%05s', $invoice['nr']),
            ':serie' => $invoice['serie'],
            ':title' => $invoice['lines'][0]['title'],
        ];

        return __trans('Payment for invoice :serie:id [:title]', $p);
    }

    public function getSubscriptionFields(array $invoice): array
    {
        $data = [];
        $countr = $invoice['buyer']['country'];
        
        $email = $invoice['buyer']['email'];        
        $phone = str_replace(" ","",$invoice['buyer']['phone']);        
        $countrycode = (!empty($countr)) ? $countr : $this->config['country_code'];
        $fname = $invoice['buyer']['first_name']; 
        $lname = $invoice['buyer']['last_name'];
        $addr = $invoice['buyer']['address'];    
        $city = $invoice['buyer']['city'];
        $state = $invoice['buyer']['state'];     
        $pcode = $invoice['buyer']['zip'];
        
        $data['id'] = $invoice['nr'];        
        $data['currency'] = $invoice['currency'];
        $data['amount'] = $this->moneyFormat($invoice['total'], $invoice['currency']);
        $data['description'] = $this->getInvoiceTitle($invoice); 
        $data['callback_url'] = $this->config['thankyou_url'];        
        $data['cancellation_url'] = $this->config['cancel_url'];
        $data['notification_url'] = $this->config['notify_url'];
        $data['billing_address'] = json_encode([
    "email_address" => $email,
    "phone_number" => $phone,
    "country_code" => $countrycode,
    "first_name" => $fname,
    "middle_name" => "",
    "last_name" => $lname,
    "line_1" => $addr,
    "line_2" => "",
    "city" => $city,
    "state" => $state,
    "postal_code" => $pcode,
    "zip_code" => $pcode,
]);

        return $data;
    }

    public function getOneTimePaymentFields(array $invoice): array
    {
        $data = [];
        $countr = $invoice['buyer']['country'];
        
        $email = $invoice['buyer']['email'];        
        $phone = str_replace(" ","",$invoice['buyer']['phone']);        
        $countrycode = (!empty($countr)) ? $countr : $this->config['country_code'];
        $fname = $invoice['buyer']['first_name']; 
        $lname = $invoice['buyer']['last_name'];
        $addr = $invoice['buyer']['address'];    
        $city = $invoice['buyer']['city'];
        $state = $invoice['buyer']['state'];     
        $pcode = $invoice['buyer']['zip'];
        
        $data['id'] = $invoice['nr'];        
        $data['currency'] = $invoice['currency'];
        $data['amount'] = $this->moneyFormat($invoice['total'], $invoice['currency']);
        $data['description'] = $this->getInvoiceTitle($invoice); 
        $data['callback_url'] = $this->config['thankyou_url'];        
        $data['cancellation_url'] = $this->config['cancel_url'];
        $data['notification_url'] = $this->config['notify_url'];
        $data['billing_address'] = json_encode([
    "email_address" => $email,
    "phone_number" => $phone,
    "country_code" => $countrycode,
    "first_name" => $fname,
    "middle_name" => "",
    "last_name" => $lname,
    "line_1" => $addr,
    "line_2" => "",
    "city" => $city,
    "state" => $state,
    "postal_code" => $pcode,
    "zip_code" => $pcode,
]);
        
        return $data;
    }
}
