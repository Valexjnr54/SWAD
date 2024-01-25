<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyPayment;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function initializePayment(Request $request)
    {
        $this->validate($request,[
            'agent_id' => 'required',
            'property_id' => 'required',
            'buyer_name' => 'required',
            'buyer_phone' => 'required',
            'buyer_email' => 'required|email',
            'currency' => 'required',
            'reference' => 'required',
            'payment_method' => 'required',
            'property_status' =>"required",
            'amount' => 'required|numeric'
        ]);

        $payment = new PropertyPayment();
        $payment->agent_id = $request->input('agent_id');
        $payment->property_id = $request->input('property_id');
        $payment->buyer_name = $request->input('buyer_name');
        $payment->buyer_phone = $request->input('buyer_phone');
        $payment->buyer_email = $request->input('buyer_email');
        $payment->currency = $request->input('currency');
        $payment->reference = $request->input('reference');
        $payment->payment_method = $request->input('payment_method');
        $payment->property_status = $request->input('property_status');
        $payment->amount = $request->input('amount');
        $payment->save();

        $curl = curl_init();
        $email = $request->input('buyer_email');
        $tot = $request->input('amount');
        $total = $tot * 100;
        $amount = $total;
        $reference = $request->input('reference');
        $fullname = $request->input('buyer_name');
        $callback_url = 'https://127.0.0.1:8000/api/v1/checkout/paystack-callback';

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
            'amount'=>$amount,
            'email'=>$email,
            'callback_url' => $callback_url,
            'reference' => $reference,
            'name' => $fullname,
            'phoneNumber'=>$request->phoneNumber,
            ]),
            CURLOPT_HTTPHEADER => [
                // "authorization: Bearer sk_live_fb184e420d3304967b4ff2522e12c1bc775ddba1",
            "authorization: Bearer sk_test_4e8fd1e07801aa989c6599d9dbcf911fe06ba691", //replace this with your own test key
            "content-type: application/json",
            "cache-control: no-cache"
            ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if($err){
            // there was an error contacting the Paystack API
            die('Curl returned error: ' . $err);
        }

        $tranx = json_decode($response, true);

        if(!$tranx['status']){
            // there was an error from the API
            print_r('API returned error: ' . $tranx['message']);
        }

        return response()->json(['response' => 'success', 'link' => $tranx['data']['authorization_url']]);
    }

    public function verifyPayment()
    {
        $curl = curl_init();
        $reference = isset($_GET['reference']) ? $_GET['reference'] : '';
        if(!$reference){
            return response()->json(['message'=>'No reference supplied']);
        }else{
            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_HTTPHEADER => [
                "accept: application/json",
                // "authorization: Bearer sk_live_fb184e420d3304967b4ff2522e12c1bc775ddba1",
                "authorization: Bearer sk_test_4e8fd1e07801aa989c6599d9dbcf911fe06ba691",
                "cache-control: no-cache"
              ],
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            if($err){
                // there was an error contacting the Paystack API
              die('Curl returned error: ' . $err);
            }

            $callback = json_decode($response);

            if(!$callback->status){
              // there was an error from the API
              die('API returned error: ' . $callback->message);
            }
            $status = $callback->data->status;
            
            $detail = PropertyPayment::where(['reference' => $reference])->first();
            $DBreference = $detail->reference;

            if ($DBreference == $reference && $status == true) {
                $propertyPaymentStatus = PropertyPayment::where(['reference' => $reference])->update(['payment_status'=>true]);
                $propertyPayment = PropertyPayment::where(['reference' => $reference])->first();
                $propertyId = $propertyPayment->property_id;
                $property = Property::where(['id' => $propertyId])->update(['is_taken'=>true]);
                return response()->json(['message'=>'Payment Have been Confirmed','url'=>'https://127.0.0.1:8000/payment-summary?trxref='.$callback->data->reference.'&reference='.$callback->data->reference],200);
            } 
        }
    }

    public function summaryPayment()
    {
        $reference = isset($_GET['reference']) ? $_GET['reference'] : '';
        if(!$reference){
            return response()->json(['message'=>'No reference supplied']);
        }else{
            $propertyPayment = PropertyPayment::where(['reference'=>$reference])->first();
            if(!$propertyPayment){
                return response()->json(['message' => 'No order was found using the reference', 'reference' => $reference]);
            }else{
                $property_agent_id = $propertyPayment->agent_id;
                $property_id = $propertyPayment->property_id;

                $agent = User::where(['id' => $property_agent_id])->first();
                $property = Property::where(['id' => $property_id])->first();

                return response()->json([
                    'buyer_detail' => $propertyPayment,
                    'agent' => $agent,
                    'property' => $property
                ],200);
            }
        }
    }
}
