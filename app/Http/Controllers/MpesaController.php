<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MpesaController extends Controller
{
    public function STKPush(Request $request)
    {
       date_default_timezone_set('Africa/Nairobi');

       $request -> validate([
        'phone'=>'required|string',
        'account_reference' => 'required|string',
        'transaction_desc' => 'required|string'
       ]);

            # access token
            $consumerKey = 'K4RpCHURtlACH5yneIRcEp0KA1erkyNrnnVC7xLzCIqkNAMq'; //Fill with your app Consumer Key
            $consumerSecret = 'J26kK0Iu45sf1F92UC2ghwkhZqfFCjsK9AbIsXRruLiCEftWyLHSZiyigeMUPYpi'; // Fill with your app Secret

            // Dynamic values
            $PartyA = $request -> input('phone'); // This is your phone number,
            $AccountReference = $request -> input('account_reference');
            $TransactionDesc = $request -> input('transaction_desc');

            //Static values
            $BusinessShortCode = '174379';
            $Passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
            $Amount = '1';

            # Get the timestamp, format YYYYmmddhms -> 20181004151020
            $Timestamp = date('YmdHis');

            # Get the base64 encoded string -> $password. The passkey is the M-PESA Public Key
            $Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp);

            # header for access token
            $headers = ['Content-Type:application/json; charset=utf8'];

            # M-PESA endpoint urls
            $access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
            $initiate_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

            # callback url
            $CallBackURL = 'https://funkies254-backend.onrender.com/';

            $curl = curl_init($access_token_url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_HEADER, FALSE);
            curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
            $result = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $result = json_decode($result);
            $access_token = $result->access_token;
            curl_close($curl);

            # header for stk push
            $stkheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token];

            # initiating the transaction
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $initiate_url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader); //setting custom header

            $curl_post_data = array(
                //Fill in the request parameters with valid values
                'BusinessShortCode' => $BusinessShortCode,
                'Password' => $Password,
                'Timestamp' => $Timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => $Amount,
                'PartyA' => $PartyA,
                'PartyB' => $BusinessShortCode,
                'PhoneNumber' => $PartyA,
                'CallBackURL' => $CallBackURL,
                'AccountReference' => $AccountReference,
                'TransactionDesc' => $TransactionDesc
            );

            $data_string = json_encode($curl_post_data);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            $curl_response = curl_exec($curl);

            if(curl_errno($curl)) {
                echo 'Curl error: ' . curl_error($curl);
            } else {
                echo $curl_response;
            }

            print_r($curl_response);

            echo $curl_response;
        }
}
