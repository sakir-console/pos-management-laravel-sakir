<?php

namespace App\Http\Controllers\Api\v1\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\items;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ItemController extends Controller
{


    //Add Item
    public function addItem(Request $request)
    {
        $vnd_id = $request->vendor_id;
        $data = $request->all();
        $rules = [
            'item_name' => 'string|required|regex:/^[\pL\s\-]+$/u|max:100',
            'about' => 'string|max:350',
            'ingrs' => 'string|max:400'
        ];

        $response = $this->validateWithJSON($request->only('item_name', 'about', 'ingrs'), $rules);

        if ($response === true) {

            if ($this->VendorCheck($vnd_id) === true) {
                try {


                    $insertedItemID = DB::table('items')->insertGetId([
                        'title' => $request->item_name,
                        'vendor_id' => $request->vendor_id,
                        'description' => $request->about,
                        'ingredients' => $request->ingrs
                    ]);
                    if (count($data['cat_id']) > 0) {
                        foreach ($data['cat_id'] as $catId => $value) {
                            $item_cat_data = [
                                'item_id' => $insertedItemID,
                                'item_category_id' => $data['cat_id'][$catId],
                            ];
                            DB::table('item_category_item')->insert($item_cat_data);
                        }
                    }

                    /*        if(count($data['img'])>0){
                                foreach ($data['img'] as $img => $value) {
                                    $img_data=[
                                        'item_id'=>$insertedItemID,
                                        'image'  =>$data['img'][$img]
                                    ];
                                    DB::table('item_images')->insert($img_data);
                                }
                            }*/
                    $images = $request->file('img');
                    if ($request->hasFile('img')) {

                        foreach ($images as $index => $image) {

                            $ext = Str::lower($image->getClientOriginalExtension());//Display File Extension
                            $name = Str::random(14) . Str::limit($image->getClientOriginalName(), 1, '');  // File new Name
                            $file_name = $name . "." . $ext;  // Modified File Name
                            $success = $image->move(public_path('uploads/images/vendors/' . $request->vendor_id . '/items/'), $file_name); //Move Uploaded File(Upload_dir, File_name)
                            $fileData[] = $file_name; // Files name array


                            $img_data = [
                                'item_id' => $insertedItemID,
                                'image' => $fileData[$index],
                            ];
                            if ($success) {
                                DB::table('item_images')->insert($img_data);
                                $info = count($fileData) . ' Product Image uploaded.';
                            }

                        }
                    }

                    if (count($data['qtys']) > 0) {
                        foreach ($data['qtys'] as $qty => $value) {
                            $qntt_data = [
                                'item_id' => $insertedItemID,
                                'qty' => $data['qtys'][$qty],
                                'price' => $data['price'][$qty],
                            ];
                            DB::table('item_quantities')->insert($qntt_data);
                        }
                    }

                    return $this->responseWithSuccess('Item added Successfully.');

                } catch (\Exception $exception) {

                    return $this->responseWithError($exception->getMessage());

                }
            } else {
                return $this->responseWithError('Your not allowed.');
            }
        } else {

            return $this->responseWithError('Validation failed', $response);
        }

    }


