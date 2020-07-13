<?php

namespace App\Http\Controllers;

use App\Document;
use App\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{

    public function getenv($type){
        if($type == 'local'){
            $env = 'http://engine-signature.test/';
        } else {
            $env = 'http://52.74.178.166:82/';
        }
    }
    
    public function index(){
        return response()->json('document engine');
    }

    public function store(Request $request){

        $validator = validator::make($request->all(),
        [
            'userid' => 'required',
            'categoryid' => 'required',
            'documentfile' => 'required|mimes:doc,docx,pdf',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        } else {

            $userid = $request->input('userid');
            $documentfile = $request->file('documentfile');
            $cc = $request->input('cc');
            $categoryid = $request->input('categoryid');

            if($categoryid == '3'){
                $tempstatus = 'success';
            } else {
                $tempstatus = 'process';
            }
            
            $status = 'process';
            $extenstion = $documentfile->getClientOriginalExtension();
            $filename = rand(11111, 99999) . '.' . $extenstion;
            $destinationPath = 'document';

            $documentfile->move($destinationPath, $filename);

            $document = new Document;
            $document->file = $filename;
            $document->userid = $userid;
            $document->cc = $cc;
            $document->status = $status;
            $document->tempstatus = $tempstatus;
            $document->category = $categoryid;

            $document->save();

            return response()->json(['status'=>'success']);

        }

    }

    public function details(Request $request){

        $env = $this->getenv('live');

        $id = $request->input('id');

        $data = Document::find($id);

        if($data){

            $tempfile = $data->file;
            $dirfile = $env . 'document/'. $tempfile;

            if($data->cc){
                $cc = json_decode($data->cc);
            } else {
                $cc = $data->cc;
            }

        }
        
        $tempArray = [
            'id' => $data->id,
            'title' => $data->title,
            'file' => $dirfile,
            'userid' => $data->userid,
            'cc' => $cc,
            'status' => $data->status,
        ];

        return response()->json(['status'=>'success', 'value'=>$tempArray]);
    }

    public function all(){

        $document = Document::all();

        return response()->json(['status'=>'success', 'value'=>$document]);

    }

    public function update(Request $request){

        $id = $request->input('id');
        $title = $request->input('title');
        $documentfile = $request->file('documentfile');
        $cc = $request->input('cc');

        $document = Document::find($id);

        if($document){

            if($title == null){
                $title = $document->title;
            }

            if($documentfile == null){
                $filename = $document->file;
            } else {
                $extenstion = $documentfile->getClientOriginalExtension();
                $filename = rand(11111, 99999) . '.' . $extenstion;
                $destinationPath = 'document';

                $documentfile->move($destinationPath, $filename);
            }

            if($cc == null){
                $cc = $document->cc;
            }

            $document->title = $title;
            $document->file = $filename;
            $document->cc = $cc;

            $document->save();

            return response()->json(['status'=>'success']);
        } else {

            return response()->json(['status'=>'failed']);
        }

    }

    public function destroy(Request $request){
        $id = $request->input('id');

        $data = Document::find($id);
        if($data){

            $data->delete($data->id);
            return response()->json(['status'=>'success']);

        } else {

            return response()->json(['status'=>'failed']);

        }
    }

    public function userdoc(Request $request){

        $env = $this->getenv('live');

        $finalArray = array();
        $userid = $request->input('userid');

        $document = Document::where('userid',$userid)->get();
        foreach($document as $data){

            $tempfile = $data->file;
            $dirfile = $env . 'document/'. $tempfile;

            if($data->cc){
                $data->cc = json_decode($data->cc);
            } 

            $tempArray = [
                'id' => $data->id,
                'title' => $data->title,
                'file' => $dirfile,
                'userid' => $data->userid,
                'cc' => $data->cc,
                'status' => $data->status,
            ];

            array_push($finalArray,$tempArray);

        }

        return response()->json(['status'=>'success', 'value'=>$finalArray]);

    }

    public function detailstosign(Request $request){

        $env = $this->getenv('live');

        $validator = validator::make($request->all(),
        [
            'docid' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        } else {

            $docid = $request->input('docid');

            $document = Document::find($docid);

            if($document){

                $tempfile = $document->file;
                $dirfile = $env . 'document/'. $tempfile;

                if($document->cc){
                    $cc = json_decode($document->cc);
                } else {
                    $cc = $document->cc;
                }
                
                $tempArray = [
                    'id' => $document->id,
                    'title' => $document->title,
                    'file' => $dirfile,
                    'userid' => $document->userid,
                    'cc' => $cc,
                    'status' => $document->status,
                ];
        
                return response()->json(['status'=>'success', 'value'=>$tempArray]);

            } else {
                return response()->json(['status'=>'failed', 'value'=>'document not exist']);
            }
        }
    }

    public function successsign(Request $request){

        $validator = validator::make($request->all(),
        [
            'docid' => 'required',
            'documentfile' => 'required|mimes:doc,docx,pdf',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        } else {

            $docid = $request->input('docid');
            $documentfile = $request->file('documentfile');

            $document = Document::find($docid);

            $tempstatus = 'success';

            if($document){

                $extenstion = $documentfile->getClientOriginalExtension();
                $filename = rand(11111, 99999) . '.' . $extenstion;
                $destinationPath = 'document';
    
                $documentfile->move($destinationPath, $filename);

                if($document->cc){
                    $status = 'process';
                } else {
                    $status = 'finish';
                }

                $document->status = $status;
                $document->file = $filename;
                $document->tempstatus = $tempstatus;

                $document->save();

                return response()->json(['status'=>'success']);
            } else {
                return response()->json(['status'=>'failed', 'value'=>'document not exist']);
            }
        }
    }

    public function listtosign(Request $request){

        $env = $this->getenv('live');

        $validator = validator::make($request->all(),
        [
            'userid' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        } else {

            $userid = $request->input('userid');
            $finalArray = array();

            $document = Document::where('userid',$userid)->where('tempstatus','process')->get();
            
            if($document){

                foreach($document as $data){

                    $tempfile = $data->file;
                    $dirfile = $env . 'document/'. $tempfile;

                    if($data->cc){
                        $data->cc = json_decode($data->cc);
                    }

                    $tempArray = [
                        'id' => $data->id,
                        'title' => $data->title,
                        'file' => $dirfile,
                        'userid' => $data->userid,
                        'cc' => $data->cc,
                        'status' => $data->status,
                    ];
                    
                    array_push($finalArray,$tempArray);

                }
                return response()->json(['status'=>'success','value'=>$finalArray]);

            } else {
                return response()->json(['status'=>'failed','document'=>'document does not exist']);
            }

        }
    }
   
}
