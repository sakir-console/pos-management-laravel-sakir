<?php

namespace App\Http\Controllers\Api\v1\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    //

    public function createVendor(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'vendor_name' => 'string|required',
            'vendor_email' => 'string|email|unique:vendors,email',
            'phone' => 'string|required|min:6|unique:vendors,phone',
            'div_id' => 'string|required',
            'dis_id' => 'string|required',
            'upa_id' => 'string|required',
            'lgt' => 'between:-180,180',
            'ltt' => 'between:-90,90'
        ];

        $response = $this->validateWithJSON($request->all(), $rules);
        if ($response === true) {

            try {

                $createPro = DB::table('vendors')->insert([
                    "name" => $request->vendor_name,
                    "user_id" => $user->id,
                    "email" => $request->vendor_email,
                    "phone" => $request->phone,
                    "logo" => $request->logo,
                    "cover" => $request->cover,
                    "lgt" => $request->lgt,
                    "ltt" => $request->ltt,
                    "address" => $request->address,
                    "bio" => $request->bio,
                    "vendor_cat_id" => $request->vnd_cat_id,
                    "vendor_subcat_id" => $request->vnd_subcat_id,
                    "facebook" => $request->facebook,
                    "instagram" => $request->instagram,
                    "division_id" => $request->div_id,
                    "district_id" => $request->dis_id,
                    "upazila_id" => $request->upa_id
                ]);

                return $this->responseWithSuccess('Successfully Added.');

            } catch (\Exception $exception) {

                return $this->responseWithError($exception->getMessage());

            }
        }

        return $this->responseWithError('Validation failed', $response);

    }

    // My vendor list....
    public function viewVendorList(Request $request)
    {

        try {
            $user = Auth::user();

            $view = DB::table('vendors')->where('user_id', $user->id)->get();

            return $this->responseWithSuccess('Successfully fetched.', $view);

        } catch (\Exception $exception) {

            return $this->responseWithError($exception->getMessage());

        }


    }

    //view Vendor Profile..
    public function viewVendorProfile(Request $request)
    {
        $vnd_id = $request->vendor_id;

        if ($this->VendorCheck($vnd_id) === true) {
            try {
                $query = Vendor::with('vendor_category', 'division', 'district')->where('id', $vnd_id)->get();

                $vndProfile = $query->map(function ($view) {
                    return $view;
                })->first();
                return $this->responseWithSuccess('vendor_profile.', $vndProfile);

            } catch (\Exception $exception) {

                return $this->responseWithError($exception->getMessage());

            }
        } else {
            return $this->responseWithError('not_allowed');
        }


    }

    //Vendor QR::
    public function vendorQR(Request $request)
    {
        $vnd_id = $request->vnd_id;

        if ($this->VendorCheck($vnd_id) === true) {
            try {

                $VendorQR = DB::table('vendor_qrs')->where('vendor_id', $vnd_id)->exists();

                if (!$VendorQR) {
                    $addQR = DB::table('vendor_qrs')->insert([
                        "vendor_id" => $vnd_id,
                        "vendor_qr" => $this->unique_random('vendor_qrs', 'vendor_qr', 12)
                    ]);
                }

                $viewVendorQR = DB::table('vendor_qrs')->where('vendor_id', $vnd_id)->first();

                return $this->responseWithSuccess('vendor_qr.', $viewVendorQR);
            } catch (\Exception $exception) {
                return $this->responseWithError($exception->getMessage());
            }
        } else {
            return $this->responseWithError('Your not allowed.');
        }

    }

    //view Vendor Rank..
    public function viewVendorRank(Request $request)
    {
        $vnd_id = $request->vnd;


        $vendor = DB::table('vendors')->where('id', $request->vnd);

        $rating = $vendor->value('rating');
        $total_item = DB::table('items')->where('vendor_id', $request->vnd)->count();
        //Vendor related ids..
        $vendor_cat_id = $vendor->value('vendor_cat_id');
        $country_id = $vendor->value('country_id');
        $division_id = $vendor->value('division_id');
        $district_id = $vendor->value('district_id');
        $upazila_id = $vendor->value('upazila_id');

        try {

            $countryRank = "SELECT * FROM (SELECT *, RANK() OVER (PARTITION BY t.status,t.vendor_cat_id,t.country_id ORDER BY t.rating DESC)
                    AS countryRank
                    FROM (SELECT * FROM vendors) AS t) AS rt WHERE
                    rt.id =$vnd_id";

            $divisionRank = "SELECT * FROM (SELECT *, RANK() OVER (PARTITION BY t.status,t.vendor_cat_id,t.country_id,t.division_id ORDER BY t.rating DESC)
                    AS divisionRank
                    FROM (SELECT * FROM vendors) AS t) AS rt WHERE
                    rt.id =$vnd_id";

            $districtRank = "SELECT * FROM (SELECT *, RANK() OVER (PARTITION BY t.status,t.vendor_cat_id,t.country_id,t.division_id,t.district_id ORDER BY t.rating DESC)
                    AS districtRank
                    FROM (SELECT * FROM vendors) AS t) AS rt WHERE
                    rt.id =$vnd_id";

            $upazilaRank= "SELECT * FROM (SELECT *, RANK() OVER (PARTITION BY t.status,t.vendor_cat_id,t.country_id,t.division_id,t.district_id,t.upazila_id ORDER BY t.rating DESC)
                    AS upazilaRank
                    FROM (SELECT * FROM vendors) AS t) AS rt WHERE
                    rt.id =$vnd_id";

            $result['rating'] = $rating;
            $result['total_item'] = $total_item;
            $result['country_rank'] = DB::select($countryRank)[0]->countryRank;
            $result['division_rank'] = DB::select($divisionRank)[0]->divisionRank;
            $result['district_rank'] = DB::select($districtRank)[0]->districtRank;
            $result['upazila_rank'] = DB::select($upazilaRank)[0]->upazilaRank;

            return $this->responseWithSuccess('vendor_rank', $result);

        } catch (\Exception $exception) {

            return $this->responseWithError($exception->getMessage());

        }


    }


    public function test(Request $request)
    {

        try {

            $user_id = $request->id;
            $vnd = $request->vnd;

            $sql1 = "Select *, (Select count(id) from vendors where rating > s.rating ) As rank from vendors as s
WHERE
    s.user_id = $user_id AND
    s.division_id = '2'
";

            $sql = "SELECT
    *
FROM (SELECT *, RANK() OVER (PARTITION BY t.division_id ORDER BY t.rating DESC) AS globalRank
  FROM (SELECT * FROM vendors) AS t) AS rt
WHERE
    rt.user_id = $user_id AND
    rt.division_id = '2'
     LIMIT 5;
";
            $result = DB::select($sql1);

            $avg = DB::table('reviews')->where('vendor_id', $vnd)->avg('rating');

            if ($result) {
                return $this->responseWithSuccess('Successfully fetched.', $avg);
            }
            return $this->responseWithSuccess('Invalid data', $result);
        } catch (\Exception $exception) {

            return $this->responseWithError('Something went wrong!', $exception->getMessage());

        }


    }


    // ----------------------------------------------------------------
    //vendor Search
    //vendor Search
    //vendor Search
