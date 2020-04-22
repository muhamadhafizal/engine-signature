<?php

namespace App\Http\Controllers;

use App\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SignatureController extends Controller
{
    public function index(){
        return response()->json('signature engine');
    }

    public function store(Request $request){

        $validator = validator::make($request->all(),
        [
            'userid' => 'required',
            'signaturefile' => 'required|mimes:doc,docx,pdf',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        } else {

            $userid = $request->input('userid');
            $signaturefile = $request->file('signaturefile');

            $extenstion = $signaturefile->getClientOriginalExtension();
            $filename = rand(11111, 99999) . '.' .$extenstion;
            $destinationPath = 'signaturefile';

            $signaturefile->move($destinationPath, $filename);

            $signature = new Signature;
            $signature->userid = $userid;
            $signature->file = $filename;

            $signature->save();

            return response()->json(['status'=>'success']);

        }

    }

    public function usersig(Request $request){
        
        $env = 'http://engine-signature.test/';

        $finalArray = array();
        $userid = $request->input('userid');
        $sig = Signature::where('userid',$userid)->get();

        foreach($sig as $data){

            $tempfile = $data->file;
            $dirfile = $env . 'signaturefile/'. $tempfile;

            $tempArray = [

                'id' => $data->id,
                'signature' => $dirfile,

            ];
            array_push($finalArray,$tempArray);
        }

        return response()->json(['status'=>'success', 'value'=>$finalArray]);

    }

    public function destroy(Request $request) {
		$signatureid = $request->input('signatureid');
		$signature = Signature::find($signatureid);
		$signature->delete($signature->id);

		return response()->json(['statys'=>'success']);

	}
}
