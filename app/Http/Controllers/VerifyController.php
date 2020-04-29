<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Validator;

class VerifyController extends Controller
{
    public function index(){
        return response('verify engine');
    }

    public function requesttac(Request $request){

        $validator = validator::make($request->all(),
        [
            'phonenumber' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        } else {
            $phonenumber = $request->input('phonenumber');

            $finalformat = "'+6". $phonenumber . "'";

            $token = getenv("TWILIO_AUTH_TOKEN");
            $twilio_sid = getenv("TWILIO_SID");
            $twilio_verify_sid = getenv("TWILIO_VERIFY_SID");
            $twilio = new Client($twilio_sid, $token);
            $twilio->verify->v2->services($twilio_verify_sid)
            ->verifications
            ->create($finalformat, "sms");
            return response()->json('success');
        }

    }

    public function sendtac(Request $request){

        $validator = validator::make($request->all(),
        [
            'taccode' => 'required',
            'phonenumber' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        } else {
            $taccode = $request->input('taccode');
            $phonenumber = $request->input('phonenumber');

            $token = getenv("TWILIO_AUTH_TOKEN");
            $twilio_sid = getenv("TWILIO_SID");
            $twilio_verify_sid = getenv("TWILIO_VERIFY_SID");
            $twilio = new Client($twilio_sid, $token);
            $verification = $twilio->verify->v2->services($twilio_verify_sid)
            ->verificationChecks
            ->create($taccode, array('to' => '+60127850154'));
            if ($verification->valid) {
                return response()->json('process verified by phone number');
            }
            return response()->json('Invalid verification code entered!');
        }

    }
}
