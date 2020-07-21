<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(){
        return response()->json('user engine');
    }

    public function all(){

        $user = User::all();

        return response()->json($user);

    }

    public function add(Request $request){

        $validator = validator::make($request->all(),
			[
				'name' => 'required',
				'email' => 'required',
                'password' => 'required',
                'phone' => 'required',
                'role' => 'required',

			]);

		if ($validator->fails()) {

			return response()->json($validator->errors(), 422);

		} else {

            $name = $request->input('name');
            $email = $request->input('email');
            $password = $request->input('password');
            $phone = $request->input('phone');
            $role = $request->input('role');

            $userExist = User::where('email',$email)->orWhere('phone',$phone)->first();
            if($userExist){

                $message = 'email or phone is exist';
                return response()->json(['status' => 'failed', 'value'=> $message]);
                
            } else {

                $user = new User;
                $user->name = $name;
                $user->email = $email;
                $user->password = $password;
                $user->phone = $phone;
                $user->role = $role;

                $user->save();

                return response()->json(['status'=> 'success']);

            }

        }
    }

    public function profile(Request $request){

        $userid = $request->input('userid');

        $profile = User::find($userid);

        return response()->json(['status'=>'success', 'value'=>$profile]);

    }

    public function update(Request $request){

        $userid = $request->input('userid');
        $name = $request->input('name');
        $phone = $request->input('phone');
        $email = $request->input('email');
        $password = $request->input('password');
        $role = $request->input('role');

        $user = User::find($userid);

        if(is_null($name)){
            $name = $user->name;
        }
        if(is_null($phone)){
            $phone = $user->phone;
        }
        if(is_null($email)){
            $email = $user->email;
        }
        if(is_null($password)){
            $password = $user->password;
        }
        if(is_null($role)){
            $role = $user->role;
        }

        $user->name = $name;
        $user->phone = $phone;
        $user->email = $email;
        $user->password = $password;
        $user->role = $role;

        $user->save();

        return response()->json(['status'=>'success']);

    }

    public function destroy(Request $request) {
		$userid = $request->input('userid');
		$user = User::find($userid);
		$user->delete($user->userid);

		return response()->json(['status'=>'success']);

	}
}
