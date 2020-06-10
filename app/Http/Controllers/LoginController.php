<?php

namespace App\Http\Controllers;
use App\User;
use App\Document;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index(){
        return response()->json('login engine');
    }

    public function main(Request $request){

        $tempArray = array();

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
                $userid = $user->id;
                $document = Document::where('status','process')->get();
                foreach($document as $data){  
                    if($data->cc){
        
                        $a = $data->cc;
                        $b = json_decode($a,true);
                        $length = count($b['cc']);
        
                        for($i=0; $i<$length; $i++){
                            if($userid == $b['cc'][$i]['id']){
                                if($b['cc'][$i]['status'] != 'success'){
                                    array_push($tempArray,$data);
                                }
                            }   
                        }
                    }
                }
                if($tempArray){
                    $docstatus = 'exist';
                } else {
                    $docstatus = 'notexist';
                }


                $token = $user->createToken('MyApp')-> accessToken;
                return response()->json(['status'=>'success', 'api_key'=>$token, 'value' => $user, 'docstatus'=> $docstatus]);

            } else {

                return response()->json(['status'=>'failed']);

            }

        }

    }

    public function unauthorized(){
        return response()->json(['error'=>'Unauthorised'], 401); 
    }
}
