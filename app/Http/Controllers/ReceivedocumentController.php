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
        $env = 'https://codeviable.com/engine-signature/public/';

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
                        if($b['cc'][$i]['status'] != 'success' && $b['cc'][$i]['turn'] == 'active'){
                            array_push($tempArray,$data);
                        }
                    }   
                }
            }
        }

        foreach($tempArray as $data){

            $tempfile = $data->file;
            $dirfile = $env . 'document/'. $tempfile;

            if($data->cc){

                $a = $data->cc;
                $b = json_decode($a,true);
                $length = count($b['cc']);

                for($i=0; $i<$length; $i++){
                    if($userid == $b['cc'][$i]['id']){
                        $coordinatex = $b['cc'][$i]['coordinatex'];
                        $coordinatey = $b['cc'][$i]['coordinatey'];
                    }   
                }
            }
          
            $temp = explode(' ',$data->updated_at);
            
            $filterArray = [
                'id' => $data->id,
                'file' => $dirfile,
                'coordinatex' => $coordinatex,
                'coordinatey' => $coordinatey,
                'date' => $temp[0],
                'time' => $temp[1],
            ];

            array_push($documentArray,$filterArray);
        }
        //count documentarray
        $count = count($documentArray);

        $finalarray = array();
        $finalarray = [
            'count' => $count,
            'data' => $documentArray,
        ];

        return response()->json(['status'=>'success', 'value'=>$finalarray]);

    }

    public function userapprove(Request $request){

        // $env = 'http://engine-signature.test/';
        $env = 'https://codeviable.com/engine-signature/public/';

        $id = $request->input('docid');
        $userid = $request->input('userid');
        $documentfile = $request->file('documentfile');
        $answer = null;

        $document = Document::find($id);

        if($document != null){
            
            $extenstion = $documentfile->getClientOriginalExtension();
            $filename = rand(11111, 99999) . '.' . $extenstion;
            $destinationPath = 'document';
    
            $documentfile->move($destinationPath, $filename);
    
            $a = $document->cc;
            $b = json_decode($a,true);
            $length = count($b['cc']);
            $endarray = $length - 1;
            for($i=0; $i<$length; $i++){
               
                if($userid == $b['cc'][$i]['id']){   
                    $b['cc'][$i]['status'] = 'success';
                    $b['cc'][$i]['turn'] = '-';
                    
                    if($b['cc'][$i]['id'] != $endarray){
                        $b['cc'][$i+1]['turn'] = 'active';
                    }
                    
                }
    
                if($b['cc'][$i]['status'] != 'success'){
                    $answer = 'process';
                }
            }
    
            if($answer == 'process'){
                $status = 'process';
            } else {
                $status = 'completed';
            }
    
            $c = json_encode($b);
               
            $document->file = $filename;
            $document->cc = $c;
            $document->status = $status;
            $document->save();
    
            $history = new History;
            $history->userid = $userid;
            $history->docid = $id;
            $history->filename = $filename;
            $history->status = 'approve';
            $history->save();
    
            return response()->json(['status'=>'success','value'=>'success update document']);

        } else {

            return response()->json(['status'=>'failed','value'=>'sorry document does not exist']);

        }

       

    }

    public function userrejected(Request $request){

        $id = $request->input('docid');
        $userid = $request->input('userid');
        $documentfile = $request->file('documentfile');

        $document = Document::find($id);

        //get cc based on user id and update turn to -

        $a = $document->cc;
        $b = json_decode($a,true);
        $length = count($b['cc']);
        for($i=0; $i<$length; $i++){
           
            if($userid == $b['cc'][$i]['id']){   
  
                $b['cc'][$i]['turn'] = '-';
                
            }

        }

        $c = json_encode($b);
        
        if($documentfile){

            $extenstion = $documentfile->getClientOriginalExtension();
            $filename = rand(11111, 99999) . '.' . $extenstion;
            $destinationPath = 'document';

            $documentfile->move($destinationPath, $filename);

        } else {
            $filename = $document->file;
        }

        $document->status = 'rejected';
        $document->file = $filename;
        $document->cc = $c;

        $document->save();

        $history = new History;
        $history->userid = $userid;
        $history->filename = $filename;
        $history->status = 'rejected';
        $history->docid = $id;

        $history->save();

        return response()->json(['status'=>'success','value'=>'success user reject document']);

    }

    public function requesterupdate(Request $request){

        $docid = $request->input('docid');
        $userid = $request->input('userid');
        $documentfile = $request->file('documentfile');

        $document = Document::find($docid);

        //file
        $extenstion = $documentfile->getClientOriginalExtension();
        $filename = rand(11111, 99999) . '.' . $extenstion;
        $destinationPath = 'document';

        $documentfile->move($destinationPath, $filename);

        //status
        $status = 'process';

        //cc
        $a = $document->cc;
        $b = json_decode($a,true);
        $length = count($b['cc']);

        for($i=0; $i<$length; $i++){
          
            $b['cc'][$i]['status'] = 'process';

        }

        $b['cc'][0]['turn'] = 'active';

        $c = json_encode($b);
        
        $document->file = $filename;
        $document->status = $status;
        $document->cc = $c;

        $history = new History;
        $history->userid = $userid;
        $history->docid = $docid;
        $history->status = 'resubmit';
        $history->filename = $filename;

        $document->save();
        $history->save();

        return response()->json(['status'=>'success','value'=>'success update document']);
    }
}
