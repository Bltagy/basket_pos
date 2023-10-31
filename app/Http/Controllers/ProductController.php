<?php

namespace App\Http\Controllers;

use App\Exports\ProductExport;
use App\Exports\ProductNullExport;
use App\Purchase;
use App\QtyHistory;
use Illuminate\Http\Request;
use Keygen;
use App\Brand;
use App\Category;
use App\Unit;
use App\Tax;
use App\Warehouse;
use App\Supplier;
use App\Product;
use App\ProductBatch;
use App\Product_Warehouse;
use App\Product_Supplier;
use Auth;
use DNS1D;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use DB;
use App\Variant;
use App\ProductVariant;
use Carbon\Carbon;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('products-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';
                if($request->p_start_date) {
                    $start_date = $request->p_start_date;
                    $end_date = $request->p_end_date;
                }
                else {
                    $start_date = "";
                    $end_date = "";
                }
            $lims_category_list = Category::where('is_active', true)->get();
            return view('product.index', compact('all_permission','start_date', 'end_date','lims_category_list'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function productData(Request $request)
    {
        $columns = array(
            2 => 'id',
            3 => 'name',
            4 => 'code',
            5 => 'brand_id',
            6 => 'category_id',
            7 => 'qty',
            8 => 'unit_id',
            9 => 'price',
            9 => 'app_price',
            10 => 'cost',
            11 => 'stock_worth'
        );

        $totalData = Product::where('is_active', true);
        if ($request->has('category_id') && !empty($request->category_id) ){
            $category_id = $request->category_id;
            $totalFiltered = $totalData->whereHas('category', function ($q) use ($category_id){
                $q->where('category_id', $category_id);
            });
        }
        if ($request->has('is_promotion') && !empty($request->is_promotion) ){
            $totalFiltered = $totalData->where('promotion', 1);
        }
        $totalData = $totalData->count();
        $totalFiltered = $totalData;
        if ($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'products.' . $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        if (empty($request->input('search.value'))) {

            $products = Product::with('category', 'brand', 'unit')->offset($start);
             if ($request->has('start_date') && !empty($request->start_date) ){
                 $dateS = new Carbon($request->start_date);
                 $dateE = new Carbon($request->end_date);
                 $products = $products->whereBetween('created_at',[$dateS->format('Y-m-d')." 00:00:00", $dateE->format('Y-m-d')." 23:59:59"]);
             }
            if ($request->has('category_id') && !empty($request->category_id) ){
                $category_id = $request->category_id;
                $products = $products->whereHas('category', function ($q) use ($category_id){
                    $q->where('category_id', $category_id);
                });
            }
            if ($request->has('is_promotion') && !empty($request->is_promotion) ){
                $products = $products->where('promotion', 1);
            }
            $products = $products
                ->where('is_active', true)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $products =  Product::select('products.*')
                ->with('category', 'brand', 'unit')
                ->orWhereTranslationLike('name', "%$search%")
                // ->join('categories', 'products.category_id', '=', 'categories.id')
                // ->leftjoin('brands', 'products.brand_id', '=', 'brands.id')
                // ->where([
                //     ['products.is_active', true]
                // ])
                // ->whereTranslationLike('name', $search)
                ->orWhere(
                    'code',
                    'LIKE',
                    "%{$search}%",
                    // ['products.is_active', true]
                )
                // ->orWhere([
                //     ['categories.name', 'LIKE', "%{$search}%"],
                //     ['categories.is_active', true],
                //     ['products.is_active', true]
                // ])
                // ->orWhere([
                //     ['brands.title', 'LIKE', "%{$search}%"],
                //     ['brands.is_active', true],
                //     ['products.is_active', true]
                // ])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)->get();

            $totalFiltered = Product::join('categories', 'products.category_id', '=', 'categories.id')
                ->leftjoin('brands', 'products.brand_id', '=', 'brands.id')
                ->where([
                    ['products.name', 'LIKE', "%{$search}%"],
                    ['products.is_active', true]
                ])
                ->orWhere([
                    ['products.code', 'LIKE', "%{$search}%"],
                    ['products.is_active', true]
                ])
                ->orWhere([
                    ['categories.name', 'LIKE', "%{$search}%"],
                    ['categories.is_active', true],
                    ['products.is_active', true]
                ])
                ->orWhere([
                    ['brands.title', 'LIKE', "%{$search}%"],
                    ['brands.is_active', true],
                    ['products.is_active', true]
                ]);

                if ($request->has('category_id') && !empty($request->category_id) ){
                    $category_id = $request->category_id;
                    $totalFiltered = $totalFiltered->whereHas('category', function ($q) use ($category_id){
                        $q->where('category_id', $category_id);
                    });
                }
            $totalFiltered = $totalFiltered->count();
        }
        $data = array();
        if (!empty($products)) {
            foreach ($products as $key => $product) {
                $purchase = Purchase::whereHas('products', function ($q) use($product) {
                    $q->where('product_id', $product->id);

                })->with('supplier')->get()->last();
                $nestedData['id'] = $product->id;
                $nestedData['key'] = $key;
                $product_image = explode(",", $product->image);
                $product_image = htmlspecialchars($product_image[0]);
                $nestedData['image'] = '<img src="' . url('public/images/product', $product_image) . '" height="80" width="80">';
                $nestedData['name'] = $product->translate('ar') ? $product->translate('ar')->name : '';

                $nestedData['code'] = $product->code;
                $nestedData['app_price'] = $product->app_price;
                if ($product->brand_id)
                    $nestedData['brand'] = $product->brand->title;
                else
                    $nestedData['brand'] = "N/A";
                $nestedData['category'] = $product->category && $product->category->translate('ar') ? $product->category->translate('ar')->name : '';;
                // $nestedData['category'] = $product->category->name;
                $nestedData['qty'] = $product->qty;
                if ($product->unit_id)
                    $nestedData['unit'] = $product->unit->unit_name;
                else
                    $nestedData['unit'] = 'N/A';

                $nestedData['price'] = $product->price;
                $nestedData['cost'] = $product->cost;

                if (config('currency_position') == 'prefix')
                    $nestedData['stock_worth'] = config('currency') . ' ' . ($product->qty * $product->price) . ' / ' . config('currency') . ' ' . ($product->qty * $product->cost);
                else
                    // $nestedData['stock_worth'] = ($product->qty * $product->price) . ' ' . config('currency') . ' / ' . ($product->qty * $product->cost) . ' ' . config('currency');
                    $nestedData['stock_worth'] = 0;
                    $nestedData['supplier'] = isset($purchase->supplier) ? $purchase->supplier->name: '--';
                //$nestedData['stock_worth'] = ($product->qty * $product->price).'/'.($product->qty * $product->cost);

                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . trans("file.action") . '
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                            <li>
                                <button="type" class="btn btn-link view"><i class="fa fa-eye"></i> ' . trans('file.View') . '</button>
                            </li>';
                if (in_array("products-edit", $request['all_permission']))
                    $nestedData['options'] .= '<li>
                            <a href="' . route('products.edit', $product->id) . '" class="btn btn-link"><i class="fa fa-edit"></i> ' . trans('file.edit') . '</a>
                        </li>';
                if (in_array("products-delete", $request['all_permission']))
                    $nestedData['options'] .= \Form::open(["route" => ["products.destroy", $product->id], "method" => "DELETE"]) . '
                            <li>
                              <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="fa fa-trash"></i> ' . trans("file.delete") . '</button>
                            </li>' . \Form::close() . '
                        </ul>
                    </div>';
                // data for product details by one click
                if ($product->tax_id)
                    $tax = Tax::find($product->tax_id)->name;
                else
                    $tax = "N/A";

                if ($product->tax_method == 1)
                    $tax_method = trans('file.Exclusive');
                else
                    $tax_method = trans('file.Inclusive');

                $nestedData['product'] = array(
                    '[ "' . $product->type . '"', ' "' . $product->name . '"', ' "' . $product->code . '"', ' "' . $nestedData['brand'] . '"', ' "' . $nestedData['category'] . '"', ' "' . $nestedData['unit'] . '"', ' "' . $product->cost . '"', ' "' . $product->price . '"', ' "' . $tax . '"', ' "' . $tax_method . '"', ' "' . $product->alert_quantity . '"', ' "' . preg_replace('/\s+/S', " ", $product->product_details) . '"', ' "' . $product->id . '"', ' "' . $product->product_list . '"', ' "' . $product->variant_list . '"', ' "' . $product->qty_list . '"', ' "' . $product->price_list . '"', ' "' . $product->qty . '"', ' "' . $product->image . '"]'
                );
                //$nestedData['imagedata'] = DNS1D::getBarcodePNG($product->code, $product->barcode_symbology);
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );

        echo json_encode($json_data);
    }

    public function create()
    {
        $role = Role::firstOrCreate(['id' => Auth::user()->role_id]);
        if ($role->hasPermissionTo('products-add')) {
            $lims_product_list_without_variant = $this->productWithoutVariant();
            $lims_product_list_with_variant = $this->productWithVariant();
            $lims_brand_list = Brand::where('is_active', true)->get();
            $lims_category_list = Category::where('is_active', true)->where('parent_id', 0)->orWhereNull('parent_id')->with('children')->get();
            // foreach ($lims_category_list as $key => $value) {
            //     foreach ($value->children as $key => $c) {
            //         # code...
            //         if ($c->children){
            //             dd($c->children);
            //         }

            //     }
            // }
            $lims_unit_list = Unit::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            return view('product.create', compact('lims_product_list_without_variant', 'lims_product_list_with_variant', 'lims_brand_list', 'lims_category_list', 'lims_unit_list', 'lims_tax_list', 'lims_warehouse_list'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => [
                'max:255',
                Rule::unique('products')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
            'name_en' => [
                'max:255',
                //     Rule::unique('products')->where(function ($query) {
                //     return $query->where('is_active', 1);
                // }),
            ]
        ]);
        $data = $request->except('image', 'file');
        // $data['name'] = htmlspecialchars(trim($data['name']));
        $data['en'] = [
            'name' => $data['name_en'],
        ];
        $data['ar'] = [
            'name' => $data['name_ar'],
        ];

        if ($data['type'] == 'combo') {
            $data['product_list'] = implode(",", $data['product_id']);
            $data['variant_list'] = implode(",", $data['variant_id']);
            $data['qty_list'] = implode(",", $data['product_qty']);
            $data['price_list'] = implode(",", $data['unit_price']);
            $data['cost'] = $data['unit_id'] = $data['purchase_unit_id'] = $data['sale_unit_id'] = 0;
        } elseif ($data['type'] == 'digital' || $data['type'] == 'service')
            $data['cost'] = $data['unit_id'] = $data['purchase_unit_id'] = $data['sale_unit_id'] = 0;

        $data['product_details'] = str_replace('"', '@', $data['product_details']);

        if ($data['starting_date'])
            $data['starting_date'] = date('Y-m-d', strtotime($data['starting_date']));
        if ($data['last_date'])
            $data['last_date'] = date('Y-m-d', strtotime($data['last_date']));
        $data['is_active'] = true;
        $images = $request->image;
        $image_names = [];
        if ($images) {
            foreach ($images as $key => $image) {
                $imageName = $image->getClientOriginalName();
                $image->move('public/images/product', $imageName);
                $image_names[] = $imageName;
            }
            $data['image'] = implode(",", $image_names);
        } else {
            $data['image'] = 'zummXD2dvAtI.png';
        }
        $file = $request->file;
        if ($file) {
            $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
            $fileName = strtotime(date('Y-m-d H:i:s'));
            $fileName = $fileName . '.' . $ext;
            $file->move('public/product/files', $fileName);
            $data['file'] = $fileName;
        }
        $lims_product_data = Product::create($data);
        //dealing with product variant
        if (!isset($data['is_batch']))
            $data['is_batch'] = null;
        if (isset($data['is_variant'])) {
            foreach ($data['variant_name'] as $key => $variant_name) {
                // $lims_variant_data = Variant::firstOrCreate(['name' => $data['variant_name'][$key]]);
                // $lims_variant_data->name = $data['variant_name'][$key];
                // $lims_variant_data->save();
                // $lims_product_variant_data = new ProductVariant;
                // $lims_product_variant_data->product_id = $lims_product_data->id;
                // $lims_product_variant_data->variant_id = $lims_variant_data->id;
                // $lims_product_variant_data->position = $key + 1;
                // $lims_product_variant_data->item_code = $data['item_code'][$key];
                // $lims_product_variant_data->additional_price = $data['additional_price'][$key];
                // $lims_product_variant_data->qty = 0;
                // $lims_product_variant_data->save();
            }
        }
        if (isset($data['is_diffPrice'])) {
            foreach ($data['diff_price'] as $key => $diff_price) {
                if ($diff_price) {
                    Product_Warehouse::create([
                        "product_id" => $lims_product_data->id,
                        "warehouse_id" => $data["warehouse_id"][$key],
                        "qty" => 0,
                        "price" => $diff_price
                    ]);
                }
            }
        }
        if (isset($data['redirect_to_new'])) {
            return json_encode(1);
        }
        $send_to_app = file_get_contents('https://app2.basketstore.net/api/posOperation/product/create/' . $lims_product_data->id);
        \Session::flash('create_message', 'Product created successfully');
    }

    public function edit($id)
    {
        $role = Role::firstOrCreate(['id' => Auth::user()->role_id]);
        if ($role->hasPermissionTo('products-edit')) {
//            $lims_product_list_without_variant = $this->productWithoutVariant();
//            $lims_product_list_with_variant = $this->productWithVariant();
            $lims_product_list_without_variant = [];
            $lims_product_list_with_variant = [];
            $lims_brand_list = Brand::where('is_active', true)->get();
            $lims_category_list = Category::where('is_active', true)->with('children')->get();
            $lims_unit_list = Unit::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_product_data = Product::where('id', $id)->first();
            $lims_product_data->name_ar = $lims_product_data->translate('ar') ? $lims_product_data->translate('ar')->name : '';
            $lims_product_data->name_en = $lims_product_data->translate('en') ? $lims_product_data->translate('en')->name : '';
            $lims_product_variant_data = $lims_product_data->variant()->orderBy('position')->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();

            return view('product.edit', compact('lims_product_list_without_variant', 'lims_product_list_with_variant', 'lims_brand_list', 'lims_category_list', 'lims_unit_list', 'lims_tax_list', 'lims_product_data', 'lims_product_variant_data', 'lims_warehouse_list'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function updateProduct(Request $request)
    {
        if (!env('USER_VERIFIED')) {
            \Session::flash('not_permitted', 'This feature is disable for demo!');
        } else {
            $this->validate($request, [
                'name_en' => [
                    'max:255',
                    // Rule::unique('products')->ignore($request->input('id'))->where(function ($query) {
                    //     return $query->where('is_active', 1);
                    // }),
                ],

                'code' => [
                    'max:255',
                    Rule::unique('products')->ignore($request->input('id'))->where(function ($query) {
                        return $query->where('is_active', 1);
                    }),
                ]
            ]);

            $lims_product_data = Product::findOrFail($request->input('id'));
            $data = $request->except('image', 'file', 'prev_img');
            // $data['name'] = htmlspecialchars(trim($data['name']));
            $data['en'] = [
                'name' => $data['name_en'],
            ];
            $data['ar'] = [
                'name' => $data['name_ar'],
            ];
            if ($data['type'] == 'combo') {
                $data['product_list'] = implode(",", $data['product_id']);
                $data['variant_list'] = implode(",", $data['variant_id']);
                $data['qty_list'] = implode(",", $data['product_qty']);
                $data['price_list'] = implode(",", $data['unit_price']);
                $data['cost'] = $data['unit_id'] = $data['purchase_unit_id'] = $data['sale_unit_id'] = 0;
            } elseif ($data['type'] == 'digital' || $data['type'] == 'service')
                $data['cost'] = $data['unit_id'] = $data['purchase_unit_id'] = $data['sale_unit_id'] = 0;

            if (!isset($data['featured']))
                $data['featured'] = 0;

            if (!isset($data['promotion']))
                $data['promotion'] = null;

            if (!isset($data['is_batch']))
                $data['is_batch'] = null;

            if (!isset($data['is_imei']))
                $data['is_imei'] = null;

            $data['product_details'] = str_replace('"', '@', $data['product_details']);
            $data['product_details'] = $data['product_details'];
            if ($data['starting_date'])
                $data['starting_date'] = date('Y-m-d', strtotime($data['starting_date']));
            if ($data['last_date'])
                $data['last_date'] = date('Y-m-d', strtotime($data['last_date']));

            $previous_images = [];
            //dealing with previous images
            if ($request->prev_img) {
                foreach ($request->prev_img as $key => $prev_img) {
                    if (!in_array($prev_img, $previous_images))
                        $previous_images[] = $prev_img;
                }
                $lims_product_data->image = implode(",", $previous_images);
                $lims_product_data->save();
            } else {
                $lims_product_data->image = null;
                $lims_product_data->save();
            }

            //dealing with new images
            if ($request->image) {
                $images = $request->image;
                $image_names = [];
                $length = count(explode(",", $lims_product_data->image));
                foreach ($images as $key => $image) {
                    $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                    /*$image = Image::make($image)->resize(512, 512);*/
                    $imageName = date("Ymdhis") . ($length + $key + 1) . '.' . $ext;
                    $image->move('public/images/product', $imageName);
                    $image_names[] = $imageName;
                }
                if ($lims_product_data->image)
                    $data['image'] = $lims_product_data->image . ',' . implode(",", $image_names);
                else
                    $data['image'] = implode(",", $image_names);
            } else
                $data['image'] = $lims_product_data->image;

            $file = $request->file;
            if ($file) {
                $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                $fileName = strtotime(date('Y-m-d H:i:s'));
                $fileName = $fileName . '.' . $ext;
                $file->move('public/product/files', $fileName);
                $data['file'] = $fileName;
            }

            $lims_product_variant_data = ProductVariant::where('product_id', $request->input('id'))->select('id', 'variant_id')->get();
            foreach ($lims_product_variant_data as $key => $value) {
                if (!in_array($value->variant_id, $data['variant_id'])) {
                    ProductVariant::find($value->id)->delete();
                }
            }
            //dealing with product variant
            if (isset($data['is_variant'])) {
                foreach ($data['variant_name'] as $key => $variant_name) {
                    if ($data['product_variant_id'][$key] == 0) {
                        $lims_variant_data = Variant::firstOrCreate(['name' => $data['variant_name'][$key]]);
                        $lims_product_variant_data = new ProductVariant();

                        $lims_product_variant_data->product_id = $lims_product_data->id;
                        $lims_product_variant_data->variant_id = $lims_variant_data->id;

                        $lims_product_variant_data->position = $key + 1;
                        $lims_product_variant_data->item_code = $data['item_code'][$key];
                        $lims_product_variant_data->additional_price = $data['additional_price'][$key];
                        $lims_product_variant_data->qty = 0;
                        $lims_product_variant_data->save();
                    } else {
                        Variant::find($data['variant_id'][$key])->update(['name' => $variant_name]);
                        ProductVariant::find($data['product_variant_id'][$key])->update([
                            'position' => $key + 1,
                            'item_code' => $data['item_code'][$key],
                            'additional_price' => $data['additional_price'][$key]
                        ]);
                    }
                }
            } else {
                $data['is_variant'] = null;
                $product_variants = ProductVariant::where('product_id', $lims_product_data->id)->get();
                foreach ($product_variants as $key => $product_variant) {
                    $product_variant->delete();
                }
            }
            if (isset($data['is_diffPrice'])) {
                foreach ($data['diff_price'] as $key => $diff_price) {
                    if ($diff_price) {
                        $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($lims_product_data->id, $data['warehouse_id'][$key])->first();
                        if ($lims_product_warehouse_data) {
                            $lims_product_warehouse_data->price = $diff_price;
                            $lims_product_warehouse_data->save();
                        } else {
                            Product_Warehouse::create([
                                "product_id" => $lims_product_data->id,
                                "warehouse_id" => $data["warehouse_id"][$key],
                                "qty" => 0,
                                "price" => $diff_price
                            ]);
                        }
                    }
                }
            } else {
                $data['is_diffPrice'] = false;
                foreach ($data['warehouse_id'] as $key => $warehouse_id) {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($lims_product_data->id, $warehouse_id)->first();
                    if ($lims_product_warehouse_data) {
                        $lims_product_warehouse_data->price = null;
                        $lims_product_warehouse_data->save();
                    }
                }
            }
            if ( isset($data['qty']) && $data['qty'] != $data['old_qty'] ){
                $dataQTY = [
                    'product_id'=>$data['id'],
                    'old_qty'=> $data['old_qty'],
                    'new_qty'=> $data['qty'],
                ];
                QtyHistory::create($dataQTY);
            }
            $data['promotion'] = $data['promotion'] ?1 :0;
            $lims_product_data->update($data);
            $send_to_app = file_get_contents('https://app2.basketstore.net/api/posOperation/product/update/' . $lims_product_data->id);
            \Session::flash('edit_message', 'Product updated successfully');
        }
    }

    public function generateCode()
    {
        $id = Keygen::numeric(8)->generate();
        return $id;
    }

    public function search(Request $request)
    {
        $product_code = explode(" ", $request['data']);
        $lims_product_data = Product::where('code', $product_code[0])->first();

        $product[] = $lims_product_data->name;
        $product[] = $lims_product_data->code;
        $product[] = $lims_product_data->qty;
        $product[] = $lims_product_data->price;
        $product[] = $lims_product_data->id;
        return $product;
    }

    public function saleUnit($id)
    {
        $unit = Unit::where("base_unit", $id)->orWhere('id', $id)->pluck('unit_name', 'id');
        return json_encode($unit);
    }

    public function getData($id, $variant_id)
    {
        if ($variant_id) {
            $data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->select('products.name', 'product_variants.item_code')
                ->where([
                    ['products.id', $id],
                    ['product_variants.variant_id', $variant_id]
                ])->first();
            $data->code = $data->item_code;
        } else
            $data = Product::select('name', 'code')->find($id);
        return $data;
    }

    public function productWarehouseData($id)
    {
        $warehouse = [];
        $qty = [];
        $batch = [];
        $expired_date = [];
        $imei_number = [];
        $warehouse_name = [];
        $variant_name = [];
        $variant_qty = [];
        $product_warehouse = [];
        $product_variant_warehouse = [];
        $lims_product_data = Product::select('id', 'is_variant')->find($id);
        if ($lims_product_data->is_variant) {
            $lims_product_variant_warehouse_data = Product_Warehouse::where('product_id', $lims_product_data->id)->orderBy('warehouse_id')->get();
            $lims_product_warehouse_data = Product_Warehouse::select('warehouse_id', DB::raw('sum(qty) as qty'))->where('product_id', $id)->groupBy('warehouse_id')->get();
            foreach ($lims_product_variant_warehouse_data as $key => $product_variant_warehouse_data) {
                $lims_warehouse_data = Warehouse::find($product_variant_warehouse_data->warehouse_id);
                $lims_variant_data = Variant::find($product_variant_warehouse_data->variant_id);
                $warehouse_name[] = $lims_warehouse_data->name;
                $variant_name[] = $lims_variant_data->name;
                $variant_qty[] = $product_variant_warehouse_data->qty;
            }
        } else {
            $lims_product_warehouse_data = Product_Warehouse::where('product_id', $id)->orderBy('warehouse_id', 'asc')->get();
        }
        foreach ($lims_product_warehouse_data as $key => $product_warehouse_data) {
            $lims_warehouse_data = Warehouse::find($product_warehouse_data->warehouse_id);
            if ($product_warehouse_data->product_batch_id) {
                $product_batch_data = ProductBatch::select('batch_no', 'expired_date')->find($product_warehouse_data->product_batch_id);
                $batch_no = $product_batch_data->batch_no;
                $expiredDate = date(config('date_format'), strtotime($product_batch_data->expired_date));
            } else {
                $batch_no = 'N/A';
                $expiredDate = 'N/A';
            }
            $warehouse[] = $lims_warehouse_data->name;
            $batch[] = $batch_no;
            $expired_date[] = $expiredDate;
            $qty[] = $product_warehouse_data->qty;
            if ($product_warehouse_data->imei_number)
                $imei_number[] = $product_warehouse_data->imei_number;
            else
                $imei_number[] = 'N/A';
        }

        $product_warehouse = [$warehouse, $qty, $batch, $expired_date, $imei_number];
        $product_variant_warehouse = [$warehouse_name, $variant_name, $variant_qty];
        return ['product_warehouse' => $product_warehouse, 'product_variant_warehouse' => $product_variant_warehouse];
    }

    public function printBarcode()
    {
        $lims_product_list_without_variant = $this->productWithoutVariant();
        // dd($lims_product_list_without_variant->toArray());
        $lims_product_list_with_variant = $this->productWithVariant();
        return view('product.print_barcode', compact('lims_product_list_without_variant', 'lims_product_list_with_variant'));
    }

    public function productWithoutVariant()
    {
        return Product::ActiveStandard()->select('id', 'name', 'code')->get();
    }

    public function productWithVariant()
    {
        return Product::join('product_variants', 'products.id', 'product_variants.product_id')
            ->ActiveStandard()
            ->whereNotNull('is_variant')
            ->select('products.id', 'products.name', 'product_variants.item_code')
            ->orderBy('position')->get();
    }

    public function limsProductSearch(Request $request)
    {
        if ($request->has('all')) {
            $all_prod = [];
            $lims_product_data_all = Product::where('qty', '!=', 100)->get();
            foreach ($lims_product_data_all as $key => $lims_product_data) {
                $product = [];
                $variant_id = '';
                $additional_price = 0;
                $product[] = $lims_product_data->name;
                if ($lims_product_data->is_variant)
                    $product[] = $lims_product_data->item_code;
                else
                    $product[] = $lims_product_data->code;

                $product[] = $lims_product_data->price + $additional_price;

                if ($lims_product_data->code && $lims_product_data->barcode_symbology) {
                    $product[] = DNS1D::getBarcodePNG($lims_product_data->code, $lims_product_data->barcode_symbology);
                } else {
                    $product[] = "";
                }
                $product[] = $lims_product_data->promotion_price;
                $product[] = config('currency');
                $product[] = config('currency_position');
                $product[] = $lims_product_data->qty;
                $product[] = $lims_product_data->id;
                $product[] = $variant_id;
                $product[] = $lims_product_data;
                $all_prod[] = $product;
            }

            return $all_prod;
        }

        $product_code = explode("(", $request['data']);
        $product_code[0] = rtrim($product_code[0], " ");
        $lims_product_data = Product::where([
            ['code', $product_code[0]],
            ['is_active', true]
        ])->first();
        if (!$lims_product_data) {
            $lims_product_data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->select('products.*', 'product_variants.item_code', 'product_variants.variant_id', 'product_variants.additional_price')
                ->where('product_variants.item_code', $product_code[0])
                ->first();

            $variant_id = $lims_product_data->variant_id;
            $additional_price = $lims_product_data->additional_price;
        } else {
            $variant_id = '';
            $additional_price = 0;
        }
        $product[] = $lims_product_data->name;
        if ($lims_product_data->is_variant)
            $product[] = $lims_product_data->item_code;
        else
            $product[] = $lims_product_data->code;

        $product[] = $lims_product_data->price + $additional_price;
        $product[] = DNS1D::getBarcodePNG($lims_product_data->code, $lims_product_data->barcode_symbology);
        $product[] = $lims_product_data->promotion_price;
        $product[] = config('currency');
        $product[] = config('currency_position');
        $product[] = $lims_product_data->qty;
        $product[] = $lims_product_data->id;
        $product[] = $variant_id;
        $product[] = $lims_product_data;
        return $product;
    }

    /*public function getBarcode()
    {
        return DNS1D::getBarcodePNG('72782608', 'C128');
    }*/

    public function checkBatchAvailability($product_id, $batch_no, $warehouse_id)
    {
        $product_batch_data = ProductBatch::where([
            ['product_id', $product_id],
            ['batch_no', $batch_no]
        ])->first();
        if ($product_batch_data) {
            $product_warehouse_data = Product_Warehouse::select('qty')
                ->where([
                    ['product_batch_id', $product_batch_data->id],
                    ['warehouse_id', $warehouse_id]
                ])->first();
            if ($product_warehouse_data) {
                $data['qty'] = $product_warehouse_data->qty;
                $data['product_batch_id'] = $product_batch_data->id;
                $data['expired_date'] = date(config('date_format'), strtotime($product_batch_data->expired_date));
                $data['message'] = 'ok';
            } else {
                $data['qty'] = 0;
                $data['message'] = 'This Batch does not exist in the selected warehouse!';
            }
        } else {
            $data['message'] = 'Wrong Batch Number!';
        }
        return $data;
    }

    public function importProduct(Request $request)
    {
        //get file
        $upload = $request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        if ($ext != 'csv')
            return redirect()->back()->with('message', 'Please upload a CSV file');

        $filePath = $upload->getRealPath();
        //open and read
        $file = fopen($filePath, 'r');
        $header = fgetcsv($file);
        $escapedHeader = [];
        //validate
        foreach ($header as $key => $value) {
            $lheader = strtolower($value);
            $escapedItem = preg_replace('/[^a-z]/', '', $lheader);
            array_push($escapedHeader, $escapedItem);
        }
        //looping through other columns
        while ($columns = fgetcsv($file)) {
            foreach ($columns as $key => $value) {
                $value = preg_replace('/\D/', '', $value);
            }
            $data = array_combine($escapedHeader, $columns);

            if ($data['brand'] != 'N/A' && $data['brand'] != '') {
                $lims_brand_data = Brand::firstOrCreate(['title' => $data['brand'], 'is_active' => true]);
                $brand_id = $lims_brand_data->id;
            } else
                $brand_id = null;

            $lims_category_data = Category::firstOrCreate(['name' => $data['category'], 'is_active' => true]);

            $lims_unit_data = Unit::where('unit_code', $data['unitcode'])->first();
            if (!$lims_unit_data)
                return redirect()->back()->with('not_permitted', 'Unit code does not exist in the database.');

            $product = Product::firstOrNew(['name' => $data['name'], 'is_active' => true]);
            if ($data['image'])
                $product->image = $data['image'];
            else
                $product->image = 'zummXD2dvAtI.png';

            $product->name = $data['name'];
            $product->code = $data['code'];
            $product->type = strtolower($data['type']);
            $product->barcode_symbology = 'C128';
            $product->brand_id = $brand_id;
            $product->category_id = $lims_category_data->id;
            $product->unit_id = $lims_unit_data->id;
            $product->purchase_unit_id = $lims_unit_data->id;
            $product->sale_unit_id = $lims_unit_data->id;
            $product->cost = $data['cost'];
            $product->price = $data['price'];
            $product->tax_method = 1;
            $product->qty = 0;
            $product->product_details = $data['productdetails'];
            $product->is_active = true;
            $product->save();

            if ($data['variantname']) {
                //dealing with variants
                $variant_names = explode(",", $data['variantname']);
                $item_codes = explode(",", $data['itemcode']);
                $additional_prices = explode(",", $data['additionalprice']);
                foreach ($variant_names as $key => $variant_name) {
                    $variant = Variant::firstOrCreate(['name' => $variant_name]);
                    if ($data['itemcode'])
                        $item_code = $item_codes[$key];
                    else
                        $item_code = $variant_name . '-' . $data['code'];

                    if ($data['additionalprice'])
                        $additional_price = $additional_prices[$key];
                    else
                        $additional_price = 0;

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'position' => $key + 1,
                        'item_code' => $item_code,
                        'additional_price' => $additional_price,
                        'qty' => 0
                    ]);
                }
                $product->is_variant = true;
                $product->save();
            }
        }
        return redirect('products')->with('import_message', 'Product imported successfully');
    }

    public function deleteBySelection(Request $request)
    {
        $product_id = $request['productIdArray'];
        foreach ($product_id as $id) {
            $lims_product_data = Product::findOrFail($id);
            $lims_product_data->is_active = false;
            $lims_product_data->save();
            $send_to_app = file_get_contents('https://app2.basketstore.net/api/posOperation/product/delete/' . $id);
        }

        return 'Product deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_product_data = Product::findOrFail($id);
        $lims_product_data->is_active = false;
        if ($lims_product_data->image != 'zummXD2dvAtI.png') {
            $images = explode(",", $lims_product_data->image);
            foreach ($images as $key => $image) {
                if (file_exists('public/images/product/' . $image))
                    @unlink('public/images/product/' . $image);
            }
        }
        $lims_product_data->delete();
        $send_to_app = file_get_contents('https://app2.basketstore.net/api/posOperation/product/delete/' . $id);
        return redirect('products')->with('message', 'تم الحـذف بنجاح');
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export()
    {
        return Excel::download(new ProductExport, 'products.xlsx');
    }
    public function exportNull()
    {
        return Excel::download(new ProductNullExport(), 'products-zero-qty.xlsx');
    }
    public function doDB()
    {
        $prods = ["745178077415",
            "6224000787417",
            "6217000340133",
            "6210966701710",
            "6224007730164",
            "5000204080711",
            "11459313",
            "6920085606208",
            "6945449301209",
            "731551988928",
            "6920085606062",
            "6920085606178",
            "19502310",
            "60683737",
            "65779318",
            "6223006317789",
            "6223004765674",
            "6211020271347",
            "6217001578689",
            "56666666415265",
            "6224008630722",
            "6224007399286",
            "6224008630739",
            "6224008630746",
            "6224007399293",
            "6224007399309",
            "6224008630753",
            "6224008630838",
            "6224007399323",
            "6224007399330",
            "6224008630814",
            "6224009794010",
            "6223010167868",
            "6224007701744",
            "6224007701324",
            "6224007701362",
            "6224007701652",
            "6224007399316",
            "6224007701706",
            "6224008630616",
            "6224007399415",
            "6224008630944",
            "6224007399491",
            "6224009794805",
            "6224008630883",
            "6224007399477",
            "6224008630999",
            "6224008630784",
            "6224009117437",
            "6224009117444",
            "6223003779306",
            "6223002380442",
            "6223014230285",
            "6223014230018",
            "6223014230155",
            "6223014230179",
            "6223014230025",
            "6223014230292",
            "6224009280650",
            "6224009280643",
            "6224009280872",
            "6224009010448",
            "6224008603788",
            "6224000515492",
            "6224000515119",
            "6224000515171",
            "6225000122215",
            "6223006620209",
            "6224007250488",
            "6223006880450",
            "6223000460153",
            "6223000460221",
            "6223000460160",
            "089686120196",
            "3255555555555560000",
            "6332001108107",
            "6223006050617",
            "6224000398613",
            "6224000398729",
            "6224000398897",
            "6223007190121",
            "6223006051416",
            "6223006050884",
            "6224008575283",
            "6224008575290",
            "6224008756347",
            "6224008756361",
            "6224008756354",
            "6223006880832",
            "6223006050686",
            "6223006880849",
            "6223006880825",
            "6224000495138",
            "6221031490750",
            "6221031490774",
            "3800203050300",
            "3800203050515",
            "3800203050676",
            "6224000582852",
            "6224000582876",
            "6224000787349",
            "6224000787196",
            "6224000787493",
            "6224000787257",
            "6224000787202",
            "6224000787233",
            "6224000787189",
            "6224000787561",
            "6771504484637",
            "6224000787486",
            "6224000787004",
            "6224000787295",
            "6224000787042",
            "6224000787288",
            "6771504484606",
            "6771504484613",
            "6771504478780",
            "6771504478797",
            "7613036052887",
            "6223002230105",
            "6223001384731",
            "6223001384663",
            "6223001384717",
            "6223001384724",
            "6224000851620",
            "6224000851637",
            "6223003500030",
            "6224001031441",
            "6224009238903",
            "6224009238910",
            "6224009238453",
            "6224009238927",
            "6224009238460",
            "6224009238132",
            "6224009238149",
            "6224009238897",
            "6223002840007",
            "8001673100181",
            "4015600572969",
            "6224000437800",
            "6224000437817",
            "6224000437688",
            "6221094501264",
            "6221094501271",
            "6221094501288",
            "6994670810332",
            "8682139495838",
            "8682139495845",
            "8680998401014",
            "8680998401021",
            "6224009238972",
            "8901138504557",
            "3610340639999",
            "3610340641107",
            "6223002051069",
            "6223002051076",
            "6223002051113",
            "6223002051144",
            "6223002051137",
            "6223002051168",
            "6624000171221",
            "8691206060411",
            "8691206060114",
            "6281008463330",
            "6224007557099",
            "6224007557129",
            "6223006342286",
            "6224010026322",
            "6224000666897",
            "6223001241485",
            "62240006666",
            "6223004113536",
            "6223004112270",
            "6772504586420",
            "6224009939374",
            "6224009939398",
            "4015000939997",
            "4015000939980",
            "6224009890545",
            "6224010026278",
            "6224009890569",
            "6224009890538",
            "6224010026285",
            "6224011512121",
            "6224000666279",
            "6223004112263",
            "6223004111495",
            "6223004112867",
            "6223004112379",
            "6224009893614",
            "6224009939350",
            "6224009939572",
            "6224009939619",
            "6225000300804",
            "6224000666613",
            "6224000666606",
            "6224009890514",
            "6224009939558",
            "7613036052726",
            "**********",
            "6224000234300",
            "211209",
            "574547575",
            "22222222222285",
            "369999999999999",
            "34563783222224",
            "251126",
            "36666666",
            "5555",
            "6222011400677",
            "6281073110542",
            "6281073110474",
            "6281073110108",
            "8901905500607",
            "6221045008606",
            "6221508070614",
            "6225000246546",
            "60702941",
            "6225000336674",
            "6225000548718",
            "155249170004",
            "5022496181174",
            "5022496181501",
            "6221155124906",
            "6221155117878",
            "8001841604510",
            "8855044040060",
            "8850344200213",
            "6223001347279",
            "6224000101732",
            "6225000004672",
            "6223004112294",
            "6223001347903",
            "6223001340317",
            "6223000228326",
            "6291003087868",
            "6223000228548",
            "6223000229415",
            "5900020025050",
            "221048503832",
            "6221155137296",
            "6221155137289",
            "6224000693817",
            "6224000693800",
            "6221048503771",
            "6223005878748",
            "6222017030441",
            "3610340628849",
            "6222017039543",
            "3610340627873",
            "6222017030465",
            "6224007939499",
            "6223001384618",
            "3838824145167",
            "6222017039376",
            "3061375781076",
            "6222017039413",
            "6222017039390",
            "3061375781137",
            "6224000851415",
            "6224007939505",
            "6224000851408",
            "8904033601857",
            "8904033601864",
            "8904033601871",
            "8904033601895",
            "8904033601840",
            "6222017030472",
            "3610340025464",
            "3061376194530",
            "6222017030458",
            "6222017030328",
            "6222017039321",
            "6222017030373",
            "3838824127804",
            "3838824239705",
            "3838824127927",
            "3838824332215",
            "3838824358574",
            "3838824127866",
            "3838824127880",
            "3838824239682",
            "6223001342038",
            "6221155049582",
            "6281001342076",
            "6281031264584",
            "6281031264706",
            "6281031264645",
            "6281031264768",
            "6281031264409",
            "6297000854472",
            "3574661053882",
            "8694965541504",
            "8694965541269",
            "8694965541252",
            "8694965541498",
            "8694965541481",
            "8694965529106",
            "8694965529090",
            "8694965541511",
            "8694965535930",
            "8694965535947",
            "8694965540149",
            "6221155026576",
            "6223002552030",
            "6223002550913",
            "6223002552146",
            "6221155054944",
            "6223002552139",
            "6224007586051",
            "6222015600202",
            "6224007581940",
            "6224007581988",
            "6222027800676",
            "6223001061076",
            "6223001062165",
            "6223001062318",
            "6224010887145",
            "6224010887046",
            "6224010887169",
            "6224008603733",
            "6222027800423",
            "6222027800645",
            "6223000057391",
            "5000204080742",
            "6281058102081",
            "6223001823551",
            "7899567248719",
            "8714555000300",
            "6224000625375",
            "6224000625436",
            "6224000625443",
            "6224007892534",
            "6224007892527",
            "6224007892565",
            "6224007892503",
            "6224007330210",
            "6223006342965",
            "6222022100382",
            "6221007031918",
            "6224000398064",
            "8710448563945",
            "2015",
            "6221033177260",
            "6221033000063",
            "6294002405800",
            "6223006050648",
            "6223006051508",
            "5000226921306",
            "6224011274104",
            "6224011274111",
            "6224011274128",
            "7310688001118",
            "7310680020100",
            "7638900429190",
            "6221508121415",
            "6281006408784",
            "6224009238118",
            "44441",
            "6001087011136",
            "8711600839588",
            "6281006406346",
            "8901138506384",
            "8901138506377",
            "8888202062666",
            "8888202000682",
            "8888202026828",
            "8888202005069",
            "8888202026422",
            "88821184",
            "8888202035233",
            "6223004682872",
            "6223004680328",
            "6223002050055",
            "6281006506367",
            "6281006506428",
            "6224000398910",
            "8710448564003",
            "6224009010035",
            "6294002400096",
            "6224000448745",
            "6224000398224",
            "6223007190152",
            "6221033101449",
            "6221033101661",
            "6294002416837",
            "6223006050600",
            "6223006050556",
            "6223006050068",
            "6223006050693",
            "6223006050457",
            "6223006050730",
            "6251136023348",
            "4015000948982",
            "6624000309013",
            "8699990912350",
            "8699990512581",
            "8699990512642",
            "8699990512598",
            "8699990512666",
            "8699990512734",
            "6224009160495",
            "6224007314531",
            "6224007314999",
            "6221155112675",
            "6224000201906",
            "6224000201906",
            "6224007939925",
            "6223001386452",
            "6223001386476",
            "6223003445027",
            "8901138815738",
            "8901138821784",
            "8901138507558",
            "8901138831721",
            "833741000066",
            "6223003446161",
            "6223003445034",
            "6223003445058",
            "6224009238996",
            "6224009238408",
            "6224009238378",
            "6224009238361",
            "6224009238392",
            "6224009238385",
            "6224009238354",
            "6224009238507",
            "6224009238491",
            "6224009238484",
            "6224009238477",
            "2013",
            "6211101189011",
            "5283000503336",
            "5283000516572",
            "6771504470333",
            "6224007227909",
            "6224000817015",
            "6224000817022",
            "6223000711453",
            "5283000503350",
            "6224000234188",
            "6224000234287",
            "6224000234065",
            "6224000234089",
            "6224000234201",
            "6224000234829",
            "6224000234249",
            "6224000234140",
            "6224000234164",
            "6224000234225",
            "6222006202170",
            "6222006202187",
            "6222006202217",
            "5283000503343",
            "6223000912874",
            "6223000912867",
            "6224008756484",
            "6224007314203",
            "6224007314838",
            "6224008070436",
            "6224008070443",
            "6224009725656",
            "6224008070429",
            "6224008070078",
            "6224007314234",
            "6224008070467",
            "2222222222",
            "223000430521",
            "6223003193003",
            "6253002444615",
            "8691720020649",
            "6223000496992",
            "6223003190569",
            "6223003190576",
            "6223003190125",
            "6223003190767",
            "6223003190545",
            "6223003193041",
            "6223003193263",
            "6223003193256",
            "6223003193249",
            "6222009565708",
            "6222009565760",
            "6222009565791",
            "6223000490617",
            "6223000757383",
            "6223000757901",
            "6223003190842",
            "6223003190217",
            "6224010573291",
            "6224010573345",
            "6224010573352",
            "6224010573338",
            "6224010573383",
            "6224010573376",
            "6224010573369",
            "6224010573307",
            "6224010573161",
            "6223000759103",
            "6223003193218",
            "6222009506565",
            "6222009506619",
            "6222009565845",
            "6222009565814",
            "6222035205944",
            "6224007561027",
            "6223005591890",
            "6223005591883",
            "6223005596338",
            "622300559631",
            "6223005409652",
            "6223005596802",
            "6223005596819",
            "6281031262061",
            "6281031262306",
            "3610340019982",
            "6222017038553",
            "3610340649653",
            "3610340655289",
            "3610340653650",
            "3610340020025",
            "3610340636691",
            "3610340020308",
            "4015000992602",
            "6192430015304",
            "4015001004182",
            "4015000934831",
            "6281031259702",
            "4015000996266",
            "6281006443716",
            "6281031262092",
            "01155856766666666666666",
            "6281031262153",
            "6281031262214",
            "8001841475776",
            "6221009000547",
            "6221009000257",
            "6221009000233",
            "6221009001711",
            "8001841475806",
            "8001841475868",
            "4015000940979",
            "4015000976299",
            "4015000985109",
            "6192430015830",
            "8001090452245",
            "8001090406811",
            "8006540166376",
            "5011321946330",
            "8001090662262",
            "8001090742445",
            "6222014510366",
            "6222014511806",
            "6222014511783",
            "6222014511776",
            "6222014510427",
            "6222014511837",
            "6222014511820",
            "6223003445065",
            "6221155086426",
            "6221155086709",
            "6221155086464",
            "6221155086365",
            "6221155086662",
            "6281006534841",
            "6221155086341",
            "6221155102577",
            "6221155102720",
            "6221155102591",
            "6221155135063",
            "6221155063328",
            "6221155113122",
            "6221155126894",
            "6221155126900",
            "6223000236246",
            "6223000238127",
            "6223000238134",
            "6225000269859",
            "6225000269842",
            "6225000269866",
            "6225000269835",
            "6223004116988",
            "6223004113895",
            "6223004113901",
            "6223004113918",
            "6224000693633",
            "6224000693657",
            "6224000693664",
            "6224000693138",
            "6224000693640",
            "6224007586914",
            "6221155140845",
            "6221155140852",
            "6223004116421",
            "42951939",
            "8690146619215",
            "8690146619215",
            "8690146619215",
            "8690146619215",
            "6221134006834",
            "6223007671859",
            "62212687",
            "6224007995907",
            "6224009678464",
            "6223006531338",
            "62810098",
            "62810142",
            "62810111",
            "62810128",
            "62810135",
            "8693323735104",
            "8693323738105",
            "8693323736101",
            "8693323737108",
            "6224009678129",
            "42112907",
            "40099361",
            "6223006532823",
            "6223003942335",
            "6223007431019",
            "6223007431002",
            "6223007431033",
            "6221024120107",
            "8859128602113",
            "6223001033707",
            "6221033190030",
            "6223000055205",
            "6223000761137",
            "6223000050538",
            "6221024994401",
            "8412434057758",
            "8412434057772",
            "6223006050099",
            "6223006050051",
            "6223006050105",
            "6223006051492",
            "6223006051461",
            "6223006051447",
            "6223006051454",
            "6223006050433",
            "6223006050419",
            "6223006050426",
            "6221024994108",
            "8698529300965",
            "6223006051676",
            "6223000050453",
            "6221024994739",
            "6223001220039",
            "8413812011065",
            "8413812011171",
            "413812011096",
            "8413812011201",
            "6224008603221",
            "6225000193017",
            "6226000002224",
            "010610045889",
            "6225000193031",
            "6225000193024",
            "6223000762295",
            "9614000405454",
            "6224010177161",
            "29748972",
            "50402299",
            "6228300052370",
            "6228300052371",
            "9900000000000000",
            "6228300052374",
            "6228300052373",
            "6223004111556",
            "6223004114175",
            "6222001558067",
            "6225000428416",
            "6805699955754",
            "6225000016439",
            "6225000019386",
            "6224007349519",
            "6225000043206",
            "6225000016460",
            "6224007349908",
            "6224007349878",
            "6224007349861",
            "6224007349892",
            "6923769830180",
            "6946357110488",
            "6223002534135",
            "9614000348119",
            "6224010074125",
            "6224010074040",
            "6224010074217",
            "6224010074170",
            "6224010074095",
            "6224010074101",
            "6224010074019",
            "6224010074545",
            "6224010074149",
            "15255679",
            "5900020026279",
            "6223000228692",
            "6224009580569",
            "6224010447349",
            "6223000208205",
            "6223000208243",
            "6224009580194",
            "6223000762110",
            "6224007305720",
            "6224007305027",
            "211123",
            "211124",
            "211125",
            "6224008286035",
            "6224008286110",
            "6224008286165",
            "6224008286158",
            "6224008286042",
            "6221029111674",
            "6221031493546",
            "6221031491504",
            "6221031000508",
            "6221031490552",
            "6221031490569",
            "6221031494987",
            "6221333000084",
            "6221333000145",
            "6221333000244",
            "6221333000251",
            "6221333000176",
            "6221333000152",
            "6221333000121",
            "6221333000046",
            "4750127300632",
            "4750127300717",
            "4750127300595",
            "4750127300618",
            "6221333000022",
            "6221333000237",
            "6221333000268",
            "6222035202158",
            "6222035202356",
            "6222035202165",
            "8698657254604",
            "8698657257124",
            "8698657257100",
            "8698657254628",
            "6271001320501",
            "6221333000435",
            "6221031000522",
            "6221333000411",
            "6221031493584",
            "6221031493560",
            "6221031493553",
            "6221031493539",
            "6223000410400",
            "4750127300731",
            "4750127000266",
            "6225000441682",
            "6225000441620",
            "6225000441606",
            "6225000225404",
            "69229019",
            "24004192",
            "6228600136262",
            "6225000332942",
            "6225000332980",
            "6225000332928",
            "6224008513612",
            "6223001365754",
            "7891000118139",
            "5760466730464",
            "3698741258148",
            "6224001179099",
            "6224001179112",
            "6224001179105",
            "6281031262573",
            "4015000536769",
            "6281031248805",
            "4015000527200",
            "6281031265574",
            "4015000624206",
            "6281031257241",
            "6223004117022",
            "6223004117015",
            "6223004115097",
            "6223005876119",
            "6223005876126",
            "6223004115103",
            "6223004117039",
            "6221155123534",
            "144012",
            "62201048467",
            "6220104856",
            "62201040978",
            "6221155069122",
            "6221155069146",
            "6221155069139",
            "6221155132444",
            "3698741258124",
            "6224001179136",
            "6224001179143",
            "6224001179150",
            "6224001179198",
            "6224001179204",
            "6223006692015",
            "6223006312517",
            "4009041104162",
            "025616202501",
            "4009041104124",
            "025616042145",
            "6223006312500",
            "6223000112595",
            "025616042138",
            "6223011810466",
            "6223011810480",
            "6223011810497",
            "6223011810473",
            "9555021502619",
            "9555021503159",
            "6222024101967",
            "8000380147144",
            "8000380005963",
            "8000380005949",
            "8000380005918",
            "8000380140541",
            "8000380153442",
            "8000380186532",
            "8000380153466",
            "6223006312326",
            "046214220209",
            "046214731552",
            "8710502470011",
            "046214230208",
            "6221073004540",
            "8004800023001",
            "6221073004724",
            "8710502171017",
            "6223006311015",
            "8710508140208",
            "9556995203700",
            "9556995203724",
            "9556995203687",
            "9556995203403",
            "6224008245476",
            "8710502153013",
            "8710502260025",
            "6223006316799",
            "6221007030249",
            "9556995110749",
            "9556995203472",
            "9556995203465",
            "9556995204011",
            "9556995204042",
            "9556995204035",
            "9556995204028",
            "6223005393869",
            "6223005390080",
            "40144061",
            "6222035202936",
            "7622201698843",
            "80965435",
            "6222009500327",
            "6223000182192",
            "8000380001149",
            "8000380152704",
            "80001027",
            "80001799",
            "80854357",
            "6222001114768",
            "6222001114850",
            "6222001103663",
            "6222001114928",
            "6222001109542",
            "6222001109566",
            "6222001114942",
            "6222001109597",
            "6222001109856",
            "6222001103540",
            "9556995240019",
            "9556995240002",
            "9556995240026",
            "8690120131955",
            "6223003807009",
            "6223003807207",
            "6223003806200",
            "8001585008339",
            "8001585008353",
            "8001585008155",
            "8001585008162",
            "8001585008780",
            "8001585008797",
            "8001585008063",
            "8001585008070",
            "8001585008056",
            "6221029910130",
            "6222009513938",
            "6222009513846",
            "6222009542396",
            "6222009542518",
            "6222009513860",
            "6222009543027",
            "6222009542433",
            "6223007944137",
            "6223007944120",
            "6223007943048",
            "6223007943055",
            "6223007942799",
            "6223007942782",
            "6223007941587",
            "6223007942843",
            "776992010456",
            "776992010487",
            "776992010463",
            "776992030638",
            "776992030010",
            "776992010432",
            "776992093220",
            "776992073536",
            "776992500131",
            "6221073001389",
            "6224008785460",
            "6224008785729",
            "6224008785033",
            "6224008785736",
            "6224008785491",
            "6224008785705",
            "6224008785484",
            "6224008785712",
            "6223007944090",
            "6223007944106",
            "6223007942973",
            "6223007942904",
            "6223007942829",
            "6223007942812",
            "6223007942881",
            "6223007942874",
            "6223007944717",
            "6223007944724",
            "6222035201953",
            "6221030009120",
            "6221030009151",
            "6221030009168",
            "6221030006426",
            "6221030006389",
            "6221030006402",
            "8000380192670",
            "8000380153480",
            "8000380007264",
            "8000380141548",
            "8000380007271",
            "8000380184644",
            "8000380152728",
            "9501025183712",
            "8000380007240",
            "8000380007219",
            "6224008630630",
            "6223000418055",
            "6224010177109",
            "6224010177116",
            "6224010177277",
            "6224008767183",
            "6224007879917",
            "6224008767589",
            "6224008767541",
            "6224008767565",
            "6224008767602",
            "6224009923236",
            "6224010177086",
            "6223005501059",
            "8796541233350",
            "4000607852008",
            "4000415043506",
            "4000415043308",
            "4000415043209",
            "4000607850004",
            "4000607854705",
            "8690632247434",
            "8690632245799",
            "5283003310016",
            "5283003311013",
            "6194008554789",
            "6224000124724",
            "86934299",
            "59093886",
            "86930987",
            "6224008930853",
            "6224008930877",
            "6224008930396",
            "6224007394137",
            "6223006933514",
            "6221134007121",
            "5000159471688",
            "4011100046283",
            "6223006050129",
            "8692806051199",
            "6191544100340",
            "8690997083999",
            "8691707035093",
            "6224009065097",
            "6224009065059",
            "6224009057061",
            "6191544100760",
            "6194005449750",
            "6194005446100",
            "6224008767527",
            "6221134007107",
            "6221134011388",
            "8690997011763",
            "8690997011787",
            "8690997011770",
            "6224009678471",
            "6223006532571",
            "6224007399279",
            "211277",
            "211278",
            "11282",
            "6223007374101",
            "6223007372329",
            "977777777774",
            "6223005408730",
            "6223005591241",
            "8682077099631",
            "7622201695286",
            "4014400902495",
            "6224008712046",
            "6224008712039",
            "6224008712800",
            "6224008712077",
            "8690997084767",
            "8690997084798",
            "8690997084804",
            "8682077099617",
            "6224007995556",
            "6223007372312",
            "6281004012938",
            "6281004013034",
            "6281004887932",
            "5281028454333",
            "6281004013133",
            "776992211211",
            "776992210214",
            "776992231059",
            "776992210115",
            "776992231103",
            "776992231080",
            "000000000000000000000",
            "8690840042661",
            "8690840177745",
            "88888888888888888888",
            "5555555555555555555555",
            "6223006531987",
            "6223006532496",
            "6224011523349",
            "6224011523721",
            "6222035208907",
            "6223005592910",
            "6223005593467",
            "6281004916625",
            "6281004893025",
            "6221009003722",
            "6221009003708",
            "6221009003715",
            "6221143076088",
            "6223002552016",
            "6223004149276",
            "6223004149269",
            "6225000269804",
            "6223000239421",
            "6225000269811",
            "6223014212731",
            "6225000265943",
            "6225000265936",
            "6225000265912",
            "6225000265929",
            "6223000239445",
            "6223000237281",
            "6223000237182",
            "6223000237199",
            "6223006343931",
            "’خق0004",
            "5000318003910",
            "5000318003972",
            "6291105690096",
            "6291105690102",
            "016000578609",
            "6223000228128",
            "6223000228869",
            "6223000228883",
            "6224000787387",
            "6223011810879",
            "6223011810909",
            "6223011810923",
            "6223011810886",
            "6223011810862",
            "6223011810893",
            "6223011810916",
            "6225487123576",
            "6225000280267",
            "6221155146663",
            "6223001038955",
            "6040391212433",
            "6040391219746",
            "6040391211658",
            "6040391211412",
            "6040391219982",
            "6040391212457",
            "6040391219678",
            "6040391212372",
            "6224008323174",
            "6040391212686",
            "6040391212860",
            "11111111111111111111111111111",
            "6040391212396",
            "6224008323747",
            "6281013112148",
            "6040391212365",
            "6224008323532",
            "6224008323549",
            "6224008323556",
            "6224008323563",
            "6224008323570",
            "6224008323587",
            "6224008323983",
            "6224011148016",
            "6224011148023",
            "6281013112100",
            "6221133014991",
            "6221133000710",
            "211270",
            "054881004787",
            "054881004770",
            "6040391211863",
            "6040391213560",
            "9312631801118",
            "9312631801101",
            "6251361100104",
            "6281013244108",
            "6281013243002",
            "6281013222069",
            "6281013233003",
            "9312631142228",
            "9312631142044",
            "9312631142129",
            "9312631142259",
            "9312631142174",
            "9312631142099",
            "9312631142242",
            "9312631142075",
            "6281013113183",
            "9312631142211",
            "9312631142112",
            "6281013244221",
            "6281013222342",
            "6281013222045",
            "6281013241008",
            "6281013247086",
            "6281013247024",
            "6281013244160",
            "6281013165052",
            "6281013231009",
            "6281013164000",
            "6281013165076",
            "054881009270",
            "070177072780",
            "6221155099471",
            "6222001004113",
            "6222001002874",
            "6222001002058",
            "6222001002164",
            "6222001012200",
            "6223000228708",
            "6223000434567",
            "6281100084051",
            "6281007036023",
            "6221143095461",
            "6221155120793",
            "6221155120861",
            "6221155068286",
            "6221155068248",
            "6221155075239",
            "6221155071989",
            "6221155068248",
            "6221155068224",
            "6221143094860",
            "6223006123861",
            "36294111",
            "6222000530101",
            "222000530125",
            "222000530132",
            "6224000200008",
            "6224000200053",
            "6224000200015",
            "6224000200046",
            "6224000200084",
            "6224000200022",
            "6224000200039",
            "6221022111459",
            "6221022111428",
            "6221022111404",
            "6221022111022",
            "6221022111282",
            "6221022111671",
            "6221022111336",
            "6224009280933",
            "6223006880313",
            "6223006880320",
            "6223006880306",
            "6223006880283",
            "6221022000555",
            "6221022000562",
            "6221022000593",
            "6221022111534",
            "6221022111527",
            "6223001511113",
            "6221022111619",
            "6194003310236",
            "6221022001118",
            "6221022000609",
            "6291047001998",
            "6291047020524",
            "6291047001974",
            "6291047010204",
            "6223007947152",
            "6223007947190",
            "6223007947183",
            "6223007947176",
            "6223007947213",
            "615222222222222",
            "634444444444445",
            "65555555555555",
            "254122222222222000",
            "6223007943918",
            "6223007943949",
            "6223007943932",
            "6223007943925",
            "6223000663967",
            "6223007520119",
            "64444444444444",
            "6224008637240",
            "+++++++++++++++++",
            "6224007581391",
            "6224007581803",
            "6224009765362",
            "6224009765379",
            "6224008603443",
            "6935897609668",
            "8858716041181",
            "6224007581636",
            "6224009062126",
            "6935897609675",
            "6224008603429",
            "6224008603511",
            "6224008603504",
            "9002907000344",
            "6224000515478",
            "6224010154544",
            "1234567890128",
            "6211101147011",
            "6952668300890",
            "6211101148018",
            "6224007214107",
            "6625000197051",
            "6224000365752",
            "6294002416110",
            "6224008603054",
            "6224010887183",
            "6224009224319",
            "6223006050044",
            "6223006050471",
            "6223006050013",
            "6223006051515",
            "6223006051218",
            "6223006051225",
            "6223006050136",
            "6223006050143",
            "6210242661134",
            "6210246091142",
            "6210241011145",
            "6926544668848",
            "6926544668428",
            "6224000567118",
            "6224000567125",
            "6221033000759",
            "6224007581216",
            "6224102550384",
            "211243",
            "6222001000016",
            "6222001000009",
            "655555555555555555+",
            "211244",
            "6224102550759",
            "6224010498150",
            "6224010498044",
            "6224000531119",
            "6224008686057",
            "6221078144296",
            "80228710",
            "22222222222222222222222222",
            "6223006051904",
            "6223006051294",
            "6223000762660",
            "6224000705596",
            "6223003779146",
            "6223003770181",
            "6223003773717",
            "6223003772642",
            "6223006510623",
            "6223003770198",
            "6223006510500",
            "6223006510524",
            "6223003776787",
            "6223006510937",
            "6223006510517",
            "6223003778972",
            "6223003733",
            "6221007004929",
            "6224000432898",
            "6224000432256",
            "6223002381814",
            "6223002382477",
            "6223002380664",
            "6223002380671",
            "6223002380688",
            "6223007520812",
            "6223003776862",
            "6224001105951",
            "6224008709008",
            "6223007520379",
            "6281007033565",
            "5000318106314",
            "5000318111424",
            "5000318004634",
            "2650650025207",
            "6223000229576",
            "6223000229514",
            "5201004523600",
            "9556995200327",
            "9556995130389",
            "9556995202505",
            "9556995111074",
            "9556995140258",
            "6221073010534",
            "6221073020113",
            "50251100",
            "6294003568269",
            "6294003568245",
            "4014400901405",
            "4014400901191",
            "6291003006678",
            "9556995203427",
            "4014400400007",
            "8000500023976",
            "8901063151017",
            "6194008555298",
            "6221030003708",
            "6222001114621",
            "6223007941686",
            "6223007942850",
            "6281004873522",
            "6221073010503",
            "6223000419427",
            "6223000418345",
            "6223000418314",
            "6224009372188",
            "6221030005160",
            "8697439300829",
            "6221030004040",
            "6221134010299",
            "6224011523141",
            "6224011523110",
            "6224011523127",
            "6224011523134",
            "6224007399422",
            "6224007701799",
            "6224007701461",
            "6224007701447",
            "75099706",
            "6223007945356",
            "6223007945363",
            "6223007946186",
            "6223007946193",
            "6223007946216",
            "6223007946230",
            "6222024102407",
            "6224011036092",
            "6224011036146",
            "6224011036016",
            "6224011036047",
            "6224011036252",
            "6224011036283",
            "6223005596246",
            "6223005596666",
            "6223005596062",
            "6223011431067",
            "6223011431050",
            "6223004761621",
            "265163480",
            "555555555555555",
            "251128",
            "6222035202721",
            "6222035202196",
            "6222035202189",
            "6223000417829",
            "6223000417867",
            "6223000417843",
            "6223000414866",
            "6223000414842",
            "6223000414859",
            "6223000414835",
            "745114140777",
            "6223000492383",
            "6223000411056",
            "6222035200574",
            "6222035200550",
            "6222035200567",
            "6221031491221",
            "6221031496141",
            "6223000418628",
            "6223000418635",
            "6223000418611",
            "6222035204572",
            "6222035204442",
            "6222035204435",
            "745114141347",
            "745114141385",
            "745114141576",
            "6221031491252",
            "6221031490903",
            "745114141583",
            "6224009986798",
            "6224009986774",
            "6224009986804",
            "6224009986781",
            "6224000582586",
            "6224000582470",
            "6221031000904",
            "6221031492839",
            "6223007670326",
            "6223000414194",
            "6223000414200",
            "8714800025706",
            "8714800019927",
            "8714800036009",
            "8714800035989",
            "5449000026521",
            "3080216032566",
            "3080216031248",
            "3080216032443",
            "3080216032474",
            "3080216028026",
            "6223001364894",
            "6223001364962",
            "8714800003384",
            "8714800023214",
            "8714800018289",
            "8714800025775",
            "6223001363644",
            "8714800014434",
            "8714800027847",
            "8714800027878",
            "8714800017473",
            "6223000082447",
            "8714800036535",
            "6223001364887",
            "5449000026408",
            "6224008033868",
            "6223004834240",
            "6223003771522",
            "6223003771553",
            "6223003771560",
            "6223003771539",
            "6223003771515",
            "6223003778149",
            "6223003771546",
            "6223003778309",
            "6223003776879",
            "6223003776886",
            "6223003778293",
            "6224000432218",
            "6224000432232",
            "4002309003689",
            "4002309003665",
            "4002309003634",
            "4002309003672",
            "5201127097019",
            "6225000341098",
            "6225000341050",
            "6225000424715",
            "6223006050402",
            "6225000175198",
            "6223006531383",
            "6224008157496",
            "6224008157410",
            "1321412341239",
            "5410126726947",
            "6223003943202",
            "6224011036306",
            "6223004833045",
            "6223004833038",
            "2027",
            "111101",
            "6224000145286",
            "222222223",
            "33333333325",
            "654121111114",
            "3354666666",
            "211239",
            "211238",
            "211241",
            "6555555555555550",
            "211271",
            "211272",
            "211273",
            "2191274",
            "211274",
            "6625000237023",
            "6224000693862",
            "211134",
            "211146",
            "6224000705428",
            "6224000705442",
            "6224000705435",
            "9994",
            "9995",
            "2666666666666630",
            "9582648792291",
            "6224009893140",
            "6294018800859",
            "6294018800637",
            "6294018800323",
            "6294018800507",
            "6294018801580",
            "6294018801504",
            "6223007652582",
            "6224008603375",
            "6225000271784",
            "6223006620230",
            "6223001356400",
            "6223001356301",
            "6223001351269",
            "6223001642596",
            "6223004224409",
            "6223004226441",
            "6223004224447",
            "6281026311354",
            "6281026122288",
            "6281026305209",
            "6281026120789",
            "6281026306008",
            "6281026120840",
            "6281026310708",
            "6281026122264",
            "9008700153843",
            "90169274",
            "6211101199010",
            "6222014336782",
            "6223003777012",
            "6223003777036",
            "6223003777029",
            "6223003777098",
            "6223003777128",
            "6223003777111",
            "6223000759066",
            "6223000756560",
            "6223000758991",
            "764460447026",
            "764460447019",
            "764460447033",
            "764460447002",
            "6224000234416",
            "6224000234393",
            "6224000234379",
            "6224011746090",
            "6224011746052",
            "6224011746076",
            "6224011746120",
            "6224011746069",
            "6224011746083",
            "6224011746106",
            "6223003693947",
            "6223007190343",
            "6223007190237",
            "6223007190336",
            "8412569000193",
            "6224009765331",
            "9310768103006",
            "9310768102993",
            "6224009765065",
            "6224000448233",
            "6224000448226",
            "6224009765058",
            "6224000448288",
            "8410090071460",
            "8410090081469",
            "6224009010387",
            "6224009010400",
            "076770294604",
            "6224008603580",
            "6224008603740",
            "6223007190312",
            "6223007190404",
            "6223007190411",
            "6224000448271",
            "6224000448257",
            "6225000280151",
            "6225000280137",
            "6225000280144",
            "4750010000199",
            "4750010000304",
            "6111249190224",
            "6223007190763",
            "6221031000515",
            "6224000627508",
            "5760466922807",
            "6223004834332",
            "6223004834318",
            "6223004834141",
            "6223004830136",
            "6223004830433",
            "6223004835629",
            "622300200032",
            "6223004772689",
            "6223004772443",
            "6221011000689",
            "6223004773488",
            "6223004772672",
            "6223004771552",
            "6223004772665",
            "6221011000269",
            "6223004772719",
            "6221011001471",
            "6223004772702",
            "6223004771545",
            "622101100351",
            "6771504457402",
            "3073781162141",
            "6224010979031",
            "6224010979048",
            "6222014300011",
            "6224000507305",
            "6223004260452",
            "6224008470731",
            "6224007693032",
            "6224007693254",
            "6224007693292",
            "6224007693308",
            "6224007693285",
            "91767390",
            "6224000850340",
            "6224000850302",
            "6223007791151",
            "6771504456788",
            "6223000662533",
            "6223006932692",
            "6772504535350",
            "6772504535343",
            "6772504535930",
            "6772504535978",
            "09143281",
            "6222000491006",
            "010",
            "0106100458888888889",
            "6224007892541",
            "6224009117222",
            "6223001824091",
            "6224010424258",
            "6222006201883",
            "6222006201715",
            "6222006201876",
            "6222006201852",
            "6222006201173",
            "6222006200510",
            "6222006200497",
            "6222006200664",
            "6222006201067",
            "6222006200732",
            "6223004277238",
            "6223004276972",
            "6223004276965",
            "6223004277214",
            "6223004277276",
            "6223004276071",
            "6223004277283",
            "6223004277252",
            "6224010424029",
            "6224010424531",
            "6224010424517",
            "6224010424524",
            "6224010424500",
            "6224010424081",
            "6224007727317",
            "6224007717141",
            "6224007717998",
            "6224007717660",
            "6224007717806",
            "6224007717233",
            "6224007717653",
            "6224007725474",
            "6224007725634",
            "6224007725627",
            "6224007717592",
            "6224007717011",
            "6224007717202",
            "6224007717394",
            "6224007717721",
            "6224007717974",
            "6224007725207",
            "6224007717325",
            "6224007725757",
            "6224007717707",
            "6224007717349",
            "6224007717028",
            "6224007717585",
            "6224007725351",
            "6224007725755",
            "6224007717203",
            "6224007717134",
            "6224007717127",
            "6224007717752",
            "2250052334",
            "6224007725405",
            "6224007725443",
            "6224007717691",
            "6224007717400",
            "6224007717615",
            "6224007717738",
            "6224007717714",
            "6224007725795",
            "6224007725399",
            "6224007717301",
            "6223002570058",
            "6222006201722",
            "6223010166342",
            "6224008630241",
            "6223010166335",
            "6224007399545",
            "6224007399224",
            "6224007399231",
            "6224007399217",
            "6224008630234",
            "6224007399682",
            "6224007399729",
            "6224007399552",
            "6224007399958",
            "6224007399606",
            "6224009794294",
            "6224007399750",
            "6224009794393",
            "6224009794362",
            "6224007399163",
            "6224007399101",
            "6224009794379",
            "6224007399668",
            "6224007399538",
            "6224007399170",
            "6224008630913",
            "6224007399200",
            "6224007399590",
            "6224009794287",
            "6224007399095",
            "6224008630920",
            "6224007399002",
            "6224008630692",
            "6224007399019",
            "6224007399026",
            "6224007701607",
            "6224007701416",
            "6224007701157",
            "6224007701638",
            "6224011843041",
            "6224011843058",
            "6224007701676",
            "6224007701188"];
        $all = Product::whereIn('code',$prods)->pluck('id')->toArray();

        dd(array_diff($all, $prods) );
    }
}
