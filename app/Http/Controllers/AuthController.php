<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(),
        [
            'name'=>'required|string',
            'email'=>'required|email:rcf,dns|unique:users,email',
            'password'=>'required|string|min:6'
        ]);

        if($validator->fails()){
           return response()->json([
                'status'=>'error',
                'message'=>'Invalid Field',
                'errors'=>$validator->errors()
            ], 422); 
        }
        

        try{
                $user = User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>bcrypt($request->password)
                
            ]);

            $token = $user->createToken('Token-Kamu')->plainTextToken;
            return response()->json([
                'status'=>'success',
                'message'=>'Registration Successfull',
                'data'=>[
                    'id'=>$user->id,
                    'name'=>$user->name,
                    'email'=>$user->email,
                    'created_at'=>$user->created_at,
                    'updated_at'=>$user->updated_at,
                    'token'=>$token
                ]
            ], 201);
            }
        
        catch(Exception $ex){
                return response()->json([
                    'status'=>'error',
                    'message'=>'Server Error',
                    'errors'=>$ex->getMessage()
                ], 422);
            }
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(),
        [
            'email'=>'required|string|email:rcf,dns',
            'password'=>'required|string|min:6'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>'error',
                'message'=>'Username or password incorrect',
                'errors'=>$validator->errors()
            ], 422);
        }

        $credentials = $request->only('email','password');
        if(!Auth::attempt($credentials)){
             return response()->json([
                'status'=>'error',
                'message'=>'Username or password incorrect'
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $token = $user->createToken('Token_Kamu')->plainTextToken;

        return response()->json([
            'status'=>'success',
            'message'=>'Login successful',
            'data'=>[
                'id'=>$user->id,
                'name'=>$user->name,
                'email'=>$user->email,
                'created_at'=>$user->created_at,
                'updated_at'=>$user->updated_at,
                'token'=>$token
            ]
        ], 200);
    }

    public function logout(Request $request){
        
        try{
            
            $request->user()->tokens()->delete();
            return response()->json([
                'status'=>'success',
                'message'=>'Logout successful'
            ], 200);
        }
        catch(Exception $ex){
            return response()->json([
                'status'=>'error',
                'message'=>'Unauthenticated',
                'error'=>$ex->getMessage()
            ], 401);
        }
    }

}