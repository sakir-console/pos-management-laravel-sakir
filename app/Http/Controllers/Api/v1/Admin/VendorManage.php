<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorManage extends Controller
{
    //Add Vendor category
    public function addVendorCategory(Request $request)
    {

        $rules = [
            'name' => 'string|required|regex:/^[\pL\s\-]+$/u|max:100|unique:vendor_cats,name'
        ];

        $response = $this->validateWithJSON($request->only('name'), $rules);

        if ($response===true ){

            if($this->adminPass()) {
                try {
                    $newCategory = DB::table('vendor_cats')->insert([
                        "name" => $request->name
                    ]);

                    return $this->responseWithSuccess('Vendor category added successfully.');

                } catch (\Exception $exception) {

                    return $this->responseWithError($exception->getMessage());

                }
            }else{
                return $this->responseWithError('Your not allowed.');
            }
        }else {

            return $this->responseWithError('Validation failed', $response);
        }

    }
    //Add Vendor sub category
    public function addVendorSubCategory(Request $request)
    {

        $rules = [
            'name' => 'string|required|regex:/^[\pL\s\-]+$/u|max:100|unique:vendor_subcats,name',
            'cid' =>'required'
        ];

        $response = $this->validateWithJSON($request->only('name','cid'), $rules);

        if ($response===true ){

            if($this->adminPass()) {
                try {
                    $newCategory = DB::table('vendor_subcats')->insert([
                        "vendor_cat_id" => $request->cid,
                        "name" => $request->name
                    ]);

                    return $this->responseWithSuccess('Vendor sub category added successfully.');

                } catch (\Exception $exception) {

                    return $this->responseWithError($exception->getMessage());

                }
            }else{
                return $this->responseWithError('Your not allowed.');
            }
        }else {

            return $this->responseWithError('Validation failed', $response);
        }

    }
    // View vendor category
    public function viewVendorCategory(Request $request)
    {

        try{
            $view =DB::table('vendor_cats')->get();
            return $this->responseWithSuccess('Successfully fetched.',$view);

        } catch(\Exception $exception){

            return $this->responseWithError($exception->getMessage());

        }


    }

    // View vendor sub category
    public function viewVendorSubCategory(Request $request)
    {

        try{
            $view =DB::table('vendor_subcats')->where('vendor_cat_id',$request->id)->get();
            return $this->responseWithSuccess('Successfully fetched.',$view);

        } catch(\Exception $exception){

            return $this->responseWithError($exception->getMessage());

        }


    }



    public function viewVendorPending(Request $request)
    {
        if($this->adminPass()) {
        try{
            $view =DB::table('vendors')->where('status',0)->get();

            return $this->responseWithSuccess('Successfully fetched.',$view);

        } catch(\Exception $exception){

            return $this->responseWithError($exception->getMessage());

        }
        }else{
            return $this->responseWithError('Opps! Aceess denied!');
        }

    }


    public function vendorAction(Request $request)
    {
        if($this->adminPass()) {
        try{
            $view =DB::table('vendors')->where('id',$request->id)->update([
                'status' => $request->do,
            ]);
            return $this->responseWithSuccess('Updated.',$view);

        } catch(\Exception $exception){

            return $this->responseWithError($exception->getMessage());

        }
        }else{
            return $this->responseWithError('Opps! Aceess denied!');
        }

    }



    public function viewVendors(Request $request)
    {
        if($this->adminPass()) {
            try{
                $view =DB::table('vendors')->where('status',1)->orWhere('status',2)->get();

                return $this->responseWithSuccess('Successfully fetched.',$view);

            } catch(\Exception $exception){

                return $this->responseWithError($exception->getMessage());

            }
        }else{
            return $this->responseWithError('Opps! Aceess denied!');
        }

    }

}
