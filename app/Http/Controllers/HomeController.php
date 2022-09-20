<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sale;
use App\Returns;
use App\ReturnPurchase;
use App\ProductPurchase;
use App\Purchase;
use App\Expense;
use App\Payroll;
use App\Quotation;
use App\Payment;
use App\Account;
use App\Category;
use Intervention\Image\Facades\Image as ImageCreator;
use App\Product_Sale;
use App\Customer;
use App\GeneralSetting;
use App\Product;
use App\Product_Warehouse;
use App\RewardPointSetting;
use App\Shift;
use App\User;
use DB;
use Auth;
use Carbon\Carbon;
use Printing;
use Rawilk\Printing\Contracts\Printer;
use Spatie\Permission\Models\Role;

/*use vendor\autoload;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;*/

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        return view('home');
    }

    public function sync()
    {
        ini_set('memory_limit', '-1');
        $producsts = Product::all();
        $names = ["النجار ذرة فيشار 500 جرام", "النجار دقيق فاخر للمخبوازات و البيتزا 1 ك", "النجار لوبيا 500 جرام", "اولويز احلام طول الليل طويل جداا 12 فوطة", "زينة مناديل كلاسيك متعددة الاستخدام 200منديل كرتون", "البوادى حلاوة طحينية باللوز 275 جرام", "كسترا للغسالات الاتوماتيك 2.5 كجم", "كوين رول اكياس قمامة بالفانيليا 70سم × 90سم 12 كيس برباط", "شوجر ماتش محلى بدون سعرات 300 كيس يعادل ذ1 ملعقة من السكر", "فيتراك مربى تين 650 جرام", "وردة معطر جول برائحة بوكية 300 مل", "كلاسي مناديل كريمي للبشرة الحساسة 20 منديل", "ميلفا معطر جو ورد 400مل", "مان لوك جيل للشعر ويت لوك ازرق وفر 2ج 125جرام", "بن اليمنى عبد المعبود بالبندق 125 جرام", "لواكر كوادرتيني مكعبات ويفر اسبريسو 110 جرام", "لواكر ويفر ليمون 90جرام", "النسر الذهبى اكياس قمامة 70*90", "النسر الذهبى 50 كيس للثلاجة 20*35", "النسر الذهبى رول اكياس ثلاجة 20*35", "النسر الذهبى رول اكياس ثلاجة 25*40", "النسر الذهبى رول اكياس قمامة 70*90", "النسر الذهبى رول قمامة مشرشر 80*110", "النسر الذهبى رول اكياس قمامة 70*90+ غطاء  قاعدة تواليت بلاستيك مجانا", "النسر الذهبى رول مفارش سفرة 110*110", "النسر الذهبى ورق زبدة غذائى 40 سم*10 متر", "النسر الذهبى رقائق ورق فويل 20 متر", "ويرذرس اورجينال زبدة الكراميل 125جرام", "دريم فروتى برتقال محلي بالسكر 25 جرام", "فيتراك مربى فراولة 650 جرام", "الضيافه جوزة الطيب محوجة 60جرام", "كولجيت معجون اسنان 3×1 بالنعناع 100مل", "كولجيت معجون اسنان 3×1 بالنعناع 50مل", "كولجيت اوبتيك وايت للتبييض 75مل", "برسيل للغسالات العادية والفوق اوتوماتيك2ك", "لايف بوي غسول لليدين عناية متكاملة 200مل", "دوف بديل الزيت للعناية بالشعر 300مل", "كلوس اب معجون اسنان فريش اخضر 50 مل", "كلوس اب معجون اسنان فريش اخضر 25 مل", "دوف بلسم بالزيوت المغزية للشعر 300 مل", "كلوس اب معجون اسنان دايموند اتراكشن باور وايت 75 مل", "كلوس اب معجون اسنان دايموند اتراكشن باور وايت 75 مل+ فرشة هدية", "كلوس اب معجون اسنان وايت ناو بالنعناع البارد 75 مل + فرشة هدية", "كرست معجون اسنان كومبليت مع غسول الفم والنعناع 50 مل", "كرست معجون اسنان وايت ثري دي قوة النعناع 100 مل", "كرست معجون اسنان وايت ثري دي قوة النعناع 50 مل", "اولويز الترا قطن ناعم كبير بالاجنحة 2عبوة + 1 مجاناً  8 فوط", "اولويز الترا رفيعة قطن ناعم كبير بالاجنحة 3عبوة + 1 مجاناً  8 فوط", "اولويز الترا رفيعة قطن ناعم ليلية بالاجنحة 3عبوة + 1مجاناً 7 فوط", "فجل 1 رابطة", "كيندر شوكولاتة بالحليب 8 الواح 100 جرام", "كوكي كرانشي صدور الدجاج كع بطاطس 8 قطع", "ستينج مشروب طاقة 275 مل", "النسر الذهبى رول مفارش سفرة 45 كيس 110*110", "النجار عدس بجبة 500 جرام", "باندا جبنةبراميلي  1 كيلو عبوة بلاستيك", "مكفتيز بسكريم محشو بكريمة الكاكاو 12 قطعة", "هوهوز كيك الشوكولاته الملفوف المحشو بالكاكاو والبندق", "كيندر ماكسي شوكولاتة 21 جرام", "شاتو بسكويت بالشوكولاتة 140 جرام", "سيريلاك ارز وخضار مع اللبن من6شهور 125جرام", "هاريبو شكل دب بطعم الفاكهة 17 جرام", "هاريبو طعم الكولا 17 جرام", "امتنان عسل نحل نوارة البرسيم 250 جرام", "امتنان عسل نحل زهور الربيع 800 جرام", "حدائق كاليفورنيا تونة جولد يلوفين قطعة واحدة 185 جرام", "حدائق كاليفورنيا تونة قطع فاتحة فى ماء وملح ومحلول ملحي 185 جرام", "حدائق كاليفورنيا تونة شرائح فى فلفل اسود وليمون 120 جرام", "حدائق كاليفورنيا تونة شرائح فى فلفل حار 120 جرام", "حدائق كاليفورنيا سردين مغربي فى زيت دوار الشمس ومحلول ملحي 125 جرام", "حدائق كاليفورنيا سردين مغربى فى زيت دوار الشمس وفلفل حار 125 جرام", "هيربال شامبو ترطيب عميق بخلاصة جوز الهند 400 مل", "صني زيت طبخ قلى وتحمير 1.75 لتر", "النجار فول تدميس 500 جرام", "كلاسي مناديل عرض 3قطع 550منديل", "الطحان تمر نصف جاف 1.800 جرام", "فاست عصير بودر مانجو 30 جرام", "ايزيس شاي المناعة 20 كيس", "ايزيس زيت زيتون بكر ممتاز 500 مل", "شيبسي فورنو فلفل حلو و جبنة 5 جنيه", "فيوري شوكولاتة بالحليب بالكراميل 36 جرام", "بيبي جوي كلوت مقاس (4) 9 حفاضة", "صانسيلك شامبو قوة و لمعان 350مل + زيت صانسيلك 75مل هدية", "صانسيلك شامبو للشعر الاسود 350مل + زيت صانسيلك 75مل هدية", "صانسيلك شامبو ناعم وانسيابي 350مل + زيت صانسيلك 75مل هدية", "صانسيلك شامبو بزيت اللوز والعسل350مل + زيت صانسيلك 75مل هدية", "صانسيلك شامبو بجوز الهند 350مل + زيت صانسيلك 75مل هدية", "طماطم للصلصة 1 كيلو", "دولتشى بونبون كريمة القهوة 8 قطع", "تايد اتوماتيك داوني 4 كيلو +2 كيلو مجانا", "رودس جبنة بالزيتون500 جرام", "رودس جبنة اسطنبولي 500 جرام", "الضيافة قرنفل 50 جرام", "الضيافة نعناع مجروش 40 جرام", "الضيافة بصل بودرة 70 جرام", "الضيافة كمون مطحون 60 جرام", "الضيافة حبة البركة 90 جرام", "الضيافة كزبرة مطحونة 70 جرام", "الضيافة بهارات محشي 70 جرام", "الضيافة ورق لورا مطحون 70 جرام", "الضيافة كاري 60 جرام", "حدائق كاليفورنيا زيت زيتون بكر ممتاز 500 مل", "موسي شراب شعير بنكهة التفاح خالى من الكحول 330 مل", "كوكي صدور باني كرنشى حار 20 قطعه", "تايد اتوماتيك مسحوق غسيل بلمسة داوني 4 كيلو", "العراب خل البلح 250 مل", "العراب خل الاعشاب 300 مل", "العراب ماء الزعتر 250 مل", "دومتي جبنة اسطنبولي لبن طبيعي 500 جرام", "جود مورنينج صابون بالورد والحليب 110 جرام", "جود مورنينج صابون بالياسمين والزيوت 110 جرام", "جبن ثلاجة الطيب ملح خفيف عبوة بلاستيك 250 جرام", "سلطة كازبلانكا كيري بالسوسيس 250 جرام", "سلطة قريش بحبة البركة 250 جرام", "اطياب لانشون بيف فاخر 250 جرام", "زيتون اخضر جامبو مفدغ بالفلفل250 جرام", "ايزيس زنجبيل 20 كيس", "حلاوة طحينية حشو سودانى 250 جرام", "حلاوة طحينية بالشوكولاته وزن 250جرام", "الضيافة فلفل اسمر 70 جرام", "كريستال زيت ذرة 3.5 لتر", "كيتو اقراص طارد للناموس 44 قرص", "كراون ورنيش سائل احذية اسود 75 مل + اسفنجة احذية شفاف", "جونسون غسول ترطيب فائق 300 مل", "جونسون لوشن ناعم للاطفال 200 مل", "جارنيه كولور ناتشرلز اشقر رمادي 7.1", "جارنيه كولور ناتشرلز بني غامق لامع 4.7", "جارنيه كولور ناتشرلز اشقر غامق رمادي 6.1", "جارنيه كولور ناتشرلز اشقر فاتح 8", "النجار نشا ذرة غذائي 250 جرام", "تيفاني ماري بسكويت شاي بالقمح 200 جرام", "جينرال منظف متعدد الاستخدامات بالياسمين 3.1 كيلو", "بانتين شامبو ضد تساقط الشعر 200 مل", "هيربال شامبو قوة فاتنة بخلاصة العسل 400 مل", "تايد فوق اوتوماتيك العطر الاصلي600 جرام المركز", "هيربال شامبو بخلاصة زيت الارغان 400 مل", "صانسيلك بلسم بزيت اللوز والعسل ضد التقصف 350 مل", "الضيافة بهارات شوربة 70 جرام", "الضيافه بهارات اسماك 70 جرام", "الضيافه زعتر مجروش 40 جرام", "الضيافه توابل كبدة 70 جرام", "الضيافه بهارات جمبري 70 جرام", "الضيافه بابريكا 70 جرام", "الضيافه بهارات كبسة 70 جرام", "الضيافه توابل فراخ 60 جرام", "ديتول منظف متعدد الاغراض 4*1 براحة الصنوبر 650 مل", "اندومي شعرية طعم الفراخ البلدي 120 جرام", "اوليف زيتون اخضر كامل 1 كيلو", "امريكانا سردين مغربي فى زيت دوار الشمس وحلول ملحي مع فلفل حار 125 جرام", "مش مخلط سادة 250 جرام", "اريال انتعاش اللافندر نصف اوتوماتيك يدوي 300 جرام", "رويال شاي بالنعناع 20 فتلة", "كلوريل 4*1 لتنظيف المنزل باللافندر 1 كيلو", "فيبا سائل غسيل الاطباق بالليمون الاخضر 3 كيلو", "برسيل باور جيل للغسالات الاتوماتيك 1 كيلو", "كسترا للغسيل اليدوى والغسالات العادية عطر الزهور 1 كيلو", "سيجنال كومبليت  8 اكشن جوز الهند والنعناع 100 مل", "برايفت نعومة القطن الطبيعي بالاجنحة رقيقة جدا 16+4 حجم سوبر", "جالكسي شوكولاته غامقة 38جرام", "فانيش مزيل البقع للاقمشة البيضاء بودر 30 جرام", "فانيش مزيل بقع الاقمشة بودر 30 جرام", "فاتيكا سائل غسيل اليدين بالزيتون والجرجير والحبة السوداء 250 مل", "بسكو مصر ويفرز بسكويت بالفانيليا 7 قطع", "بن اليمني عبد المغبود محوج سوبر وسط 200 جرام", "بن اليمني عبد المعبود محوج سوبر وسط 100 جرام", "بن اليمني عبد المعبود محوج سوبر غامق 200 جرام", "بن اليمني عبد المعبود محوج سوبر غامق 100 جرام", "بن اليمني عبد المعبود محوج دوبل غامق 200 جرام", "بن اليمني عبد المعبود محوج دوبل غامق 100 جرام", "بن اليمني عبد المعبود محوج دوبل فاتح 200 جرام", "بن اليمني عبد المعبود محوج دوبل فاتح 100 جرام", "بن اليمني عبد المعبود محوج دوبل وسط 200 جرام", "بن اليمني عبد المعبود محوج دوبل وسط 100 جرام", "بن شاهين سبيسيال ايليت سادة غامق 250 جرام", "بليدج سبراي ملمع اثاث برتقال 300 مل", "كلوريل كلور للملابس وردي 2 كيلو", "النجار فريك 500 جرام", "النجار فاصوليا 500 جرام", "كوكو لافرز بسكويت ويفر شوكولاته وبندق 8 قطع", "حياة مياه شرب طبيعية 6 لتر", "كيت كات شوكولاته 2 قطعة 4+1 هدية", "البوادي حلاوة طحينية بالشوكولاته 275 جرام", "كلاسي مناديل مبللة لازالة المكياج 20 منديل", "صن شاين سردين حار فى زيت نباتى ومحلول ملحي 125 جرام", "تريسيمي بوتنيكس بلسم بحليب جوز الهند وخلاصة الصبار 200 مل", "دوف شامبو اصلاح مكثف 400 مل + دوف بديل الزيت اصلاح مكثف 300 مل", "جبن سلطة ميلانو سوسيس بالكاتشب 250 جرام", "ابو عوف قهوة تركي غامق محوج 100 جرام", "ابو عوف قهوة تركي وسط محوج 100 جرام", "الضيافة بهارات حواوشى 60 جرام", "الضيافة بهارات شاورما 70 جرام", "الضيافة بهارات لحوم وخضراوات 60 جرام", "الضيافة ثوم بودر 70 جرام", "سانيتا مناديل متعددة الاستخدامات ماكسي رول اكس لارج", "سانيتا مناديل متعددة الاستخدامات ماكسي رول اكس اكس لارج", "نسكافيه جولد كابتشينو كراميل ظرف 21جرام", "نسكافيه جولد موكا ظرف 21جرام", "النجار قمح مقشور 500جرام", "الضيافة فلفل احمر حار 70 جرام", "الضيافة سبع بهارات 70 جرام", "الضيافه زنجبيل وليمون 70 جرام", "الضيافة روزمارى 40جرام", "النجار مسحوق سحلب مع المكسرات 200 جرام", "دريم فانيليا بالبرتقال 5 جرام", "صن بايتس بطعم البيتزا الايطالية", "رودس جبنة فيتا جبنة قديمة 500جرام", "القصيم تمر نصف جاف بالنوى 450 جرام", "برافو فراخ شواية 5جنيه", "بونا شيف بيكنج بودر+محسن كيك 20جرام", "عافية زيت ذرة بالأوميجا 800 مل", "سيجنال معجون اسنان 120مل +سيجنال كومبليت بالفحم 50مل", "ديفا سائل تنظيف اليدين مضاد للبكتريا حساس 480مل", "ديفا سائل تنظيف اليدين مضاد للبكتريا رفاهية فائقة 480 مل", "الضيافة كركم مطحون 70جرام", "الفؤاد نشا ذرة 150جرام"];
        foreach ($producsts as $key => $value) {
            if ($value->translate('ar')) {
                if (in_array($value->translate('ar')->name, $names)) {
                    $value->image = null;
                    $value->update();
                }
            }
        }
        dd(33);
        $pos_categories = DB::connection('mysql2')->table('Categories')->get();
        foreach ($pos_categories as $key => $cat) {
            if ($cat->image) {
                $image = $cat->image;
                $image = ImageCreator::make($image);
                if ($image->mime()) {
                    $type = explode('/', $image->mime());
                    $name = time() . rand(1111, 9999) . '.' . $type[1];
                }
                $image->save('public/images/category/' . $name);
                $image->destroy();
                $data['image'] = $name;
            }
            $data['en'] = ['name' => $cat->EnglishCategoryName];
            $data['ar'] = ['name' => $cat->ArabicCategoryName];
            $data['is_active'] = true;
            if ($cat->ParentCategoryID) {
                $data['parent_id'] = Category::where('pos_id', $cat->ParentCategoryID)->first()->id;
            }
            $data['pos_id'] = $cat->CategoryID;
            $new = Category::create($data);
            $parents[$cat->CategoryID] = $new->id;
        }


        $pos_products = DB::connection('mysql2')->table('SalesItems')->get();
        foreach ($pos_products as $key => $product) {
            if (!$product->EnglishItemName) {
                continue;
            }
            $data['en'] = [
                'name' => $product->EnglishItemName,
            ];
            $data['ar'] = [
                'name' => $product->ArabicItemName,
            ];
            $data['type'] = 'standard';
            $data['code'] = $product->Barcode;
            $data['barcode_symbology'] = 'C128';
            $category = Category::where('pos_id', $product->CategoryID)->first();
            $data['category_id'] = $category ? $category->id : 0;
            $data['unit_id'] = 1;
            $data['sale_unit_id'] = 1;
            $data['purchase_unit_id'] = 1;
            $data['tax_method'] = 1;
            $data['is_active'] = true;
            $data['cost'] = $product->Price;
            $data['price'] = $product->Price;
            $data['app_price'] = $product->Price;
            $data['qty'] = "0.00";

            if ($product->image) {
                $image = $product->image;
                $image = \Image::make($image);
                if ($image->mime()) {
                    $type = explode('/', $image->mime());
                    $name = $product->Barcode . rand(1111, 9999) . '.' . $type[1];
                }
                $image->save('public/images/product/' . $name);
                $image->destroy();
                $data['image'] = $name;
                // $img->save(public_path('images/').$name);
            }
            $lims_product_data = Product::create($data);
        }
        dd('done');
    }

    public function sendsms()
    {
        $apiToken = "kisob-67d9542b-3e4e-45ed-b8aa-3061cc79d9e9";
        $sid = "KISOBBRANDAPI";
        $msisdn = "01741202865";
        $messageBody = "Hello Ashfaq! This is a test sms. Do not reply.";
        $csmsId = "2934fe343";

        $params = [
            "api_token" => $apiToken,
            "sid" => $sid,
            "msisdn" => $msisdn,
            "sms" => $messageBody,
            "csms_id" => $csmsId
        ];

        $url = "https://smsplus.sslwireless.com/api/v3/send-sms";
        $params = json_encode($params);

        $ch = curl_init(); // Initialize cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($params),
            'accept:application/json'
        ));

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;

        //return 'send sms';
    }

    public function index()
    {

        //return phpinfo();
        //return Printing::printers();
        /*$printerId = '69993185';
        $content = 'Hello world';
        $printJob = Printing::newPrintTask()
        ->printer($printerId)
        ->content($content)
        ->send();*/
        //return 'printed successfully';
        /*$connector = new NetworkPrintConnector("192.168.1.87",9100);
        //return dd($connector);
        $printer = new Printer($connector);
        try {
            $printer -> text("Hello World");
        } finally {
            $printer -> close();
        }*/
        if (Auth::user()->role_id == 5) {
            $customer = Customer::select('id', 'points')->where('user_id', Auth::id())->first();
            $lims_sale_data = Sale::with('warehouse')->where('customer_id', $customer->id)->orderBy('created_at', 'desc')->get();
            $lims_payment_data = DB::table('payments')
                ->join('sales', 'payments.sale_id', '=', 'sales.id')
                ->where('customer_id', $customer->id)
                ->select('payments.*', 'sales.reference_no as sale_reference')
                ->orderBy('payments.created_at', 'desc')
                ->get();
            $lims_quotation_data = Quotation::with('biller', 'customer', 'supplier', 'user')->orderBy('id', 'desc')->where('customer_id', $customer->id)->orderBy('created_at', 'desc')->get();

            $lims_return_data = Returns::with('warehouse', 'customer', 'biller')->where('customer_id', $customer->id)->orderBy('created_at', 'desc')->get();
            $lims_reward_point_setting_data = RewardPointSetting::select('per_point_amount')->latest()->first();
            return view('customer_index', compact('customer', 'lims_sale_data', 'lims_payment_data', 'lims_quotation_data', 'lims_return_data', 'lims_reward_point_setting_data'));
        }

        $start_date = date("Y") . '-' . date("m") . '-' . '01';
        $end_date = date("Y") . '-' . date("m") . '-' . date('t', mktime(0, 0, 0, date("m"), 1, date("Y")));
        $yearly_sale_amount = [];

        $general_setting = GeneralSetting::latest()->first();
      
        $cashiers = User::whereIn("role_id", [4, 6])->with('cashierLogs')->orderby('id', 'DESC')->get();
        foreach ($cashiers as $key => $cashier) {
            $log = $cashier->cashierLogs()->latest()->first();
            $cashier->log = $log;
            $cashier->active = $log && !$log->time_closed ? "مفتوح" : "مغلق";
            $cashier->total_sale_amount = $log ? $cashier->shiftSales()->where('cashier_log_id', $log->id)->sum('grand_total') : 0;
            $cashier->role_name = Role::find($cashier->role_id)->name;
        }
        $shift = Shift::whereNull('time_closed')->first();
        $mytime = Carbon::now();
        // dd($cashiers);
        //return $month;
        return view('index', compact('mytime', 'shift', 'cashiers'));
    }

    public function dashboardFilter($start_date, $end_date)
    {
        $general_setting = DB::table('general_settings')->latest()->first();
        if (Auth::user()->role_id > 2 && $general_setting->staff_access == 'own') {
            $product_sale_data = Sale::join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                ->select(DB::raw('product_sales.product_id, product_sales.product_batch_id, sum(product_sales.qty) as sold_qty, sum(product_sales.total) as sold_amount'))
                ->where('sales.user_id', Auth::id())
                ->whereDate('product_sales.created_at', '>=', $start_date)
                ->whereDate('product_sales.created_at', '<=', $end_date)
                ->groupBy('product_sales.product_id', 'product_sales.product_batch_id')
                ->get();

            $product_cost = 0;
            foreach ($product_sale_data as $key => $product_sale) {
                $product_data = Product::select('type', 'product_list', 'variant_list', 'qty_list')->find($product_sale->product_id);
                if ($product_data->type == 'combo') {
                    $product_list = explode(",", $product_data->product_list);
                    if ($product_data->variant_list)
                        $variant_list = explode(",", $product_data->variant_list);
                    else
                        $variant_list = [];
                    $qty_list = explode(",", $product_data->qty_list);

                    foreach ($product_list as $index => $product_id) {
                        if (count($variant_list) && $variant_list[$index]) {
                            $product_purchase_data = ProductPurchase::where([
                                ['product_id', $product_id],
                                ['variant_id', $variant_list[$index]]
                            ])->get();
                        } else
                            $product_purchase_data = ProductPurchase::where('product_id', $product_id)->get();

                        $purchased_qty = 0;
                        $purchased_amount = 0;
                        $sold_qty = $product_sale->sold_qty * $qty_list[$index];

                        foreach ($product_purchase_data as $product_purchase) {
                            $purchased_qty += $product_purchase->qty;
                            $purchased_amount += $product_purchase->total;
                            if ($purchased_qty >= $sold_qty) {
                                $qty_diff = $purchased_qty - $sold_qty;
                                $unit_cost = $product_purchase->total / $product_purchase->qty;
                                $purchased_amount -= ($qty_diff * $unit_cost);
                                break;
                            }
                        }
                        $product_cost += $purchased_amount;
                    }
                } else {
                    if ($product_sale->product_batch_id)
                        $product_purchase_data = ProductPurchase::where([
                            ['product_id', $product_sale->product_id],
                            ['product_batch_id', $product_sale->product_batch_id]
                        ])->get();
                    else
                        $product_purchase_data = ProductPurchase::where('product_id', $product_sale->product_id)->get();

                    $purchased_qty = 0;
                    $purchased_amount = 0;
                    $sold_qty = $product_sale->sold_qty;
                    foreach ($product_purchase_data as $key => $product_purchase) {
                        $purchased_qty += $product_purchase->qty;
                        $purchased_amount += $product_purchase->total;
                        if ($purchased_qty >= $sold_qty) {
                            $qty_diff = $purchased_qty - $sold_qty;
                            $unit_cost = $product_purchase->total / $product_purchase->qty;
                            $purchased_amount -= ($qty_diff * $unit_cost);
                            break;
                        }
                    }
                    $product_cost += $purchased_amount;
                }
            }

            $revenue = Sale::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->where('user_id', Auth::id())->sum('grand_total');
            $return = Returns::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->where('user_id', Auth::id())->sum('grand_total');
            $purchase_return = ReturnPurchase::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->where('user_id', Auth::id())->sum('grand_total');
            $revenue -= $return;
            $profit = $revenue + $purchase_return - $product_cost;

            $data[0] = $revenue;
            $data[1] = $return;
            $data[2] = $profit;
            $data[3] = $purchase_return;
        } else {
            $product_sale_data = Product_Sale::select(DB::raw('product_id, product_batch_id, sum(qty) as sold_qty, sum(total) as sold_amount'))->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->groupBy('product_id', 'product_batch_id')->get();

            $product_cost = 0;
            foreach ($product_sale_data as $key => $product_sale) {
                $product_data = Product::select('type', 'product_list', 'variant_list', 'qty_list')->find($product_sale->product_id);
                if ($product_data->type == 'combo') {
                    $product_list = explode(",", $product_data->product_list);
                    if ($product_data->variant_list)
                        $variant_list = explode(",", $product_data->variant_list);
                    else
                        $variant_list = [];
                    $qty_list = explode(",", $product_data->qty_list);

                    foreach ($product_list as $index => $product_id) {
                        if (count($variant_list) && $variant_list[$index]) {
                            $product_purchase_data = ProductPurchase::where([
                                ['product_id', $product_id],
                                ['variant_id', $variant_list[$index]]
                            ])->get();
                        } else
                            $product_purchase_data = ProductPurchase::where('product_id', $product_id)->get();

                        $purchased_qty = 0;
                        $purchased_amount = 0;
                        $sold_qty = $product_sale->sold_qty * $qty_list[$index];

                        foreach ($product_purchase_data as $product_purchase) {
                            $purchased_qty += $product_purchase->qty;
                            $purchased_amount += $product_purchase->total;
                            if ($purchased_qty >= $sold_qty) {
                                $qty_diff = $purchased_qty - $sold_qty;
                                $unit_cost = $product_purchase->total / $product_purchase->qty;
                                $purchased_amount -= ($qty_diff * $unit_cost);
                                break;
                            }
                        }
                        $product_cost += $purchased_amount;
                    }
                } else {
                    if ($product_sale->product_batch_id)
                        $product_purchase_data = ProductPurchase::where([
                            ['product_id', $product_sale->product_id],
                            ['product_batch_id', $product_sale->product_batch_id]
                        ])->get();
                    else
                        $product_purchase_data = ProductPurchase::where('product_id', $product_sale->product_id)->get();

                    $purchased_qty = 0;
                    $purchased_amount = 0;
                    $sold_qty = $product_sale->sold_qty;
                    foreach ($product_purchase_data as $key => $product_purchase) {
                        $purchased_qty += $product_purchase->qty;
                        $purchased_amount += $product_purchase->total;
                        if ($purchased_qty >= $sold_qty) {
                            $qty_diff = $purchased_qty - $sold_qty;
                            $unit_cost = $product_purchase->total / $product_purchase->qty;
                            $purchased_amount -= ($qty_diff * $unit_cost);
                            break;
                        }
                    }
                    $product_cost += $purchased_amount;
                }
            }

            $revenue = Sale::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('grand_total');
            $return = Returns::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('grand_total');
            $purchase_return = ReturnPurchase::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('grand_total');
            $revenue -= $return;
            $profit = $revenue + $purchase_return - $product_cost;

            $data[0] = $revenue;
            $data[1] = $return;
            $data[2] = $profit;
            $data[3] = $purchase_return;
        }

        return $data;
    }
    public function generalReport()
    {

        //return phpinfo();
        //return Printing::printers();
        /*$printerId = '69993185';
        $content = 'Hello world';
        $printJob = Printing::newPrintTask()
        ->printer($printerId)
        ->content($content)
        ->send();*/
        //return 'printed successfully';
        /*$connector = new NetworkPrintConnector("192.168.1.87",9100);
        //return dd($connector);
        $printer = new Printer($connector);
        try {
            $printer -> text("Hello World");
        } finally {
            $printer -> close();
        }*/
        if (Auth::user()->role_id == 5) {
            $customer = Customer::select('id', 'points')->where('user_id', Auth::id())->first();
            $lims_sale_data = Sale::with('warehouse')->where('customer_id', $customer->id)->orderBy('created_at', 'desc')->get();
            $lims_payment_data = DB::table('payments')
                ->join('sales', 'payments.sale_id', '=', 'sales.id')
                ->where('customer_id', $customer->id)
                ->select('payments.*', 'sales.reference_no as sale_reference')
                ->orderBy('payments.created_at', 'desc')
                ->get();
            $lims_quotation_data = Quotation::with('biller', 'customer', 'supplier', 'user')->orderBy('id', 'desc')->where('customer_id', $customer->id)->orderBy('created_at', 'desc')->get();

            $lims_return_data = Returns::with('warehouse', 'customer', 'biller')->where('customer_id', $customer->id)->orderBy('created_at', 'desc')->get();
            $lims_reward_point_setting_data = RewardPointSetting::select('per_point_amount')->latest()->first();
            return view('customer_index', compact('customer', 'lims_sale_data', 'lims_payment_data', 'lims_quotation_data', 'lims_return_data', 'lims_reward_point_setting_data'));
        }

        $start_date = date("Y") . '-' . date("m") . '-' . '01';
        $end_date = date("Y") . '-' . date("m") . '-' . date('t', mktime(0, 0, 0, date("m"), 1, date("Y")));
        $yearly_sale_amount = [];

        $general_setting = GeneralSetting::latest()->first();
        if (Auth::user()->role_id > 2 && $general_setting->staff_access == 'own') {
            $product_sale_data = Sale::join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                ->select(DB::raw('product_sales.product_id, product_sales.product_batch_id, sum(product_sales.qty) as sold_qty, sum(product_sales.total) as sold_amount'))
                ->where('sales.user_id', Auth::id())
                ->whereDate('product_sales.created_at', '>=', $start_date)
                ->whereDate('product_sales.created_at', '<=', $end_date)
                ->groupBy('product_sales.product_id', 'product_sales.product_batch_id')
                ->get();

            $product_cost = 0;
            foreach ($product_sale_data as $key => $product_sale) {
                $product_data = Product::select('type', 'product_list', 'variant_list', 'qty_list')->find($product_sale->product_id);
                if (!$product_data) {
                    return;
                }
                if ($product_data->type == 'combo') {
                    $product_list = explode(",", $product_data->product_list);
                    if ($product_data->variant_list)
                        $variant_list = explode(",", $product_data->variant_list);
                    else
                        $variant_list = [];
                    $qty_list = explode(",", $product_data->qty_list);

                    foreach ($product_list as $index => $product_id) {
                        if (count($variant_list) && $variant_list[$index]) {
                            $product_purchase_data = ProductPurchase::where([
                                ['product_id', $product_id],
                                ['variant_id', $variant_list[$index]]
                            ])->get();
                        } else
                            $product_purchase_data = ProductPurchase::where('product_id', $product_id)->get();

                        $purchased_qty = 0;
                        $purchased_amount = 0;
                        $sold_qty = $product_sale->sold_qty * $qty_list[$index];

                        foreach ($product_purchase_data as $product_purchase) {
                            $purchased_qty += $product_purchase->qty;
                            $purchased_amount += $product_purchase->total;
                            if ($purchased_qty >= $sold_qty) {
                                $qty_diff = $purchased_qty - $sold_qty;
                                $unit_cost = $product_purchase->total / $product_purchase->qty;
                                $purchased_amount -= ($qty_diff * $unit_cost);
                                break;
                            }
                        }
                        $product_cost += $purchased_amount;
                    }
                } else {
                    if ($product_sale->product_batch_id)
                        $product_purchase_data = ProductPurchase::where([
                            ['product_id', $product_sale->product_id],
                            ['product_batch_id', $product_sale->product_batch_id]
                        ])->get();
                    else
                        $product_purchase_data = ProductPurchase::where('product_id', $product_sale->product_id)->get();

                    $purchased_qty = 0;
                    $purchased_amount = 0;
                    $sold_qty = $product_sale->sold_qty;
                    foreach ($product_purchase_data as $key => $product_purchase) {
                        $purchased_qty += $product_purchase->qty;
                        $purchased_amount += $product_purchase->total;
                        if ($purchased_qty >= $sold_qty) {
                            $qty_diff = $purchased_qty - $sold_qty;
                            $unit_cost = $product_purchase->total / $product_purchase->qty;
                            $purchased_amount -= ($qty_diff * $unit_cost);
                            break;
                        }
                    }
                    $product_cost += $purchased_amount;
                }
            }

            $revenue = Sale::whereDate('created_at', '>=', $start_date)->where('user_id', Auth::id())->whereDate('created_at', '<=', $end_date)->sum('grand_total');
            $return = Returns::whereDate('created_at', '>=', $start_date)->where('user_id', Auth::id())->whereDate('created_at', '<=', $end_date)->sum('grand_total');
            $purchase_return = ReturnPurchase::whereDate('created_at', '>=', $start_date)->where('user_id', Auth::id())->whereDate('created_at', '<=', $end_date)->sum('grand_total');
            $revenue = $revenue - $return;
            $purchase = Purchase::whereDate('created_at', '>=', $start_date)->where('user_id', Auth::id())->whereDate('created_at', '<=', $end_date)->sum('grand_total');
            $profit = $revenue + $purchase_return - $product_cost;
            $expense = Expense::whereDate('created_at', '>=', $start_date)->where('user_id', Auth::id())->whereDate('created_at', '<=', $end_date)->sum('amount');
            $recent_sale = Sale::orderBy('id', 'desc')->where('user_id', Auth::id())->take(5)->get();
            $recent_purchase = Purchase::orderBy('id', 'desc')->where('user_id', Auth::id())->take(5)->get();
            $recent_quotation = Quotation::orderBy('id', 'desc')->where('user_id', Auth::id())->take(5)->get();
            $recent_payment = Payment::orderBy('id', 'desc')->where('user_id', Auth::id())->take(5)->get();
        } else {
            $product_sale_data = Product_Sale::select(DB::raw('product_id, product_batch_id, sum(qty) as sold_qty, sum(total) as sold_amount'))->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->groupBy('product_id', 'product_batch_id')->get();

            $product_cost = 0;
            foreach ($product_sale_data as $key => $product_sale) {
                $product_data = Product::select('type', 'product_list', 'variant_list', 'qty_list')->find($product_sale->product_id);
                if ($product_data && $product_data->type == 'combo') {
                    $product_list = explode(",", $product_data->product_list);
                    if ($product_data->variant_list)
                        $variant_list = explode(",", $product_data->variant_list);
                    else
                        $variant_list = [];
                    $qty_list = explode(",", $product_data->qty_list);

                    foreach ($product_list as $index => $product_id) {
                        if (count($variant_list) && $variant_list[$index]) {
                            $product_purchase_data = ProductPurchase::where([
                                ['product_id', $product_id],
                                ['variant_id', $variant_list[$index]]
                            ])->get();
                        } else
                            $product_purchase_data = ProductPurchase::where('product_id', $product_id)->get();

                        $purchased_qty = 0;
                        $purchased_amount = 0;
                        $sold_qty = $product_sale->sold_qty * $qty_list[$index];

                        foreach ($product_purchase_data as $product_purchase) {
                            $purchased_qty += $product_purchase->qty;
                            $purchased_amount += $product_purchase->total;
                            if ($purchased_qty >= $sold_qty) {
                                $qty_diff = $purchased_qty - $sold_qty;
                                $unit_cost = $product_purchase->total / $product_purchase->qty;
                                $purchased_amount -= ($qty_diff * $unit_cost);
                                break;
                            }
                        }
                        $product_cost += $purchased_amount;
                    }
                } else {
                    if ($product_sale->product_batch_id)
                        $product_purchase_data = ProductPurchase::where([
                            ['product_id', $product_sale->product_id],
                            ['product_batch_id', $product_sale->product_batch_id]
                        ])->get();
                    else
                        $product_purchase_data = ProductPurchase::where('product_id', $product_sale->product_id)->get();

                    $purchased_qty = 0;
                    $purchased_amount = 0;
                    $sold_qty = $product_sale->sold_qty;
                    foreach ($product_purchase_data as $key => $product_purchase) {
                        $purchased_qty += $product_purchase->qty;
                        $purchased_amount += $product_purchase->total;
                        if ($purchased_qty >= $sold_qty) {
                            $qty_diff = $purchased_qty - $sold_qty;
                            $unit_cost = $product_purchase->total / $product_purchase->qty;
                            $purchased_amount -= ($qty_diff * $unit_cost);
                            break;
                        }
                    }
                    $product_cost += $purchased_amount;
                }
            }

            $revenue = Sale::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('grand_total');
            $return = Returns::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('grand_total');
            $purchase_return = ReturnPurchase::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('grand_total');
            $revenue = $revenue - $return;
            $purchase = Purchase::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('grand_total');
            $profit = $revenue + $purchase_return - $product_cost;
            $expense = Expense::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
            $recent_sale = Sale::orderBy('id', 'desc')->take(5)->get();
            $recent_purchase = Purchase::orderBy('id', 'desc')->take(5)->get();
            $recent_quotation = Quotation::orderBy('id', 'desc')->take(5)->get();
            $recent_payment = Payment::orderBy('id', 'desc')->take(5)->get();
        }

        $best_selling_qty = Product_Sale::select(DB::raw('product_id, sum(qty) as sold_qty'))->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->groupBy('product_id')->orderBy('sold_qty', 'desc')->take(5)->get();

        $yearly_best_selling_qty = Product_Sale::select(DB::raw('product_id, sum(qty) as sold_qty'))->whereDate('created_at', '>=', date("Y") . '-01-01')->whereDate('created_at', '<=', date("Y") . '-12-31')->groupBy('product_id')->orderBy('sold_qty', 'desc')->take(5)->get();

        $yearly_best_selling_price = Product_Sale::select(DB::raw('product_id, sum(total) as total_price'))->whereDate('created_at', '>=', date("Y") . '-01-01')->whereDate('created_at', '<=', date("Y") . '-12-31')->groupBy('product_id')->orderBy('total_price', 'desc')->take(5)->get();

        //cash flow of last 6 months
        $start = strtotime(date('Y-m-01', strtotime('-6 month', strtotime(date('Y-m-d')))));
        $end = strtotime(date('Y-m-' . date('t', mktime(0, 0, 0, date("m"), 1, date("Y")))));

        while ($start < $end) {
            $start_date = date("Y-m", $start) . '-' . '01';
            $end_date = date("Y-m", $start) . '-' . date('t', mktime(0, 0, 0, date("m", $start), 1, date("Y", $start)));

            if (Auth::user()->role_id > 2 && $general_setting->staff_access == 'own') {
                $recieved_amount = Payment::whereNotNull('sale_id')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->where('user_id', Auth::id())->sum('amount');
                $sent_amount = Payment::whereNotNull('purchase_id')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->where('user_id', Auth::id())->sum('amount');
                $return_amount = Returns::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->where('user_id', Auth::id())->sum('grand_total');
                $purchase_return_amount = ReturnPurchase::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->where('user_id', Auth::id())->sum('grand_total');
                $expense_amount = Expense::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->where('user_id', Auth::id())->sum('amount');
                $payroll_amount = Payroll::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->where('user_id', Auth::id())->sum('amount');
            } else {
                $recieved_amount = Payment::whereNotNull('sale_id')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
                $sent_amount = Payment::whereNotNull('purchase_id')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
                $return_amount = Returns::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('grand_total');
                $purchase_return_amount = ReturnPurchase::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('grand_total');
                $expense_amount = Expense::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
                $payroll_amount = Payroll::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
            }
            $sent_amount = $sent_amount + $return_amount + $expense_amount + $payroll_amount;

            $payment_recieved[] = number_format((float)($recieved_amount + $purchase_return_amount), 2, '.', '');
            $payment_sent[] = number_format((float)$sent_amount, 2, '.', '');
            $month[] = date("F", strtotime($start_date));
            $start = strtotime("+1 month", $start);
        }
        // yearly report
        $start = strtotime(date("Y") . '-01-01');
        $end = strtotime(date("Y") . '-12-31');
        while ($start < $end) {
            $start_date = date("Y") . '-' . date('m', $start) . '-' . '01';
            $end_date = date("Y") . '-' . date('m', $start) . '-' . date('t', mktime(0, 0, 0, date("m", $start), 1, date("Y", $start)));
            if (Auth::user()->role_id > 2 && $general_setting->staff_access == 'own') {
                $sale_amount = Sale::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->where('user_id', Auth::id())->sum('grand_total');
                $purchase_amount = Purchase::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->where('user_id', Auth::id())->sum('grand_total');
            } else {
                $sale_amount = Sale::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('grand_total');
                $purchase_amount = Purchase::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('grand_total');
            }
            $yearly_sale_amount[] = number_format((float)$sale_amount, 2, '.', '');
            $yearly_purchase_amount[] = number_format((float)$purchase_amount, 2, '.', '');
            $start = strtotime("+1 month", $start);
        }
        $cashiers = User::whereIn("role_id", [4, 6])->with('cashierLogs')->orderby('id', 'DESC')->get();
        foreach ($cashiers as $key => $cashier) {
            $log = $cashier->cashierLogs()->latest()->first();
            $cashier->log = $log;
            $cashier->active = $log && !$log->time_closed ? "مفتوح" : "مغلق";
            $cashier->total_sale_amount = $log ? $cashier->shiftSales()->where('cashier_log_id', $log->id)->sum('grand_total') : 0;
            $cashier->role_name = Role::find($cashier->role_id)->name;
        }
        $shift = Shift::whereNull('time_closed')->first();
        $mytime = Carbon::now();
        // dd($cashiers);
        //return $month;
        return view('index-report', compact('mytime', 'revenue', 'shift', 'cashiers', 'purchase', 'expense', 'return', 'purchase_return', 'profit', 'payment_recieved', 'payment_sent', 'month', 'yearly_sale_amount', 'yearly_purchase_amount', 'recent_sale', 'recent_purchase', 'recent_quotation', 'recent_payment', 'best_selling_qty', 'yearly_best_selling_qty', 'yearly_best_selling_price'));
    }


    public function myTransaction($year, $month)
    {
        $start = 1;
        $number_of_day = date('t', mktime(0, 0, 0, $month, 1, $year));
        while ($start <= $number_of_day) {
            if ($start < 10)
                $date = $year . '-' . $month . '-0' . $start;
            else
                $date = $year . '-' . $month . '-' . $start;
            $sale_generated[$start] = Sale::whereDate('created_at', $date)->where('user_id', Auth::id())->count();
            $sale_grand_total[$start] = Sale::whereDate('created_at', $date)->where('user_id', Auth::id())->sum('grand_total');
            $purchase_generated[$start] = Purchase::whereDate('created_at', $date)->where('user_id', Auth::id())->count();
            $purchase_grand_total[$start] = Purchase::whereDate('created_at', $date)->where('user_id', Auth::id())->sum('grand_total');
            $quotation_generated[$start] = Quotation::whereDate('created_at', $date)->where('user_id', Auth::id())->count();
            $quotation_grand_total[$start] = Quotation::whereDate('created_at', $date)->where('user_id', Auth::id())->sum('grand_total');
            $start++;
        }
        $start_day = date('w', strtotime($year . '-' . $month . '-01')) + 1;
        $prev_year = date('Y', strtotime('-1 month', strtotime($year . '-' . $month . '-01')));
        $prev_month = date('m', strtotime('-1 month', strtotime($year . '-' . $month . '-01')));
        $next_year = date('Y', strtotime('+1 month', strtotime($year . '-' . $month . '-01')));
        $next_month = date('m', strtotime('+1 month', strtotime($year . '-' . $month . '-01')));
        return view('user.my_transaction', compact('start_day', 'year', 'month', 'number_of_day', 'prev_year', 'prev_month', 'next_year', 'next_month', 'sale_generated', 'sale_grand_total', 'purchase_generated', 'purchase_grand_total', 'quotation_generated', 'quotation_grand_total'));
    }
}
