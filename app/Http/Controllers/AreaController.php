<?php

namespace App\Http\Controllers;

use App\Area;
use Illuminate\Http\Request;
use App\Coupon;
use Auth;
use Keygen;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AreaController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);

        // if($role->hasPermissionTo('unit')) {
            $lims_coupon_all = Area::orderBy('id', 'desc')->get();
            foreach ($lims_coupon_all as $key => $value) {
                $value->name_ar = $value->translate('ar')->name;
                $value->name_en = $value->translate('en')->name;
            }

            return view('areas.index', compact('lims_coupon_all'));
        // }
        // else
            // return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function create()
    {
        //
    }

    public function generateCode()
    {
        $id = Keygen::alphanum(10)->generate();
        return $id;
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data = [
            'en' => [
                'name' => $request->name_en,
            ],
            'ar' => [
                'name' => $request->name_ar,
            ],
            'fee' => $request->fee,
        ];
        Area::create($data);
        return redirect('areas')->with('message', 'Coupon created successfully');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $area  = Area::find($data['area_id']);
        $data = [
            'en' => [
                'name' => $request->name_en,
            ],
            'ar' => [
                'name' => $request->name_ar,
            ],
            'fee' => $request->fee,
        ];

        $area->update($data);

        $send_to_app = file_get_contents('https://app2.basketstore.net/api/posOperation/area/update/'.$area->id);
        return redirect('areas')->with('message', 'تم التحديث بنجاح');
    }

    public function deleteBySelection(Request $request)
    {
        $coupon_id = $request['couponIdArray'];
        foreach ($coupon_id as $id) {
            $lims_coupon_data = Area::find($id);
            $lims_coupon_data->delete();
        }
        return 'تم الحذف بنجاح!';
    }

    public function destroy($id)
    {
        $lims_coupon_data = Area::find($id);
        $lims_coupon_data->delete();
        return redirect('areas')->with('not_permitted', 'تم الحذف بنجاح');
    }
}
