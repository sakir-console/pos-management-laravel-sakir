<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{


    public function signUp(Request $request)
    {
        $rules = [
            'name' => 'string|required',
            'email' => 'string|required|email|unique:users,email',
            'password' => 'string|required|min:6'
        ];

        $response = $this->validateWithJSON($request->all(), $rules);
        $data = [];


        if ($response===true){

            try{

                $user = new User;
                $user->name = $request->name;
                $user->email = $request->email;
                $user->password = bcrypt($request->password);
                $user->save();

                $token = $user->createToken('Token',['role:gen_cus'])->plainTextToken;
                $data['message'] = 'Successfully Created.';
                $data['token'] = $token;
                $data['inputed'] = $request->all();


                return $this->responseWithSuccess('Registration successful',$data);


            } catch(\Exception $exception) {

                return $this->responseWithError($exception->getMessage());
            }
        }

        return $this->responseWithError('Validation failed',$response);

    }


    public function signIn(Request $request)
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
                    'password' => $request->password
                ])) {

                    $user = Auth::user();
                    $token = $user->createToken('Token',['role:gen_cus'])->plainTextToken;

                    $data['user'] = $user;
                    $data['token'] = $token;
                    return $this->responseWithSuccess('Login successful',$data);
                } else {

                    return $this->responseWithError('Invalid Credentials');

                }



            } catch(\Exception $exception) {

                return $this->responseWithError($exception->getMessage());
            }
        }

        return $this->responseWithError('Validation failed',$response);
    }



    public function logOut()
    {

        try{

            if (Auth::user()) {
                $user = Auth::user();
                $data['user'] = $user;

                //Only for this device
                Auth::user()->currentAccessToken()->delete();

                // For all device
                // Auth::user()->tokens()->delete();


                return $this->responseWithSuccess('Logged Out.',$data);
            }

        } catch(\Exception $exception) {

            return $this->responseWithError($exception->getMessage());
        }
    }


    public function pages()
    {

        $users = DB::table('users')->get();
        $new= User::all();
        $user = Auth::user();
        $data['user']= $user;
        $data['user_id']=$user->id;
        $data['token_info']=$users;
        $data['info']=$new;
        return response()->json($data, 200);
    }



    public function hmm()
    {


    }

}

