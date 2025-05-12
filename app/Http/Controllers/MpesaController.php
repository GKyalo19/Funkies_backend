<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MpesaController extends Controller
{
    public function generateAccessToken(){
        $consumer_key = "GOsNjC8cyxtA6JM8B0zMMMG3cDE8FebsXvjG3nQeAtDc8fE8";
        $consumer_secret = "RdzHHaIpTUqYbEoz5G8NFd1rhEAzGUK9ZxN0OnFPfAK1x9Tn6U9pZn7MhHpkItdp";
        $credentials = base64_encode($consumer_key.":".$consumer_secret);

        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '. $credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $curl_response = curl_exec($curl);

        if (curl_errno($curl)) {
            return response()->json(['error' => curl_error($curl)], 500);
        }

        $access_token = json_decode($curl_response);

        if (!isset($access_token->access_token)) {
            return response()->json(['error' => 'Access token not generated', 'response' => $curl_response], 500);
        }

        return $access_token->access_token;
    }

    public function STKPush(){
        $access_token = $this->generateAccessToken();
        dd("Access Token: ", $access_token);

        $BusinessShortCode = 174379;
        $passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
        $timestamp = Carbon::now()->format('YmdHis');

        $password = base64_encode($BusinessShortCode.$passkey.$timestamp);

        $Amount = 1;
        $PartyA = 254702925121;
        $PartyB = 174379;

        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type:application/json',
            'Authorization: Bearer '.$access_token
        ]);

        $curl_post_data = array(
            'BusinessShortCode'=>$BusinessShortCode,
            'Password'=>$password,
            'Timestamp'=>$timestamp,
            'TransactionType'=>'CustomerPayBillOnline',
            'Amount'=>$Amount,
            'PartyA'=>$PartyA,
            'PartyB'=>$PartyB,
            'PhoneNumber'=>$PartyA,
            'CallBackURL'=>'https://funkies254-backend.onrender.com/callback',
            'AccountReference'=>'Funkies 254',
            'TransactionDesc'=>'Transaction successful!'
        );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

        $curl_response = curl_exec($curl);

        if (curl_errno($curl)) {
            return response()->json(['error' => curl_error($curl)], 500);
        }
        curl_close($curl);

        return $curl_response;

    }
}
