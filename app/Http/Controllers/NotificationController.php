<?php

namespace App\Http\Controllers;

use App\Imports\ProductImport;
use Excel;
use Illuminate\Http\Request;
use App\User;
use App\Notifications\SendNotification;
use Auth;

class NotificationController extends Controller
{
    public function store(Request $request)
    {

        $uploadedFile = $request->file('file');
        if(!$uploadedFile){
            return redirect()->back()->with('not_permitted', 'لم يتم تحميل الملف');
        }

        $ss = Excel::toArray(new ProductImport(), $uploadedFile, \Maatwebsite\Excel\Excel::XLSX);

        $barcodes = collect(reset($ss))->pluck('0')->map(function ($address){

            return strval(trim($address));

        })->toArray();
        $imported_items = collect(reset($ss))->map(function ($address){
            $address[0] = strval(trim($address[0]));
            return  $address;
        });

        $products = \App\Product::whereIn('code', $barcodes)->get();
        $c= 0;
        foreach ($products as $product) {
            $item  =$imported_items->where('0', $product->code)->first();
            if ( $item ){
                $c++;
                $product->qty = floatval($item[2]);
                $product->price = floatval($item[1]);
                $product->app_price = floatval($item[1]);
                $product->save();
            }
        }
        exec("wget -O https://app2.basketstore.net/api/syncAll");


        return redirect()->back()->with('message', 'تم التحديث بنجاح');
    }

    public function markAsRead()
    {
    	Auth::user()->unreadNotifications->markAsRead();
    }
}
