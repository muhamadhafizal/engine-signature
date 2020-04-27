<?php

namespace App\Http\Controllers;

use App\Document;
use App\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    public function index(){
        return response()->json('document engine');
    }

    public function store(Request $request){

        $validator = validator::make($request->all(),
        [
            'title' => 'required',
            'userid' => 'required',
            'documentfile' => 'required|mimes:doc,docx,pdf',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        } else {

            $title = $request->input('title');
            $userid = $request->input('userid');
            $documentfile = $request->file('documentfile');
            $cc = $request->input('cc');

            if($cc){
                $status = 'process';
            } else {
                $status = 'finish';
            }

            $extenstion = $documentfile->getClientOriginalExtension();
            $filename = rand(11111, 99999) . '.' . $extenstion;
            $destinationPath = 'document';

            $documentfile->move($destinationPath, $filename);

            $document = new Document;
            $document->title = $title;
            $document->file = $filename;
            $document->userid = $userid;
            $document->cc = $cc;
            $document->status = $status;

            $document->save();

            return response()->json(['status'=>'success']);

        }

    }

    public function details(Request $request){

        // $env = 'http://engine-signature.test/';
        $env = 'http://52.74.178.166:82/';
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

        // $env = 'http://engine-signature.test/';
        $env = 'http://52.74.178.166:82/';
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
   
}