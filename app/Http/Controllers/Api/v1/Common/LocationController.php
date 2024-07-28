<?php

namespace App\Http\Controllers\Api\v1\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    // View Divisions
    public function viewDivisions()
    {

        try{
            $view =DB::table('divisions')->get();
            return $this->responseWithSuccess('Division list',$view);

        } catch(\Exception $exception){

            return $this->responseWithError($exception->getMessage());

        }


    }

    // View Divisions
    public function viewDistricts(Request $request)
    {

        try{
            $view =DB::table('districts')->where('division_id',$request->div_id)->get();
            return $this->responseWithSuccess('Districts',$view);
        } catch(\Exception $exception){

            return $this->responseWithError($exception->getMessage());

        }


    }

    // View Upazilas
    public function viewUpazilas(Request $request)
    {
        try{
            $view =DB::table('upazilas')->where('district_id',$request->dis_id)->get();
            return $this->responseWithSuccess('Upazilas',$view);
        } catch(\Exception $exception){

            return $this->responseWithError($exception->getMessage());

        }


    }

    // View Unions
    public function viewUnions(Request $request)
    {
        try{
            $view =DB::table('unions')->where('upazila_id',$request->upa_id)->get();
            return $this->responseWithSuccess('Union list',$view);
        } catch(\Exception $exception){

            return $this->responseWithError($exception->getMessage());

        }


    }

}
