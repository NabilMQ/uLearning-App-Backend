<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Models\Order;
use App\Models\Payment;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

use Stripe\Stripe;
use Stripe\Checkout\Session;


use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;

class PayController extends Controller
{

    // 
    // 
    // Stripe
    // 
    // 

    public function checkoutStripe(Request $request) {
        try {
            $user = $request->user();
            $token = $user->token;
            $course_id = $request->id;

            Stripe::setApiKey(env('STRIPE_KEY'));

            $course_result = Course::where('id', '=', $course_id)->first();

            if (empty($course_result)) {
                return response()->json([
                    'code'=> 400,
                    'msg'=> "Course doesn't exist",
                ], 400);
            }

            $orderMap = [];

            $orderMap['course_id'] = $course_id;
            $orderMap['user_token'] = $token;
            $orderMap['status'] = 1;

            $orderRes = Order::where($orderMap)->first(); 

            if (!empty($orderRes)) {
                return response()->json([
                    'code'=> 400,
                    'msg'=> "You already bought this course",
                    'data'=> $orderRes,
                ], 200); 
            }

            $yourDomain = env('APP_URL');

            $map = [];
            $map['user_token'] = $token;
            $map['course_id'] = $course_id;
            $map['total_amount'] = $course_result->price;
            $map['status'] = 0;
            $map['created_at'] = Carbon::now();
            $orderNum = Order::insertGetId($map);

            $checkOutSession = Session::create(
                [
                    'line_items'=>[[
                        'price_data'=>[
                            'currency'=>'USD',
                            'product_data'=>[
                                'name'=>$course_result->name,
                                'description'=>$course_result->description,
                            ],
                            'unit_amount'=>intval(($course_result->price) * 100),
                        ],
                        'quantity'=>1,
                    ]],
                    'payment_intent_data'=>[
                        'metadata'=>['order_num'=>$orderNum, 'user_token'=>$token],
                    ],
                    'metadata'=>['order_num'=>$orderNum, 'user_token'=>$token],
                    'mode'=>'payment',
                    'success_url'=> $yourDomain . '/success',
                    'cancel_url'=> $yourDomain . '/cancel',
                ],
            );

            return response()->json([
                'code'=> 200,
                'msg'=> "Successfully bought the course",
                'data'=> $checkOutSession->url,
            ], 200); 
        }
        catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'msg' => $th->getMessage(),
                'data' => null,
            ], 500);
        }
    }



    // 
    // 
    // XENDIT
    // 
    // 

    public function __construct()
    {
        Configuration::setXenditKey(env('XENDIT_SECRET_KEY'));
    }

    public function createInvoiceXendit(Request $request)
    {
        try {
            $order = new Payment;
            $order->user_id = $request->input('user_id');
            $order->external_id = (string) Str::uuid();
            $order->amount = $request->input('amount');
            $order->payer_email = $request->input('payer_email');
            $order->description = $request->input('description');
            $createInvoice = new CreateInvoiceRequest([
                'external_id' => $order->external_id,
                'amount' => $request->input('amount'),
                'payer_email' => $request->input('payer_email'),
                'description' => $request->input('description'),
                'invoice_duration' => 172800,
            ]);


            $apiInstance = new InvoiceApi();
            $generateInvoice = $apiInstance->createInvoice($createInvoice);
            $order->checkout_link = $generateInvoice['invoice_url'];
            $order->save();
            return response()->json([
                'message' => 'Invoice created',
                'checkout_link' => $order->checkout_link,
            ]);


        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function handleWebhook(Request $request)
    {
        $data = $request->all();
        $external_id = $data['external_id'];
        $status = strtolower($data['status']);
        $payment_method = $data['payment_method'];


        $order = Payment::where('external_id', $external_id)->first();
        $order->status = $status;
        $order->payment_method = $payment_method;
        $order->save();


        return response()->json([
            'message' => 'Webhook received',
            'status' => $status,
            'payment_method' => $payment_method,
        ]);
    }

}
