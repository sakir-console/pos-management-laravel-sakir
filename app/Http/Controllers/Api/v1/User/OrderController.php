<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //Add order from counter verify
    public function addOrder(Request $request)
    {
        try {

            $user = Auth::user();
            $counterCheck = DB::table('counters')->where('counter_code', $request->cc);
            $counter = DB::table('counters')->where('counter_code', $request->cc);

            //----------------------------------------------------------------
            $avg_review = DB::table('reviews')
                ->where('vendor_id', $counter->value('vendor_id'))
                ->where('created_at', '>=', now()->subMonths(6)) // Filter reviews from the last 6 months
                ->avg('rating');

            // Calculate total item count from order_details for the last 6 months
            $total_item_count = DB::table('order_details')
                ->where('vendor_id', $counter->value('vendor_id'))
                ->where('created_at', '>=', now()->subMonths(6))
                ->sum('item_count');

            // Calculate final score (example formula, adjust as needed)
            $final_score = ($avg_review * 0.7) + ($total_item_count * 0.3);

            DB::table('vendors')->where('id', $request->vnd)->update([
                "rating" => $avg_review
            ]);
            DB::table('vendors')->where('id', $request->vnd)->update([
                "points" => $final_score
            ]);

            //----------------------------------------------------------------


            if ($counterCheck->exists()) {

                $orderCheck = DB::table('orders')
                    ->where('counter_code', $request->cc)
                    ->where('user_id', $user->id);

                if (!$orderCheck->exists()) {

                    $newOrderID = DB::table('orders')->insertGetId([
                        "user_id" => $user->id,
                        "vendor_id" => $counter->value('vendor_id'),
                        "counter_code" => $request->cc,
                        "counter_id" => $counter->value('id'),
                        "invoice" => $counter->value('invoice')
                    ]);

                    $existed_counter = DB::table('counter_details')
                        ->where('item_id',  $request->item_id)
                        ->where('counter_id', $counter->value('id'))
                        ->where('qty',  $request->qty)
                        ->whereNot('remaining', 0);

                    if ($existed_counter) {

                                $counterItem = DB::table('counter_details')
                                    ->where('counter_id', $counter->value('id'))
                                    ->where('qty',  $request->qty)
                                    ->where('item_id', $request->item_id);


                                $counter_item = [
                                    'item_id' => $request->item_id,
                                    "order_id" => $newOrderID,
                                    "vendor_id" => $counter->value('vendor_id'),
                                    'qty' => $counterItem->value('qty'),
                                    'price' => $counterItem->value('price'),
                                    "item_count" => $request->item_count > $counterItem->value('remaining') ? $counterItem->value('remaining') :$request->item_count,
                                    "review_remain" => 1
                                ];
                                if ($request->item_count != 0) {
                                    DB::table('order_details')->insert($counter_item);

                                    DB::table('counter_details')
                                        ->where('counter_id', $counter->value('id'))
                                        ->where('qty', $request->qty)
                                        ->where('item_id', $request->item_id)->decrement('remaining', $request->item_count > $counterItem->value('remaining') ? $counterItem->value('remaining') : $request->item_count);

                                }

                    } else {

                        return "invalid_items_in_counterX.";
                    }
                    return $this->responseWithSuccess('order_added');

///ei theke check koro..
                } else {

                    $existed_counter = DB::table('counter_details')
                        ->where('item_id', $request->item_id)
                        ->where('counter_id', $counter->value('id'))
                        ->where('qty', $request->qty)
                        ->whereNot('remaining', 0)
                        ->count();

                    if ($existed_counter) {

                                $order = DB::table('orders')
                                    ->where('counter_code', $request->cc)
                                    ->where('user_id', $user->id);

                                $itemExist = DB::table('order_details')
                                    ->where('order_id', $order->value('id'))
                                    ->where('item_id', $request->item_id)
                                    ->where('qty',$request->qty);

                                if (!$itemExist->exists()) {
                                    $counterItem = DB::table('counter_details')
                                        ->where('qty', $request->qty)
                                        ->where('counter_id', $counter->value('id'))
                                        ->where('item_id', $request->item_id);

                                    $counter_items = [
                                        'item_id' => $request->item_id,
                                        "order_id" => $order->value('id'),
                                        "vendor_id" => $counter->value('vendor_id'),
                                        'qty' => $counterItem->value('qty'),
                                        'price' => $counterItem->value('price'),
                                        "item_count" => $request->item_count > $counterItem->value('remaining') ? $counterItem->value('remaining') : $request->item_count,
                                        "review_remain" => 1
                                    ];
                                    if ($request->item_count != 0) {

                                        DB::table('order_details')->insert($counter_items);

                                        DB::table('counter_details')
                                            ->where('counter_id', $counter->value('id'))
                                            ->where('qty', $request->qty)
                                            ->where('item_id',$request->item_id)->decrement('remaining', $request->item_count > $counterItem->value('remaining') ? $counterItem->value('remaining') : $request->item_count);

                                    }

                                } else {

                                    $cntrItem = DB::table('counter_details')
                                        ->where('counter_id', $counter->value('id'))
                                        ->where('qty',$request->qty)
                                        ->where('item_id', $request->item_id);


                                    DB::table('order_details')
                                        ->where('order_id', $order->value('id'))
                                        ->where('item_id', $request->item_id)
                                        ->increment('item_count', $request->item_count > $cntrItem->value('remaining') ? $cntrItem->value('remaining') : $request->item_count);


                                    DB::table('counter_details')
                                        ->where('counter_id', $counter->value('id'))
                                        ->where('qty', $request->qty)
                                        ->where('item_id', $request->item_id)->decrement('remaining', $request->item_count > $cntrItem->value('remaining') ? $cntrItem->value('remaining') : $request->item_count);


                                }



                            return $this->responseWithSuccess('order_added');


                    } else {
                        return $this->responseWithError('invalid_items_in_counterY');
                    }
                    return $this->responseWithSuccess('order_added');

                }


            } else {

                return $this->responseWithError('invalid_counter_code');
            }


        } catch (\Exception $exception) {

            return $this->responseWithError($exception->getMessage());

        }


    }

    //Show My Order list::
    public function viewMyOrder(Request $request)
    {

        try {
            // ?review=1 for remaind review
            $user = Auth::user();
            $query = Order::with('vendor.division', 'vendor.district', 'order_items.item.imgs')
                ->where('user_id', $user->id);

            $view = $this->FilterOrder($query, $request);

            $orders = $view['data']->map(function ($order) {
                $totalPrice = 0;
                foreach ($order->order_items as $item) {
                    $totalPrice += (float)$item->price * $item->item_count;
                }
                $order->total_price = $totalPrice;
                // $order->vendor_name = $order->vendor->name;
                return $order;
            });

            return $this->responseWithSuccess('user_order_item_showed', $orders, $view['disp']);
        } catch (\Exception $exception) {
            return $this->responseWithError($exception->getMessage());
        }
    }


}
