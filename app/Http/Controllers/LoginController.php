<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index(){
        return response()->json('login engine');
    }

    public function main(Request $request){

        $validator = validator::make($request->all(),
        [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        } else {

            $email = $request->input('email');
            $password = $request->input('password');

            $user = User::where('email',$email)->where('password',$password)->first();

            if($user){

                return response()->json(['status'=>'success', 'api_key'=>'0', 'value' => $user]);

            } else {

                return response()->json(['status'=>'failed']);

            }

        }

    }
}

