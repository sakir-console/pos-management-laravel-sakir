<?php

namespace App\Http\Controllers\Api\v1\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ItemCategoryController extends Controller
{
    //Add Item category
    public function addItemCategory(Request $request)
    {
        $vnd_id = $request->vendor_id;
        $rules = [
            'name' => 'string|required|regex:/^[\pL\s\-]+$/u|max:100'
        ];

        $response = $this->validateWithJSON($request->only('name'), $rules);

        if ($response === true) {

            try {
                if ($this->VendorCheck($vnd_id) === true) {
                    $newCategory = DB::table('item_categories')->insert([
                        "name" => $request->name,
                        "vendor_id" => $request->vendor_id
                    ]);

                    return $this->responseWithSuccess('category_added.', $newCategory);
                } else {
                    return $this->responseWithError('Your not allowed.');
                }
            } catch (\Exception $exception) {

                return $this->responseWithError($exception->getMessage());

            }

        } else {

            return $this->responseWithError('Validation failed', $response);
        }
        // Verification with user's own Profile.

    }

    //Add Item category
    public function viewItemCategory(Request $request)
    {
        $vnd_id = $request->vendor_id;
        $vnd_qr = $request->vendor_qr;

        // if($this->VendorCheck($vnd_id)===true) {
        try {
            $vndCategory = DB::table('item_categories')
                ->where('vendor_id', $vnd_id)
                ->orWhere('vendor_id', $this->VndId($vnd_qr))
                ->get();

            return $this->responseWithSuccess('categories.', $vndCategory);

        } catch (\Exception $exception) {

            return $this->responseWithError($exception->getMessage());

        }


        /*    }else{
                return $this->responseWithError('not_allowed');
            }*/

    }


    //Update Item category
    public function updateItemCategory(Request $request)
    {
        $vnd_id = $request->vendor_id;
        $rules = [
            'name' => 'string|required|regex:/^[\pL\s\-]+$/u|max:100'
        ];

        $response = $this->validateWithJSON($request->only('name'), $rules);

        if ($response === true) {

            try {
                if ($this->VendorCheck($vnd_id) === true) {
                    $updateCategory = DB::table('item_categories')
                        ->where('id', $request->cat_id)
                        ->where('vendor_id', $vnd_id)
                        ->update(["name" => $request->name]);

                    return $this->responseWithSuccess('category_updated.', $updateCategory);
                } else {
                    return $this->responseWithError('Your not allowed.');
                }
            } catch (\Exception $exception) {

                return $this->responseWithError($exception->getMessage());

            }

        } else {

            return $this->responseWithError('Validation failed', $response);
        }

    }

    //Remove Category--
    public function removeCategory(Request $request)
    {
        //vnd_id, cat_id
        try {
            if ($this->VendorCheck($request->vnd_id) === true) {
                $user = Auth::user();

                $cat = DB::table('item_categories')
                    ->where('vendor_id', $request->vnd_id)
                    ->where('id', $request->cat_id);

                if ($cat->exists()) {

                    $post_delete = $cat->delete();
                    return $this->responseWithSuccess('Category deleted');

                } else {
                    return $this->responseWithError('Invalid Category');
                }

            } else {
                return $this->responseWithError('Invalid Permission!');
            }

        } catch (\Exception $exception) {

            return $this->responseWithError($exception->getMessage());

        }
    }

}
