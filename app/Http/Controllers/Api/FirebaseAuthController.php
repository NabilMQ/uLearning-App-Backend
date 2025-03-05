<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Http\Request;

class FirebaseAuthController extends Controller
{
    protected $auth;
    public function __construct()
    {
        $this->auth = Firebase::auth();
    }

    public function loginFirebaseAdmin(Request $request)
    {
        try {
            $email = $request->input("email");
            $password = $request->input("password");
            
            $response = $this->auth->signInWithEmailAndPassword(
                $email,
                $password,
            )->data();

            return response()->json([
                'code' => 200,
                'msg' => 'Successfully Logging in to Firebase',
                'data' => $response,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
