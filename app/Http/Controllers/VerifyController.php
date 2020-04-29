<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\User;
use Illuminate\Support\Facades\Validator;

class VerifyController extends Controller
{
    public function index(){
        return response('verify engine');
    }

    public function requesttac(Request $request){

        $validator = validator::make($request->all(),
        [
            'userid' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        } else {
            $userid = $request->input('userid');

            $user = User::find($userid);

            if($user){

                $phonenumber = $user->phone;
                $finalformat = "'+6". $phonenumber . "'";

                $token = getenv("TWILIO_AUTH_TOKEN");
                $twilio_sid = getenv("TWILIO_SID");
                $twilio_verify_sid = getenv("TWILIO_VERIFY_SID");
                $twilio = new Client($twilio_sid, $token);
                $twilio->verify->v2->services($twilio_verify_sid)
                ->verifications
                ->create($finalformat, "sms");

                return response()->json(['status'=>'success']);
            } else {
                return response()->json(['status'=>'failed', 'value'=>'user not exist']);
            }

        }

    }

    public function sendtac(Request $request){

        $validator = validator::make($request->all(),
        [
            'taccode' => 'required',
            'userid' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        } else {
            $taccode = $request->input('taccode');
            $userid = $request->input('userid');

            $user = User::find($userid);

            if($user){

                $phonenumber = $user->phone;
                $finalformat = "'+6". $phonenumber . "'";

                $token = getenv("TWILIO_AUTH_TOKEN");
                $twilio_sid = getenv("TWILIO_SID");
                $twilio_verify_sid = getenv("TWILIO_VERIFY_SID");
                $twilio = new Client($twilio_sid, $token);
                $verification = $twilio->verify->v2->services($twilio_verify_sid)
                ->verificationChecks
                ->create($taccode, array('to' => $finalformat));
                if ($verification->valid) {
                    return response()->json(['status'=>'success','value'=>'success verification code by phone number']);
                }
                return response()->json(['status'=>'failed','value'=>'Invalid verification code entered!']);
            } else {
                return response()->json(['status'=>'failed','value'=>'user not exist']);
            }
        }
    }
}
