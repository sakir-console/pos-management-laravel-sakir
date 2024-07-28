<?php

namespace App\Http\Controllers\Api\v1\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CounterController extends Controller
{
    public function addCounter(Request $request)
    {


        if($this->VendorCheck($request->vendor_id)===true) {
            try {

                if (count($request->items) > 0) {

                    $lastCounter = Counter::orderBy('id', 'desc')->first();
                    if (isset($lastCounter)) {
                        // Sum 1 + last id
                        $counterInvoice = 'BI-0000' . ($lastCounter->id + 1) . '-' . date('Y');
                    } else {
                        $counterInvoice = 'BI-00001-' . date('Y');
                    }

                    $insertedCounterID = DB::table('counters')->insertGetId([
                        'counter_code' => $this->unique_random('counters','counter_code',6),
                        'invoice' => $counterInvoice,
                        'vendor_id' => $request->vendor_id,
                        'total_price' => 0, // Initialize total price to 0
                    ]);

                    $totalPrice = 0; // Initialize total price variable

                    if ($insertedCounterID) {
                        foreach ($request->items as $catId => $value) {
                            $totalPrice += $value["price"] * $value["qty"]; // Add each item's price * count to total price
                            DB::table('counter_details')->insert([
                                "counter_id" => $insertedCounterID,
                                "item_id" => $value["id"],
                                "price" => $value["price"],
                                "qty" => $value["size"],
                                "item_count" => $value["qty"],
                                "remaining" => $value["qty"]
                            ]);
                        }

                        // Update the total price in the counters table
                        DB::table('counters')
                            ->where('id', $insertedCounterID)
                            ->update(['total_price' => $totalPrice]);
                    }

                }

                // Fetch the newly inserted counter details
                $counterDetails = DB::table('counters')
                    ->select('counter_code', 'invoice', 'vendor_id', 'total_price')
                    ->where('id', $insertedCounterID)
                    ->first();

                return $this->responseWithSuccess('counter_added.', $counterDetails);

            } catch (\Exception $exception) {

                return $this->responseWithError($exception->getMessage());

            }
        }else{
            return $this->responseWithError('Your not allowed.');
        }
    }

    //Vendor Item
    public function viewCounter(Request $request) {
        $counter_code = $request->cc;

        try {
            $counterCheck = DB::table('counters')->where('counter_code', $request->cc);
            $counter = DB::table('counters')->where('counter_code', $request->cc);

            if ($counterCheck->exists()) {
                $query = Counter::with('counter_items.item.imgs', 'vendor.district')->where('counter_code', $counter_code)->first();

                $counter_data = [
                    "id" => $query->id,
                    "counter_code" => $query->counter_code,
                    "invoice" => $query->invoice,
                    "vendor_id" => $query->vendor_id,
                    "vendor_dis" => $query->vendor->district->name,
                    "vendor_name" => $query->vendor->name,
                    "total_price" => $query->total_price,
                    "counter_items" => $query->counter_items->map(function ($item) {
                        $itemImages = $item->item->imgs->pluck('image')->toArray();
                        return [
                            "id" => $item->id,
                            "counter_id" => $item->counter_id,
                            "item_id" => $item->item_id,
                            "price" => $item->price,
                            "qty" => $item->qty,
                            "item_count" => $item->item_count,
                            "remaining" => $item->remaining,
                            "title" => $item->item->title,
                            "imgs" => $itemImages
                        ];
                    })
                ];

                return $this->responseWithSuccess('counter_item', $counter_data);
            } else {
                return $this->responseWithError('invalid_counter_code');
            }
        } catch (\Exception $exception) {
            return $this->responseWithError($exception->getMessage());
        }
    }

}
