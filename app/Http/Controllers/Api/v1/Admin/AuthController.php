<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function AdminLogin(Request $request)
    {

        $rules = [
            'email' => 'string|required',
            'password' => 'string|required|min:6'
        ];

        $response = $this->validateWithJSON($request->all(), $rules);
        $data = [];


        if ($response===true){

            try{
                if (Auth::attempt([
                    'email' => $request->email,
                    'password' => $request->password,
                    'id' =>[2,3]

                ])) {

                    $user = Auth::user();
                    $token = $user->createToken('Token',['role:sup_admin'])->plainTextToken;

                    $data['user'] = $user;
                    $data['token'] = $token;
                    return $this->responseWithSuccess('Admin Login successful',$data);
                } else {
                    return $this->responseWithError('Invalid Admin');
                }



            } catch(\Exception $exception) {

                return $this->responseWithError($exception->getMessage());
            }
        }

        return $this->responseWithError('Validation failed',$response);
    }


    public function controlpanel(Request $request)
    {
        if ($this->adminPass()) {
            $user = Auth::user();
            $data['user']=$user;
            $data['user_id']= $user->id;
            return $this->responseWithSuccess('Admin Control Panel','hmm');

        }

        return'\n hmm';
    }

}
