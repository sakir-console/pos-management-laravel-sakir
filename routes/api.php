<?php

use App\Http\Controllers\Api\v1\Admin\AuthController;
use App\Http\Controllers\Api\v1\Admin\VendorManage;
use App\Http\Controllers\Api\v1\Common\LocationController;
use App\Http\Controllers\Api\v1\User\AccountController;
use App\Http\Controllers\Api\v1\User\OrderController;
use App\Http\Controllers\Api\v1\Vendor\CounterController;
use App\Http\Controllers\Api\v1\Vendor\CustomerOrderController;
use App\Http\Controllers\Api\v1\Vendor\ItemCategoryController;
use App\Http\Controllers\Api\v1\Vendor\ItemController;
use App\Http\Controllers\Api\v1\Vendor\VendorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

                      //##########-Unlogged-##############

Route::group(['prefix' => 'v1'], function() {
                         //Admin AUTH::
    Route::post('/admin-login',[AuthController::class,'AdminLogin']);
                        //Users AUTH:
    Route::post('/signup',[AccountController::class,'signUp']);
    Route::post('/signin',[AccountController::class,'signIn']);
                    //COMMON::
    Route::get('/divisions',[LocationController::class,'viewDivisions']);
    Route::get('/districts/{div_id}',[LocationController::class,'viewDistricts']);
    Route::get('/upazilas/{dis_id}',[LocationController::class,'viewUpazilas']);
    Route::get('/unions/{upa_id}',[LocationController::class,'viewUnions']);


    Route::post('/item-categories',[ItemCategoryController::class,'viewItemCategory']);
    Route::get('/items',[ItemController::class,'viewItems']);
    Route::get('/item-list',[ItemController::class,'showItems']);


});

                      //############-Logged-###########
Route::group(['prefix' => 'v1','middleware' => ['auth:sanctum']], function() {
                       //******Admin Control******
    Route::post('/controlpanel',[AuthController::class,'controlpanel']);

    Route::post('/vendor-category/add',[VendorManage::class,'addVendorCategory']);
    Route::post('/vendor-subcategory/add',[VendorManage::class,'addVendorSubCategory']);
    Route::get('/vendor-category',[VendorManage::class,'viewVendorCategory']);
    Route::get('/vendor-subcategory/{id}',[VendorManage::class,'viewVendorSubCategory']);
    Route::get('/vendors-pending',[VendorManage::class,'viewVendorPending']);
    Route::post('/vendor/action',[VendorManage::class,'vendorAction']);
    Route::get('/vendors',[VendorManage::class,'viewVendors']);



    //*********************************User control--+++++++++++++++++++*******
    Route::post('/logout',[AccountController::class,'logOut']);


    Route::post('/vendor/create',[VendorController::class,'createVendor']);
    Route::get('/vendor-list',[VendorController::class,'viewVendorList']);
//-----------------------------------//

    Route::post('/order/add',[OrderController::class,'addOrder']);
    Route::get('/orders',[OrderController::class,'viewMyOrder']);
//-----------------------------------//
///-----------------------------------------------------------------------------------------///////


                        //******VENDOR- control*******


    Route::post('/vendor-profile',[VendorController::class,'viewVendorProfile']);
    Route::get('/vendor/status',[VendorController::class,'viewVendorRank']);


    Route::post('/vendor/qr',[VendorController::class,'vendorQR']);

    Route::post('/item-category/add',[ItemCategoryController::class,'addItemCategory']);
    Route::post('/item-category/update',[ItemCategoryController::class,'updateItemCategory']);


    Route::post('/item/add',[ItemController::class,'addItem']);
    Route::post('/item/update',[ItemController::class,'updateItem']);

    Route::post('/item/delete',[ItemController::class,'removeItem']);
    Route::post('/item-category/delete',[ItemCategoryController::class,'removeCategory']);
    Route::post('/item-image/delete',[ItemController::class,'removeItemImage']);
    Route::post('/item-qty/delete',[ItemController::class,'removeItemQty']);


    Route::post('/counter/add',[CounterController::class,'addCounter']);
    Route::get('/counter',[CounterController::class,'viewCounter']);

    Route::get('/customer-orders/{vnd_id}',[CustomerOrderController::class,'viewOrder']);



    //TEST--
    Route::post('/vendor/search',[VendorController::class,'vendorSearch']);

});
