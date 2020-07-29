<?php

namespace App\Http\Controllers;

use App\Document;
use App\History;
use App\Category;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{

    public function getenv($type){
        if($type == 'local'){
            $env = 'http://engine-signature.test/';
        } else {
            $env = 'https://codeviable.com/engine-signature/public/';
        }

        return $env;
    }
    
    public function index(){
        return response()->json('document engine');
    }

    public function store(Request $request){

        $validator = validator::make($request->all(),
        [
            'userid' => 'required',
            'documentfile' => 'required|mimes:doc,docx,pdf',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        } else {

            $userid = $request->input('userid');
            $documentfile = $request->file('documentfile');
            $cc = $request->input('cc');
         
            $a = json_decode($cc,true);

            $a['cc'][0]['turn'] = 'active';

            $b = json_encode($a);
            $status = 'process';
            $extenstion = $documentfile->getClientOriginalExtension();
            $filename = rand(11111, 99999) . '.' . $extenstion;
            $destinationPath = 'document';

            $documentfile->move($destinationPath, $filename);

            $document = new Document;
            $document->file = $filename;
            $document->userid = $userid;
            $document->cc = $b;
            $document->status = $status;

            $document->save();

            return response()->json(['status'=>'success','value'=>'success upload document']);

        }

    }

    public function details(Request $request){

        $env = $this->getenv('live');
        $id = $request->input('id');

        $historyArray = array();

        $data = Document::find($id);

        $category = Category::find($data->category);
        $user = User::find($data->userid);

        $time = date('Y-m-d h:i:sa', strtotime($data->created_at));

        $tempfile = $data->file;
        $dirfile = $env . 'document/'. $tempfile;
   

        if($data->category == '1'){

            $tempArray = [
                'userid' => $user->id,
                'name' => $user->name,
                'time' => $time,
            ];

            array_push($historyArray,$tempArray);

        } elseif($data->category == '2'){

            $a = $data->cc;
            $b = json_decode($a,true);
            $length = count($b['cc']);

            $tempArray = [
                'userid' => $user->id,
                'name' => $user->name,
                'time' => $time,
            ];

            array_push($historyArray,$tempArray);

            for($i=0; $i<$length; $i++){
                $tempid = $b['cc'][$i]['id'];
                $tempuser = User::find($tempid);
                
                $timecc = $b['cc'][$i]['time'];

                $temphistoryArray = [
                    'userid' => $tempuser->id,
                    'name' => $tempuser->name,
                    'time' => $timecc,
                ];
                array_push($historyArray,$temphistoryArray);   
            }

        } else{

            $a = $data->cc;
            $b = json_decode($a,true);
            $length = count($b['cc']);

            for($i=0; $i<$length; $i++){
                $tempid = $b['cc'][$i]['id'];
                $tempuser = User::find($tempid);
                
                $timecc = $b['cc'][$i]['time'];

                $temphistoryArray = [
                    'userid' => $tempuser->id,
                    'name' => $tempuser->name,
                    'time' => $timecc,
                ];
                array_push($historyArray,$temphistoryArray);   
            }

        }

        $finalsortarray = array();

        if($data->status == 'finish'){
            $ordered = array();
            foreach ($historyArray as $event) {
                
                $ordered[$event['time']] = $event;
                
            }
            ksort($ordered);
           
            foreach($ordered as $history){
                    
                $tempsort = [
                    'userid' => $history['userid'],
                    'name' => $history['name'],
                    'time' => $history['time'],
                ];
    
                    array_push($finalsortarray,$tempsort);
    
            }

        } else {
            $finalsortarray = $historyArray;
        }

       
        
        $tempArray = [
            'id' => $data->id,
            'category' => $category->name,
            'file' => $dirfile,
            'userid' => $data->userid,
            'history' => $finalsortarray,
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

                return response()->json(['status'=>'success','value'=>'success update document']);
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

                    $category = Category::where('id',$data->category)->first();

                    if($data->cc){
                        $data->cc = json_decode($data->cc);
                    }

                    $tempArray = [
                        'id' => $data->id,
                        'file' => $dirfile,
                        'category' => $category->name,
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

    public function listdocument(Request $request){

        $status = $request->input('status');
        $userid = $request->input('userid');

        $env = $this->getenv('live');

        $totalarray = array();
        $valuearray = array();
        $listarray = array();
        $finalarray = array();

        $documentprocess = Document::where('status','process')->where('userid',$userid)->orderBy('created_at','DESC')->get();

        $documentrejected = Document::where('status','rejected')->where('userid',$userid)->orderBy('created_at','DESC')->get();

        $documentcompleted = Document::where('status','completed')->where('userid',$userid)->orderBy('created_at','DESC')->get();

        $totalprocess = count($documentprocess);
        $totalrejected = count($documentrejected);
        $totalcompleted = count($documentcompleted);

        $totalarray = [

            'totalprocess' => $totalprocess,
            'totalrejected' => $totalrejected,
            'totalcompleted' => $totalcompleted,

        ];

        //list document based on status
        if($status == 'process'){
            $tempdocument = $documentprocess;
        } elseif($status == 'rejected'){
            $tempdocument = $documentrejected;
        } else{
            $tempdocument = $documentcompleted;
        }

        foreach($tempdocument as $data){
            $temp = explode(' ',$data->updated_at);

            $tempfile = $data->file;
            $dirfile = $env . 'document/'. $tempfile;
            
            $valuearray = [
                'id' => $data->id,
                'file' => $dirfile,
                'date' => $temp[0],
                'time' => $temp[1],
            ];

            array_push($listarray,$valuearray);
            
        }

        $finalarray = [
            'count' => $totalarray,
            'listbystatus' => $listarray, 
        ];

        return response()->json(['status'=>'success', 'value'=>$finalarray]);
        

    }
   
}
