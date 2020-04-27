<?php

namespace App\Http\Controllers;

use App\Document;
use App\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReceivedocumentController extends Controller
{
    public function index(){
        return response()->json('receivedocument engine');
    }

    public function userrecdoc(Request $request){
        
        // $env = 'http://engine-signature.test/';
        $env = 'http://52.74.178.166:82/';

        $tempArray = array();
        $documentArray = array();
        $userid = $request->input('userid');

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

        foreach($tempArray as $data){

            $tempfile = $data->file;
            $dirfile = $env . 'document/'. $tempfile;
            $cc = json_decode($data->cc);

            $filterArray = [
                'id' => $data->id,
                'title' => $data->title,
                'file' => $dirfile,
                'userid' => $data->userid,
                'cc' => $cc,
                'status' => $data->status,
            ];

            array_push($documentArray,$filterArray);
        }
        return response()->json(['status'=>'success', 'value'=>$documentArray]);

    }

    public function userupdate(Request $request){

        // $env = 'http://engine-signature.test/';
        $env = 'http://52.74.178.166:82/';

        $id = $request->input('id');
        $userid = $request->input('userid');
        $answer = null;
        $documentfile = $request->file('documentfile');

        $document = Document::find($id);

        $extenstion = $documentfile->getClientOriginalExtension();
        $filename = rand(11111, 99999) . '.' . $extenstion;
        $destinationPath = 'document';

        $documentfile->move($destinationPath, $filename);

        $a = $document->cc;
        $b = json_decode($a,true);
        $length = count($b['cc']);

        for($i=0; $i<$length; $i++){
            if($userid == $b['cc'][$i]['id']){   
                $b['cc'][$i]['status'] = 'success';
                $b['cc'][$i]['time'] = date("Y-m-d h:i:sa");
            }

            if($b['cc'][$i]['status'] != 'success'){
                $answer = 'process';
            }
        }

        if($answer == 'process'){
            $status = 'process';
        } else {
            $status = 'finish';
        }

        $c = json_encode($b);
        
        $document->file = $filename;
        $document->cc = $c;
        $document->status = $status;
        $document->save();

        return response()->json(['status'=>'success']);

    }
}
