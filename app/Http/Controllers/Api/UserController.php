<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\Course;

class UserController extends Controller 
{

    public function createUser(Request $request) 
    {

        try {
            $validateUser = Validator::make($request->all(),
            [
                'avatar' => 'required',
                'type' => 'required',
                'open_id' => 'required',
                'name' => 'required',
                'email' => 'required|email',
                // 'password' => 'required|min:6', 
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors(),
                ], 401);
            }
            
            // validated will have all user fie lds values
            // we can save in the database
            $validated = $validateUser->validated();

            $map = [];
            // email, phone, gogle, facebook, apple
            $map['type'] = $validated['type'];
            $map['open_id'] = $validated['open_id'];

            $user = User::where($map)->first();  
            // Whether user has already logged in or not
            // Empty means doesn't exist
            // Then save the user in the database for the first time
            if (empty($user->id)) {
                // This certain user has neveer been in our database
                // Assign the user in the database
                // This token is user id
                $validated["token"] = md5(uniqid().rand(10000, 99999));
                // User first time created
                $validated['created_at'] = Carbon::now();
                // Encrypt password
                // $validated['password'] = Hash::make($validated['password']);

                //returns the id of the row after saving
                $userID = User::insertGetId($validated);
                // All user information
                $userInfo = User::where('id', '=', $userID)->first( );

                $accessToken = $userInfo->createToken((uniqid()))->plainTextToken;
                
                $userInfo->access_token = $accessToken; 
                User::where('id', '=', $userID)->update([
                    'access_token' => $accessToken,
                ]);
                
                return response()->json([
                    'code' => 200,
                    'msg' => 'User Created Successfully',
                    'data' => $userInfo,
                ], 200);
            }
            
            // User previously has logged in
            $accessToken = $user->createToken(uniqid())->plainTextToken; 
            $user->access_token = $accessToken;
            User::where('open_id', '=', $validated['open_id'])->update([
                'access_token' => $accessToken,
            ]);
            
            return response()->json([
                'code' => 200,
                'msg' => 'User Logged in Successfully',
                'data' => $user,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function loginAdmin(Request $request) 
    {

        try {
            $validateUser = Validator::make($request->all(),
            [
                'avatar' => 'required',
                'type' => 'required',
                'open_id' => 'required',
                'name' => 'required',
                'email' => 'required|email|regex:(admin@gmail.com)',
                'password' => 'required', 
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors(),
                ], 401);
            }
            
            // validated will have all user fie lds values
            // we can save in the database
            $validated = $validateUser->validated();

            $map = [];
            // email, phone, gogle, facebook, apple
            $map['type'] = $validated['type'];
            $map['open_id'] = $validated['open_id'];

            $user = User::where($map)->first();  
            
            // User previously has logged in
            $accessToken = $user->createToken(uniqid())->plainTextToken; 
            $user->access_token = $accessToken;
            User::where('open_id', '=', $validated['open_id'])->update([
                'access_token' => $accessToken,
            ]);
            
            return response()->json([
                'code' => 200,
                'msg' => 'Successfully Logging In as a Admin',
                'data' => $user,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors(),
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully!',
                'token' => $user->createToken("API TOKEN")->plainTextToken,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function userListAdmin()
    {
        try {
            $result = User::orderBy(
                "id"
            )->select([
                "id",
                "name",
                "email",
                "created_at",
            ])->get();
            
            return response()->json([
                'code' => 200,
                'msg' => "Successfully getting response",
                'data' => $result,
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
}