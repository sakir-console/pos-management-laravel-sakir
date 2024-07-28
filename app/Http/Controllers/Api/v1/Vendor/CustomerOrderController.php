<?php

namespace App\Http\Controllers\Api\v1\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerOrderController extends Controller
{
    //Vendors Orders
    public function viewOrder(Request $request): JsonResponse
    {

        try {

            $user = Auth::user();
            $query = Order::with('user','order_items.item.imgs')
                ->where('vendor_id', $request->vnd_id);

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

            return $this->responseWithSuccess('customer_order_item_showed', $orders, $view['disp']);
        } catch (\Exception $exception) {
            return $this->responseWithError($exception->getMessage());
        }
    }

}
