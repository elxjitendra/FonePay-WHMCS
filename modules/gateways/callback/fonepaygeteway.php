<?php
/**
 * FonePay WHMCS Payment Callback File
 *
 * It demonstrates verifying that the payment gateway module is active,
 * validating an Invoice ID, checking for the existence of a Transaction ID,
 * Logging the Transaction for debugging and Adding Payment to an Invoice.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/callbacks/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}


$PID = $gatewayParams['PID'];
$sharedSecretKey = $gatewayParams['sharedSecretKey'];
$requestData = [
    'PRN' => $_GET['PRN'],
    'PID' => $PID,
    'BID' => $_GET['BID'],
    'AMT' => $_GET['AMT'], // original payment amount
    'UID' => $_GET['UID'],
    'DV' => hash_hmac('sha512', $PID.','.$_GET['AMT'].','.$_GET['PRN'].','.$_GET['BID'].','.$_GET['UID'], $sharedSecretKey),
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $_GET['RU'].'?'.http_build_query($requestData));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$responseXML = curl_exec($ch);
$message = null;
if($response = simplexml_load_string($responseXML)){
	if($response->success == 'true'){
	    $success = true;
	    $message = $response->message;
	   // echo "Payment Verifcation Completed: ".$response->message;
	}else{
	    $success = false;
	    $message = $response->message;
	   // echo "Payment Verifcation Failed: ".$response->message;
	}
}
   


// Retrieve data returned in payment gateway callback
// Varies per payment gateway
$invoiceId = $_GET['inv'];
$transactionId = $_GET['BID'];
$paymentAmount = $_GET['AMT'];
$currencyCode = $_GET['currency'];
$paymentFee = 0;


$transactionStatus = $success ? 'Success' : 'Failure';

if( $currencyCode == 'USD'){
    $usdAmt = $_GET['usdamt'];
    if( isset($usdAmt))
        $paymentAmount = $usdAmt;
}
/**
 * Validate Callback Invoice ID.
 *
 * Checks invoice ID is a valid invoice number. Note it will count an
 * invoice in any status as valid.
 *
 * Performs a die upon encountering an invalid Invoice ID.
 *
 * Returns a normalised invoice ID.
 *
 * @param int $invoiceId Invoice ID
 * @param string $gatewayName Gateway Name
 */
// $invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);
$invoiceURL = $_GET['vurl'];

/**
 * Log Transaction.
 *
 * Add an entry to the Gateway Log for debugging purposes.
 *
 * The debug data can be a string or an array. In the case of an
 * array it will be
 *
 * @param string $gatewayName        Display label
 * @param string|array $debugData    Data to log
 * @param string $transactionStatus  Status
 */
// logTransaction($gatewayParams['name'], $_POST, $success);

if ($success) {

    /**
     * Add Invoice Payment.
     *
     * Applies a payment transaction entry to the given invoice ID.
     *
     * @param int $invoiceId         Invoice ID
     * @param string $transactionId  Transaction ID
     * @param float $paymentAmount   Amount paid (defaults to full balance)
     * @param float $paymentFee      Payment fee (optional)
     * @param string $gatewayModule  Gateway module name
     */
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        $paymentFee,
        $gatewayModuleName
    );
}

if( isset($invoiceURL)){
    $invoiceURL = $invoiceURL ."=". $invoiceId. "&msg=". $message;
    echo '<script>';
        echo "location.href='". $invoiceURL. "'";
    echo "</script>";
    exit;
}