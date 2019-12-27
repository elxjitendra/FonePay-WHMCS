<?php
/**
 * FonePay WHMCS Payment Gateway Module
 *
 * FonePay Payment Gateway modules allow you to integrate payment solutions with the
 * WHMCS platform.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "fonepaygeteway" and therefore all functions
 * begin "fonepaygeteway_".
 *
 * If your module or third party API does not support a given function, you
 * should not define that function within your module. Only the _config
 * function is required.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function fonepaygeteway_MetaData()
{
    return array(
        'DisplayName' => 'Fonepay System',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @return array
 */
function fonepaygeteway_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'FonePay Merchant Gateway Module',
        ),
        // a text field type allows for single line text input
        'PID' => array(
            'FriendlyName' => 'Merchant Code',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your Merchant Code here',
        ),
        // a text field type allows for single line text input
        'sharedSecretKey' => array(
            'FriendlyName' => 'SecretKey',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your SecretKey here',
        ),
        
        // the yesno field type displays a single checkbox option
        'testMode' => array(
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode',
        ),
        
    );
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/third-party-gateway/
 *
 * @return string
 */
function fonepaygeteway_link($params){
    // Gateway Configuration Parameters
    $accountId = $params['PID'];
    $sharedSecretKey = $params['sharedSecretKey'];
    $testMode = $params['testMode'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    // $firstname = $params['clientdetails']['firstname'];
    // $lastname = $params['clientdetails']['lastname'];
    // $email = $params['clientdetails']['email'];
    // $address1 = $params['clientdetails']['address1'];
    // $address2 = $params['clientdetails']['address2'];
    // $city = $params['clientdetails']['city'];
    // $state = $params['clientdetails']['state'];
    // $postcode = $params['clientdetails']['postcode'];
    // $country = $params['clientdetails']['country'];
    // $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName    = $params['companyname'];
    $systemUrl      = $params['systemurl'];
    $returnUrl      = $params['returnurl'];
    $langPayNow     = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName     = $params['paymentmethod'];
    $whmcsVersion   = $params['whmcsVersion'];

    $url            = 'https://clientapi.fonepay.com/api/merchantRequest';
    if( $testMode ) $url = 'https://dev-clientapi.fonepay.com/api/merchantRequest';
    
    $prn                =  uniqid();
    $postfields         = array();
    $postfields['PID']  = $accountId;
    $postfields['MD']   ='P';
    $postfields['PRN']  = $prn;
    $postfields['R1']   = $description;
    $postfields['R2']   = 'payment';
    $postfields['AMT']  = $amount;
    $postfields['CRN']  = $currencyCode;
    $postfields['RU']   = $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php?PRN='.$prn.'&inv='. $invoiceId. "&vurl=". $returnUrl;
    
    
    $date               = date('m/d/Y');
    $dv                 = hash_hmac('sha512', $accountId.','.$postfields['MD'].','.$prn.','.$postfields['AMT'].','.$postfields['CRN'].','.$date.','.$postfields['R1'].','.$postfields['R2'].','.$postfields['RU'], $sharedSecretKey);
    
    $postfields['DT']   = $date;
    $postfields['DV']   = $dv;
    

    $htmlOutput = '<form method="post" action="' . $url . '">';
    foreach ($postfields as $k => $v) {
        $htmlOutput .= '<input type="hidden" name="' . $k . '" value="' . ($v) . '" />';
    }
    $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
    $htmlOutput .= '</form>';

    return $htmlOutput;
}

/**
 * Refund transaction.
 *
 * Called when a refund is requested for a previously successful transaction.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/refunds/
 *
 * @return array Transaction response status
 */
function fonepaygeteway_refund($params){
    // Gateway Configuration Parameters
    $accountId = $params['PID'];
    $secretKey = $params['sharedSecretKey'];
    $testMode = $params['testMode'];
    
    // Transaction Parameters
    $transactionIdToRefund = $params['transid'];
    $refundAmount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // perform API call to initiate refund and interpret result

    return array(
        // 'success' if successful, otherwise 'declined', 'error' for failure
        'status' => 'success',
        // Data to be recorded in the gateway log - can be a string or array
        'rawdata' => $responseData,
        // Unique Transaction ID for the refund transaction
        'transid' => $refundTransactionId,
        // Optional fee amount for the fee value refunded
        'fees' => $feeAmount,
    );
}