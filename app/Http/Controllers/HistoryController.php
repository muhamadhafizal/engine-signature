<?php

namespace App\Http\Controllers;
use App\History;
use Illuminate\Http\Request;
use DB;

class HistoryController extends Controller
{
    public function index(){
        return response()->json(['status'=>'success','view'=>'history engine']);
    }

    public function info(Request $request){

        $docid = $request->input('docid');
        $temparray = array();
        $finalarray = array();

        $document = DB::table('histories')
                ->join('users','users.id','=','histories.userid')
                ->select('histories.*','users.name as username')
                ->where('histories.docid',$docid)
                ->get();
        
        $oridoc = DB::table('documents')
                ->join('users','users.id','=','documents.userid')
                ->select('documents.created_at as time','users.name as username')
                ->where('documents.id',$docid)
                ->first();
        
        if($document){

            $temparray = [
                'message' => 'created by '. $oridoc->username. ', '. $oridoc->time,
            ];
    
            array_push($finalarray,$temparray);
    
            foreach($document as $data){
    
                $tarray = [
                    'message' => $data->status. ' by ' .$data->username. ', ' .$data->created_at,
                ];
    
                array_push($finalarray,$tarray);
    
            }
    
            return response()->json(['status'=>'success','value'=>$finalarray]);

        } else {

            return response()->json(['status'=>'failed','value'=>'sorry document not exist']);

        }
        

    }
}

