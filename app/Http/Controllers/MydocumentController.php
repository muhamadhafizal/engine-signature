<?php

namespace App\Http\Controllers;

use App\Document;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MydocumentController extends Controller
{
    public function index(){
        return response()->json('mydocument engine');
    }

    public function personal(Request $request){

        $userid = $request->input('userid');
        $env = 'http://engine-signature.test/';
        $finalArray = array();

        $document = Document::where('userid',$userid)->whereNull('cc')->get();

        foreach($document as $data){

            $tempfile = $data->file;
            $dirfile = $env . 'document/'. $tempfile;

            $user = User::find($data->userid);

            $time = date('Y-m-d h:i:sa', strtotime($data->updated_at));

            $historyArray = [

                'name' => $user->name,
                'time' => $time,
            ];

            $tempArray = [

                'id' => $data->id,
                'title' => $data->title,
                'file' => $dirfile,
                'userid' => $data->userid,
                'user created' => $user->name,
                'status' => $data->status,
                'history' => $historyArray,
            ];

            array_push($finalArray,$tempArray);

        }

        return response()->json(['status'=>'success','value'=> $finalArray]);
    }

    public function group(Request $request){

        $userid = $request->input('userid');
        $finalArray = array();
        $env = 'http://engine-signature.test/';

        $document = Document::where('userid',$userid)->whereNotNull('cc')->where('status','finish')->get();

        foreach($document as $data){
            $historyArray = array();
            $a = $data->cc;
            $b = json_decode($a,true);
            $length = count($b['cc']);
    
            for($i=0; $i<$length; $i++){
                $tempid = $b['cc'][$i]['id'];
                $tempuser = User::find($tempid);
                
                $timecc = $b['cc'][$i]['time'];

                $temphistoryArray = [
                    'name' => $tempuser->name,
                    'time' => $timecc,
                ];
                array_push($historyArray,$temphistoryArray);   
            }

            $tempfile = $data->file;
            $dirfile = $env . 'document/'. $tempfile;

            $user = User::find($data->userid);

            $time = date('Y-m-d h:i:sa', strtotime($data->updated_at));

            $historycreated = [

                'name' => $user->name,
                'time' => $time,
            ];

            array_push($historyArray,$historycreated);

            $tempArray = [
                'id' => $data->id,
                'title' => $data->title,
                'file' => $dirfile,
                'userid' => $data->userid,
                'user created' => $user->name,
                'status' => $data->status,
                'history' => $historyArray,
            ];
            array_push($finalArray,$tempArray);
        }   
        return response()->json(['status'=>'success', 'value'=>$finalArray]);
    }
}
