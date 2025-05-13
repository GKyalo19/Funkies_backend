<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    public function STKPush(Request $request)
    {
        date_default_timezone_set('Africa/Nairobi');

        $request->validate([
            'phone' => 'required|string',
            'event_id' => 'required|exists:events,id',
            'transaction_desc' => 'required|string'
        ]);

        $user = Auth::user();
        $eventId = $request->input('event_id');

        $alreadyPaid = DB::table('event_user_payments')
            ->where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->where('status', 'YES')
            ->exists();

        if ($alreadyPaid) {
            return response()->json([
                'message' => 'You have already paid for this event',
                'status' => 'already_paid'
            ], 200);
        }

        # access token
        $consumerKey = 'K4RpCHURtlACH5yneIRcEp0KA1erkyNrnnVC7xLzCIqkNAMq'; //Fill with your app Consumer Key
        $consumerSecret = 'J26kK0Iu45sf1F92UC2ghwkhZqfFCjsK9AbIsXRruLiCEftWyLHSZiyigeMUPYpi'; // Fill with your app Secret

        // Dynamic values
        $PartyA = $request->input('phone'); // This is your phone number,
        $AccountReference = 'user' . $user->id . '_event' . $eventId;
        $TransactionDesc = $request->input('transaction_desc');

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
        $CallBackURL = 'https://funkies254-backend.onrender.com/api/mpesa/callback';

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

        if (curl_errno($curl)) {
            Log::error('Curl error: ' . curl_error($curl));
            return response()->json([
                'message' => 'Failed to initiate payment',
                'error' => curl_error($curl)
            ], 500);
        }

        $response_data = json_decode($curl_response, true);
        curl_close($curl);

        // Create a pending payment record (status = NO)
        DB::table('event_user_payments')->updateOrInsert(
            ['user_id' => $user->id, 'event_id' => $eventId],
            ['status' => 'NO', 'updated_at' => now(), 'created_at' => now()]
        );

        // Return a response with the CheckoutRequestID for reference
        return response()->json([
            'message' => 'STK push sent successfully',
            'checkout_request_id' => $response_data['CheckoutRequestID'] ?? null,
            'response' => $response_data
        ]);
    }

    public function mpesaCallback(Request $request)
    {
        $data = $request->getContent();
        Log::info('M-Pesa Callback received: ' . $data);

        $response = json_decode($data, true);

        $stkCallback = $response['Body']['stkCallback'] ?? null;
        if (!$stkCallback) {
            Log::error('Invalid M-Pesa callback payload');
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $resultCode = $stkCallback['ResultCode'] ?? null;
        $merchantRequestID = $stkCallback['MerchantRequestID'] ?? null;
        $checkoutRequestID = $stkCallback['CheckoutRequestID'] ?? null;

        if ($resultCode === 0) {
            $callbackItems = collect($stkCallback['CallbackMetadata']['Item']);
            $mpesaReceipt = $callbackItems->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;
            $accountReference = $callbackItems->firstWhere('Name', 'AccountReference')['Value'] ?? null;

            // Ensure idempotency: Check if this receipt has already been processed
            $existing = DB::table('mpesa_transactions')
                ->where('mpesa_receipt', $mpesaReceipt)
                ->first();

            if ($existing) {
                Log::info("Duplicate callback received for receipt: $mpesaReceipt");
                return response()->json(['message' => 'Duplicate transaction'], 200);
            }

            // Parse user_id and event_id from AccountReference: e.g., user123_event456
            if (preg_match('/user(\d+)_event(\d+)/', $accountReference, $matches)) {
                $userId = $matches[1];
                $eventId = $matches[2];

                // Record the transaction
                DB::table('event_user_payments')->updateOrInsert(
                    ['user_id' => $userId, 'event_id' => $eventId],
                    ['status' => 'YES', 'updated_at' => now()]
                );

                // Save full transaction for audit/logging
                DB::table('mpesa_transactions')->insert([
                    'user_id' => $userId,
                    'event_id' => $eventId,
                    'merchant_request_id' => $merchantRequestID,
                    'checkout_request_id' => $checkoutRequestID,
                    'mpesa_receipt' => $mpesaReceipt,
                    'amount' => $callbackItems->firstWhere('Name', 'Amount')['Value'] ?? null,
                    'phone' => $callbackItems->firstWhere('Name', 'PhoneNumber')['Value'] ?? null,
                    'transaction_time' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                Log::info("Payment updated successfully for user $userId and event $eventId");

                return response()->json(['message' => 'Payment processed successfully']);
            } else {
                Log::error('Could not parse account reference: ' . $accountReference);
                return response()->json(['message' => 'Failed to parse account reference'], 400);
            }
        } else {
            Log::error('M-Pesa transaction failed with code ' . $resultCode);
            return response()->json(['message' => 'Transaction failed'], 400);
        }
    }


    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id'
        ]);

        $user = Auth::user();
        $eventId = $request->input('event_id');

        $paymentStatus = DB::table('event_user_payments')
            ->where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->value('status');

        return response()->json([
            'status' => $paymentStatus === 'YES',
            'event_id' => $eventId
        ]);
    }
}
