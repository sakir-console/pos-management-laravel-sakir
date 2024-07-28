<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Profile;
use App\Models\Vendor;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    // Validation Helper
    protected function validateWithJson($data = [], $rules = [])
    {
        $validator = Validator::make($data, $rules);

        if ($validator->passes()) {
            return true;
        }
        return $validator->getMessageBag()->all();
    }


    // Success Result
    protected function responseWithSuccess($message = '', $data = [], $display = [], $code = 200)
    {

        return response()->json([
            'result' => true,

            'message' => $message,
            'data' => $data ?? "no_data",
            'display' => $display,
        ], $code, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);

    }


    //Failure Result
    protected function responseWithError($message = '', $data = [], $code = 400)
    {
        return response()->json([
            'result' => false,
            'message' => $message,
            'data' => $data
        ], $code);

    }


    // Admin pass Helper
    protected function adminPass()
    {
        if (Auth::user()->tokenCan('role:sup_admin')) {
            return true;
        }
        return false;
    }


    // Vendor owner verification
    protected function VendorCheck($vnd_id)
    {
        if (Vendor::where('user_id', Auth::user()->id)->where('id', $vnd_id)->exists()) {
            return true;
        }
        return false;
    }

    // Vendor Item verification
    protected function ItemVerify($vnd_id, $item_id)
    {
        if ($this->VendorCheck($vnd_id) && Item::where('id', $item_id)->where('vendor_id', $vnd_id)->exists()) {
            return true;
        }
        return false;
    }


    protected function shipCheck($ship_id): bool
    {
        if (DB::table('users')
            ->where('id', Auth::user()->id)
            ->where('ship_id', $ship_id)->exists()) {
            return true;
        }
        return false;
    }


    // Vendor_id by QR
    protected function VndId($VndQr)
    {
        return DB::table('vendor_qrs')->where('vendor_qr', $VndQr)->value('vendor_id');
    }


    //#Data Search # Filter as Cat, subCat,Top--Sold,price
    protected function Filter($query, $request)
    {

        //---- Vendor wise
        if ($request->vnd) {
            $query->where('vendor_id', $request->vnd);
        }


        //----Search
        if ($request->s) {
            $query->where('title', 'LIKE', '%' . $request->s . '%');
        }

        //----Category wise
        if ($request->cat) {
            $cat = $request->cat;
            $query->whereHas('cats',
                function ($query) use ($request) {
                    $query->whereIn('item_category_id', [$request->cat]);
                });
        }


        $total = $query->count();
        $limit = $request->limit ?: 10;
        $page = $request->page && $request->page > 0 ? $request->page : 1;
        $offsetValue = ($page - 1) * $limit;
        $result = $query->
        //orderBy('mark','desc')->
        offset($offsetValue)->limit($limit)->get();

        $display = [
            "per_page" => $limit,
            "current_page" => $page,
            "last_page" => ceil($total / $limit),
            "total" => $total,
        ];
        return array('data' => $result, 'disp' => $display);
    }

    protected function FilterOrder($query, $request)
    {

        //---- Counter_code wise
        if ($request->cc) {
            $query->where('counter_code', $request->cc);
        }

        //---- vendor_id wise
        if ($request->vnd) {
            $query->where('vendor_id', $request->vnd);
        }

        //---- invoice wise
        if ($request->inv) {
            $query->where('invoice', $request->inv);
        }

        //---- user_id wise
        if ($request->uid) {
            $query->where('user_id', $request->uid);
        }

        //---- Order_id wise
        if ($request->order_id) {
            $query->where('id', $request->order_id);
        }


        //----Review wise
        if ($request->has('review') === true) {
            $query->whereHas('order_items',
                function ($query) use ($request) {
                    $query->whereIn('order_details.review_remain', [$request->review]);
                });
        }


        $total = $query->count();
        $limit = $request->limit ?: 10;
        $page = $request->page && $request->page > 0 ? $request->page : 1;
        $offsetValue = ($page - 1) * $limit;
        $result = $query->
        //orderBy('mark','desc')->
        offset($offsetValue)->limit($limit)->get();

        $display = [
            "per_page" => $limit,
            "current_page" => $page,
            "last_page" => ceil($total / $limit),
            "total" => $total,
        ];
        return array('data' => $result, 'disp' => $display);
    }

    protected function FilterReview($query, $request)
    {

        //---- User wise
        if ($request->uid) {
            $query->where('user_id', $request->uid);
        }

        //---- vendor_id wise
        if ($request->vnd) {
            $query->where('vendor_id', $request->vnd);
        }

        //---- invoice wise
        if ($request->order_id) {
            $query->where('order_id', $request->order_id);
        }

        //---- Item wise
        if ($request->item) {
            $query->where('item_id', $request->item);
        }

        //---- review_id wise
        if ($request->review_id) {
            $query->where('review_id', $request->review_id);
        }

        //---- Review_item wise
        if ($request->has('item_id') === true) {
            $query->whereHas('review_items',
                function ($query) use ($request) {
                    $query->whereIn('review_items.item_id', [$request->item_id]);
                });
        }


        $total = $query->count();
        $limit = $request->limit ?: 10;
        $page = $request->page && $request->page > 0 ? $request->page : 1;
        $offsetValue = ($page - 1) * $limit;
        $result = $query->
        //orderBy('mark','desc')->
        offset($offsetValue)->limit($limit)->get();

        $display = [
            "per_page" => $limit,
            "current_page" => $page,
            "last_page" => ceil($total / $limit),
            "total" => $total,
        ];
        return array('data' => $result, 'disp' => $display);
    }


    function unique_random($table, $col, $chars = 7)
    {

        $unique = false;

        // Store tested results in array to not test them again
        $tested = [];

        do {

            // Generate random string of characters
            $random = Str::random($chars);

            // Check if it's already testing
            // If so, don't query the database again
            if (in_array($random, $tested)) {
                continue;
            }

            // Check if it is unique in the database
            $count = DB::table($table)->where($col, '=', $random)->count();

            // Store the random character in the tested array
            // To keep track which ones are already tested
            $tested[] = $random;

            // String appears to be unique
            if ($count == 0) {
                // Set unique to true to break the loop
                $unique = true;
            }

            // If unique is still false at this point
            // it will just repeat all the steps until
            // it has generated a random string of characters

        } while (!$unique);


        return $random;
    }


    //Rough::::::Multiple Image Upload
    public function imgUploadmultiple($request)
    {

        $data = $request->all();
        $rules = [
            //'imageFile.*' => 'required',
            'imageFile.*' => 'required|mimes:jpeg,jpg,png,webp|max:2048'
        ];

        $response = $this->validateWithJSON($request->only('imageFile'), $rules);

        if ($response === true) {

            try {


                if ($request->hasFile('imageFile')) {
                    $info = [];
                    $files = $request->file('imageFile');
                    foreach ($files as $index => $file) {
                        $ext = $file->getClientOriginalExtension();//Display File Extension
                        $name = Str::random(14) . Str::limit($file->getClientOriginalName(), 1, '');  // File new Name
                        $file_name = $name . "." . $ext;  // Modified File Name
                        $success = $file->move(public_path('uploads/image/'), $file_name); //Move Uploaded File(Upload_dir, File_name)
                        $fileData[] = $file_name; // Files name array

                        $img_data = [
                            'product_id' => 6,
                            'img_name' => $fileData[$index],
                        ];

                        if ($success) {
                            DB::table('product_imgs')->insert($img_data);
                            $info['count'] = count($fileData);
                        }
                    }
                    return $this->responseWithSuccess($info['count'] . ' Image Uploaded Successfully ');

                } else {

                    return $this->responseWithError('Select Image first', $response);
                }


            } catch (\Exception $exception) {

                return $this->responseWithError($exception->getMessage());

            }

        } else {

            return $this->responseWithError('Validation failed', $response);
        }

    }

    //Image(single) Upload Helper
    protected function imageUpload($request, $ImageName, $uploadDir)
    {
        if ($request->hasFile($ImageName)) {
            $Image = $request->file($ImageName);
            $ext = Str::lower($Image->getClientOriginalExtension());//Display File Extension
            $name = Str::random(15) . Str::limit($Image->getClientOriginalName(), 1, '');  // File new Name
            $file_name = $name . "." . $ext;  // Modified File Name
            $success = $Image->move(public_path('uploads/images' . $uploadDir), $file_name); //Move Uploaded File(Upload_dir, File_name)

            if ($success) {
                return $file_name;
            } else {
                return false;
            }
        } else {
            return 'Select Image first';
        }

    }

     // Find loc wise vendors
    protected function FilterAll($query, $request)
    {


        // Check if latitude, longitude, and distance are provided in the request
        if ($request->has('ltt') && $request->has('lgt') && $request->has('distance')) {
            $latitude = $request->ltt;
            $longitude = $request->lgt;
            $distance = $request->distance;

            $haversine = "(6371 * acos(
                                cos(radians(" . $latitude . "))
                                * cos(radians(`ltt`))
                                * cos(radians(`lgt`) - radians(" . $longitude . "))
                                + sin(radians(" . $latitude . ")) * sin(radians(`ltt`))
                            )
                        )";

            $query->selectRaw("*")
                ->selectRaw("round($haversine, 2) AS distance")
                ->having("distance", "<=", $distance)
                ->orderBy("distance", "asc");
        }

        // Check if name, vendor category ID, division ID, and district ID are provided in the request
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->has('vendor_cat_id')) {
            $query->where('vendor_cat_id', $request->vendor_cat_id);
        }
        if ($request->has('division_id')) {
            $query->where('division_id', $request->division_id);
        }
        if ($request->has('district_id')) {
            $query->where('district_id', $request->district_id);
        }


        //----Review wise
        if ($request->has('review') === true) {
            $query->whereHas('order_items',
                function ($query) use ($request) {
                    $query->where('review_remain', $request->review);
                });
        }


        $total = $query->count();
        $limit = $request->limit ?: 10;
        $page = $request->page && $request->page > 0 ? $request->page : 1;
        $offsetValue = ($page - 1) * $limit;
        if ($request->has('trending')) {
            $query->orderBy('points','desc');
        }

        $result = $query->
        //orderBy('mark','desc')->
        offset($offsetValue)->limit($limit)->get();

        $display = [
            "per_page" => $limit,
            "current_page" => $page,
            "last_page" => ceil($total / $limit),
            "total" => $total,
        ];
        return array('data' => $result, 'disp' => $display);
    }



}