    //Vendor Item catwise only
    public function viewItems(Request $request)
    {
        $vnd_id = $request->vnd;
        $cat_id = $request->cat;
        $vnd_qr = $request->vendor_qr;

        try {
            $query = Item::with('cats', 'imgs', 'qty', 'vendor')
                ->whereHas('cats', function ($query) use ($cat_id) {
                    $query->whereIn('item_category_id', [$cat_id]);
                })->where('vendor_id', $vnd_id)
                ->orWhere('vendor_id', $this->VndId($vnd_qr))
                ->get();


            $items = $query->map(function ($view) {
                return [
                    "id" => $view->id,
                    "title" => $view->title,
                    "rating" => $view->rating,
                    'discount' => $view->qty->map(function ($details) {
                            return [
                                'discount_%' => $details->dcnt_status == 2 ? $details->discount : ($details->dcnt_status == 1 ? ($details->discount * 100 / $details->price) : null),
                                'discount_price' => $details->dcnt_status == 1 ? ($details->price - $details->discount) : ($details->dcnt_status == 2 ? ($details->price - ($details->price * $details->discount / 100)) : null),
                            ];
                        })->max('discount_%') . "%",

                    'price_max' => $view->qty->map(function ($details) {
                        return ['price' => $details->price];
                    })->max('price'),
                    'price_min' => $view->qty->map(function ($details) {
                        return ['price' => $details->price];
                    })->min('price'),

                    'price_discounted' => $view->qty->map(function ($details) {
                        return ['discount_price' => $details->dcnt_status == 1 ? ($details->price - $details->discount) : ($details->dcnt_status == 2 ? ($details->price - ($details->price * $details->discount / 100)) : null)
                        ];
                    })->min('discount_price'),

                    "sold" => $view->sold,
                    "description" => $view->description,
                    "ingredients" => $view->ingredients,
                    "created_at" => $view->created_at,
                    "updated_at" => $view->updated_at,
                    "cats" => $view->cats,
                    'qtys' => $view->qty->map(function ($details) {
                        return [
                            'qty' => $details->qty,
                            'price' => $details->price,
                            'dcnt_status' => $details->dcnt_status !== 0,
                            'discount_tk' => $details->dcnt_status === 1 ? $details->discount : ($details->dcnt_status == 2 ? ($details->price * $details->discount / 100) : null),
                            'discount_%' => $details->dcnt_status === 2 ? $details->discount : ($details->dcnt_status == 1 ? ($details->discount * 100 / $details->price) : null),

                            'discount_price' => $details->dcnt_status === 1 ? ($details->price - $details->discount) : ($details->dcnt_status == 2 ? ($details->price - ($details->price * $details->discount / 100)) : null)

                        ];
                    }),

                    "imgs" => $view->imgs,
                    // "vendor" => $view->vendor


                ];
            });

            return $this->responseWithSuccess('Item Showed Successfully.', $items);
        } catch (\Exception $exception) {
            return $this->responseWithError($exception->getMessage());
        }

    }

    //Show Item list
    public function showItems(Request $request)
    {


        try {


            $query = Item::with('cats', 'imgs', 'qty', 'vendor');

            $view = $this->Filter($query, $request);

            $items = $view['data']->map(function ($view) {
                return [
                    "id" => $view->id,
                    "title" => $view->title,
                    "description" => $view->description,
                    "ingredients" => $view->ingredients,
                    "created_at" => $view->created_at,
                    "updated_at" => $view->updated_at,
                    "cats" => $view->cats,
                    "qty" => $view->qty,
                    "imgs" => $view->imgs,
                    "vendor" => $view->vendor


                ];
            });

            return $this->responseWithSuccess('item_showed', $items, $view['disp']);
        } catch (\Exception $exception) {
            return $this->responseWithError($exception->getMessage());
        }


    }

    //Update Item
    public function updateItem(Request $request)
    {
        $vnd_id = $request->vendor_id;
        $data = $request->all();
        $rules = [
            'item_name' => 'string|required|regex:/^[\pL\s\-]+$/u|max:100',
            'about' => 'string|max:350',
            'ingrs' => 'string|max:400'
        ];

        $response = $this->validateWithJSON($request->only('item_name', 'about', 'ingrs'), $rules);

        if ($response === true) {

            if ($this->VendorCheck($vnd_id) === true) {
                try {


                    $insertedItemID = DB::table('items')
                        ->where('vendor_id', $request->vnd_id)
                        ->where('id', $request->item_id)
                        ->update([
                            'title' => $request->item_name,
                            'description' => $request->about,
                            'ingredients' => $request->ingrs
                        ]);

                    if (count($data['cat_id']) > 0) {
                        foreach ($data['cat_id'] as $catId => $value) {
                            $item_cat_data = [
                                'item_id' => $insertedItemID,
                                'item_category_id' => $data['cat_id'][$catId],
                            ];
                            DB::table('item_category_item')->insert($item_cat_data);
                        }
                    }


                    $images = $request->file('img');
                    if ($request->hasFile('img')) {

                        foreach ($images as $index => $image) {

                            $ext = Str::lower($image->getClientOriginalExtension());//Display File Extension
                            $name = Str::random(14) . Str::limit($image->getClientOriginalName(), 1, '');  // File new Name
                            $file_name = $name . "." . $ext;  // Modified File Name
                            $success = $image->move(public_path('uploads/images/vendors/' . $request->vendor_id . '/items/'), $file_name); //Move Uploaded File(Upload_dir, File_name)
                            $fileData[] = $file_name; // Files name array


                            $img_data = [
                                'item_id' => $request->item_id,
                                'image' => $fileData[$index],
                            ];
                            if ($success) {
                                DB::table('item_images')->insert($img_data);
                                $info = count($fileData) . ' Product Image uploaded.';
                            }

                        }
                    }

                    if (count($data['qtys']) > 0) {
                        foreach ($data['qtys'] as $qty => $value) {
                            $qntt_data = [
                                'item_id' => $request->item_id,
                                'qty' => $data['qtys'][$qty],
                                'price' => $data['price'][$qty],
                            ];
                            DB::table('item_quantities')->insert($qntt_data);
                        }
                    }

                    return $this->responseWithSuccess('Item updated Successfully.');

                } catch (\Exception $exception) {

                    return $this->responseWithError($exception->getMessage());

                }
            } else {
                return $this->responseWithError('Your not allowed.');
            }
        } else {

            return $this->responseWithError('Validation failed', $response);
        }

    }