//vendor Search
    public function vendorSearch(Request $request)
    {
        try {
            $user = Auth::user();

            $query = Vendor::with('vendor_category');

            $view = $this->FilterAll($query, $request);

            $vendors = $view['data']->map(function ($view) {
                return $view;
            });


            if ($vendors->isEmpty()) {
                return $this->responseWithError('No vendors found within the specified criteria.');
            }
            return $this->responseWithSuccess('Successfully fetched', $vendors, $view['disp']);


        } catch (\Exception $exception) {
            return $this->responseWithError($exception->getMessage());
        }
    }


}



/*
$countryRank = "SELECT * FROM (SELECT *, RANK() OVER (ORDER BY t.rating DESC)
                    AS countryRank
                    FROM (SELECT * FROM vendors) AS t) AS rt WHERE
                    rt.status = '1' AND
                    rt.country_id = $country_id AND
                    rt.vendor_cat_id = $vendor_cat_id AND
                    rt.id =$vnd_id";

$divisionRank = "SELECT * FROM (SELECT *, RANK() OVER (ORDER BY t.rating DESC)
                    AS divisionRank
                    FROM (SELECT * FROM vendors) AS t) AS rt WHERE
                    rt.status = '1' AND
                    rt.country_id = $country_id AND
                    rt.division_id = $division_id AND
                    rt.vendor_cat_id = $vendor_cat_id AND
                    rt.id =$vnd_id";

$districtRank = "SELECT * FROM (SELECT *, RANK() OVER (ORDER BY t.rating DESC)
                    AS districtRank
                    FROM (SELECT * FROM vendors) AS t) AS rt WHERE
                    rt.status = '1' AND
                    rt.country_id = $country_id AND
                    rt.division_id = $division_id AND
                    rt.district_id = $district_id AND
                    rt.vendor_cat_id = $vendor_cat_id AND
                    rt.id =$vnd_id";
$upazilaRank
    = "SELECT * FROM (SELECT *, RANK() OVER (ORDER BY t.rating DESC)
                    AS upazilaRank
                    FROM (SELECT * FROM vendors) AS t) AS rt WHERE
                    rt.status = '1' AND
                    rt.country_id = $country_id AND
                    rt.division_id = $division_id AND
                    rt.district_id = $district_id AND
                    rt.upazila_id = $upazila_id AND
                    rt.vendor_cat_id = $vendor_cat_id AND
                    rt.id =$vnd_id";

$result['country_rank'] = DB::select($countryRank)[0]->countryRank;
$result['division_rank'] = DB::select($divisionRank)[0]->divisionRank;
$result['district_rank'] = DB::select($districtRank)[0]->districtRank;
//$result['upazila_rank'] = DB::select($upazilaRank)[0]->upazilaRank;*/
