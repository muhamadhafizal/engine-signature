<?php

namespace App\Http\Controllers;

use App\Document;
use App\User;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MydocumentController extends Controller
{
    public function index(){
        return response()->json('mydocument engine');
    }

    public function personal(Request $request){

        $userid = $request->input('userid');

        // $env = 'http://engine-signature.test/';
        $env = 'http://52.74.178.166:82/';

        $finalArray = array();

        $document = Document::where('userid',$userid)->whereNull('cc')->orderBy('created_at','DESC')->get();

        foreach($document as $data){

            $tempfile = $data->file;
            $dirfile = $env . 'document/'. $tempfile;

            $user = User::find($data->userid);

            $category = Category::find($data->category);

            $time = date('Y-m-d h:i:sa', strtotime($data->updated_at));

            $historyArray = [

                'name' => $user->name,
                'time' => $time,
            ];

            $tempArray = [

                'id' => $data->id,
                'category' => $category->name,
                'file' => $dirfile,
                'userid' => $data->userid,
                'user created' => $user->name,
                'status' => $data->status,
                'history' => $historyArray,
            ];

            array_push($finalArray,$tempArray);

        }

        $countarray = array();

        $count = count($finalArray);
        $countarray = [
            'count' => $count,
            'data' => $finalArray,
        ];

        return response()->json(['status'=>'success','value'=> $countarray]);
    }

    public function group(Request $request){

        $userid = $request->input('userid');
        $categoryid = $request->input('categoryid');
        $finalArray = array();
        $tempArray = array();

        // $env = 'http://engine-signature.test/';
        $env = 'http://52.74.178.166:82/';

        if($categoryid){

            $document = Document::whereNotNull('cc')->where('category',$categoryid)->where('status','finish')->orderBy('created_at','DESC')->get();

        } else {

            $document = Document::whereNotNull('cc')->where('status','finish')->orderBy('created_at','DESC')->get();

        }

        foreach($document as $data){  
            if($data->userid == $userid){
                array_push($tempArray,$data);
            }
            if($data->cc){

                $a = $data->cc;
                $b = json_decode($a,true);
                $length = count($b['cc']);

                for($i=0; $i<$length; $i++){
                    if($userid == $b['cc'][$i]['id']){
                        if($b['cc'][$i]['status'] == 'success'){
                            array_push($tempArray,$data);
                        }
                    }   
                }
            }
        }

        foreach($tempArray as $data){
            $historyArray = array();
            $a = $data->cc;
            $b = json_decode($a,true);
            $length = count($b['cc']);

            $category = Category::find($data->category);
            $user = User::find($data->userid);
            
            //check category 2 / 3
            if($data->category == '2'){
                $time = date('Y-m-d h:i:sa', strtotime($data->created_at));

                $historycreated = [
                    'userid' => $user->id,
                    'name' => $user->name,
                    'time' => $time,
                ];

                array_push($historyArray,$historycreated);
            }
    
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

         

            $ordered = array();
            foreach ($historyArray as $event) {
            $ordered[$event['time']] = $event;
            
            }
            ksort($ordered);
            $finalsortarray = array();
            foreach($ordered as $history){
                
                $tempsort = [
                    'userid' => $history['userid'],
                    'name' => $history['name'],
                    'time' => $history['time'],
                ];

                array_push($finalsortarray,$tempsort);

            }

            $tempfile = $data->file;
            $dirfile = $env . 'document/'. $tempfile;            

            $tempArray = [
                'id' => $data->id,
                'category' => $category->name,
                'file' => $dirfile,
                'userid' => $data->userid,
                'user created' => $user->name,
                'status' => $data->status,
                'history' => $finalsortarray,
            ];
            array_push($finalArray,$tempArray);
         
        }

        $countarray = array();

        $count = count($finalArray);
        $countarray = [
            'count' => $count,
            'data' => $finalArray,
        ];
        return response()->json(['status'=>'success', 'value'=>$countarray]);
    }
}