    //Remove Item--
    public function removeItem(Request $request)
    {

        try {
            if ($this->VendorCheck($request->vnd_id) === true) {

                $user = Auth::user();

                $item = DB::table('items')
                    ->where('vendor_id', $request->vnd_id)
                    ->where('id', $request->item_id);

                if ($item->exists()) {
                    $item_delete = $item->delete();

                    $hasItemImages = DB::table('item_images')->where('item_id', $request->item_id);
                    if ($hasItemImages->exists()) {
                        $itemImages = $hasItemImages->pluck('image');
                        print_r($itemImages);

                        foreach ($itemImages as $deleteImage) {
                            $itemPhoto = (public_path('uploads/images/vendors/' . $request->vnd_id . '/items/' . $deleteImage));
                            if (file_exists($itemPhoto)) {
                                unlink('uploads/images/vendors/' . $request->vnd_id . '/items/' . $deleteImage);
                            }
                        }

                    }
                    return $this->responseWithSuccess('Item deleted');

                } else {
                    return $this->responseWithError('Invalid Item');
                }

            } else {
                return $this->responseWithError('Invalid Permission!');
            }

        } catch (\Exception $exception) {

            return $this->responseWithError($exception->getMessage());

        }
    }

    //Remove Item Image--
    public function removeItemImage(Request $request)
    {

        try {
            if ($this->ItemVerify($request->vnd_id,$request->item_id) === true) {

                $item = DB::table('items')
                    ->where('vendor_id', $request->vnd_id)
                    ->where('id', $request->item_id);

                if ($item->exists()) {
                    $itemImageDelete = DB::table('item_images')
                        ->where('item_id', $request->item_id)
                        ->where('image', $request->img)->delete();

                    $hasItemImage = DB::table('item_images')->where('item_id', $request->item_id);
                    if ($hasItemImage->exists()) {

                        //  print_r($hasItemImage);
                        $itemPhoto = (public_path('uploads/images/vendors/' . $request->vnd_id . '/items/' . $request->img));
                        if (file_exists($itemPhoto)) {
                            unlink('uploads/images/vendors/' . $request->vnd_id . '/items/' . $request->img);
                        }
                    }
                    return $this->responseWithSuccess('Item_image_removed');

                } else {
                    return $this->responseWithError('Invalid Item');
                }

            } else {
                return $this->responseWithError('Invalid Permission!');
            }

        } catch (\Exception $exception) {

            return $this->responseWithError($exception->getMessage());

        }
    }

  //Remove Item Qty--
    public function removeItemQty(Request $request): JsonResponse
    {

        try {
            if ($this->ItemVerify($request->vnd_id,$request->item_id) === true) {

                $item = DB::table('item_quantities')
                    ->where('item_id', $request->item_id)
                    ->where('qty_id', $request->qty_id);

                if ($item->exists()) {

                    $itemCatRemove = $item->delete();
                    return $this->responseWithSuccess('Item_qty_removed');

                } else {
                    return $this->responseWithError('Invalid Item');
                }

            } else {
                return $this->responseWithError('Invalid Permission!');
            }

        } catch (\Exception $exception) {

            return $this->responseWithError($exception->getMessage());

        }
    }


}
