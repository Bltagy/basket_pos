@extends('layout.main')
@section('content')
<!-- this portion is for demo only -->
<!-- <style type="text/css">

  nav.navbar a.menu-btn {
    padding: 12 !important;
  }
  .color-switcher {
      background-color: #fff;
      border: 1px solid #e5e5e5;
      border-radius: 2px;
      padding: 10px;
      position: fixed;
      top: 150px;
      transition: all 0.4s ease 0s;
      width: 150px;
      z-index: 99999;
  }
  .hide-color-switcher {
      right: -150px;
  }
  .show-color-switcher {
      right: -1px;
  }
  .color-switcher a.switcher-button {
      background: #fff;
      border-top: #e5e5e5;
      border-right: #e5e5e5;
      border-bottom: #e5e5e5;
      border-left: #e5e5e5;
      border-style: solid solid solid solid;
      border-width: 1px 1px 1px 1px;
      border-radius: 2px;
      color: #161616;
      cursor: pointer;
      font-size: 22px;
      width: 45px;
      height: 45px;
      line-height: 43px;
      position: absolute;
      top: 24px;
      left: -44px;
      text-align: center;
  }
  .color-switcher a.switcher-button i {
    line-height: 40px
  }
  .color-switcher .color-switcher-title {
      color: #666;
      padding: 0px 0 8px;
  }
  .color-switcher .color-switcher-title:after {
      content: "";
      display: block;
      height: 1px;
      margin: 14px 0 0;
      position: relative;
      width: 20px;
  }
  .color-switcher .color-list a.color {
      cursor: pointer;
      display: inline-block;
      height: 30px;
      margin: 10px 0 0 1px;
      width: 28px;
  }
  .purple-theme {
      background-color: #7c5cc4;
  }
  .green-theme {
      background-color: #1abc9c;
  }
  .blue-theme {
      background-color: #3498db;
  }
  .dark-theme {
      background-color: #34495e;
  }
</style>
<div class="color-switcher hide-color-switcher">
    <a class="switcher-button"><i class="fa fa-cog fa-spin"></i></a>
    <h5>{{trans('file.Theme')}}</h5>
    <div class="color-list">
        <a class="color purple-theme" title="purple" data-color="default.css"></a>
        <a class="color green-theme" title="green" data-color="green.css"></a>
        <a class="color blue-theme" title="blue" data-color="blue.css"></a>
        <a class="color dark-theme" title="dark" data-color="dark.css"></a>
    </div>
</div> -->
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
@endif
      <div class="row">
        <div class="container-fluid">
          <div class="col-md-12">
            <div class="brand-text float-left mt-4">
                <h3>{{trans('file.welcome')}} <span>{{Auth::user()->name}}</span> </h3>
            </div>
            <div class="filter-toggle btn-group">
              <button class="btn btn-secondary date-btn" data-start_date="{{date('Y-m-d')}}" data-end_date="{{date('Y-m-d')}}">{{trans('file.Today')}}</button>
              <button class="btn btn-secondary date-btn" data-start_date="{{date('Y-m-d', strtotime(' -7 day'))}}" data-end_date="{{date('Y-m-d')}}">{{trans('file.Last 7 Days')}}</button>
              <button class="btn btn-secondary date-btn active" data-start_date="{{date('Y').'-'.date('m').'-'.'01'}}" data-end_date="{{date('Y-m-d')}}">{{trans('file.This Month')}}</button>
              <button class="btn btn-secondary date-btn" data-start_date="{{date('Y').'-01'.'-01'}}" data-end_date="{{date('Y').'-12'.'-31'}}">{{trans('file.This Year')}}</button>
            </div>
          </div>
        </div>
      </div>
      <!-- Counts Section -->
      @php
        $user = auth()->user();
        $display = $user->role_id == 4 || $user->role_id == 6 || $user->role_id == 8 || $user->role_id == 11 ?" d-none":"";
        $role = Spatie\Permission\Models\Role::find($user->role_id);
      @endphp

@if ($role->hasPermissionTo('admin_code'))
    <section class="dashboard-counts">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 form-group">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>كود المشرف المؤقت :  <span style="text-align: right;font-size:xx-large">{{$supervisor_code}}</span></h4>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endif
      <section class="dashboard-counts{{$display}}">
        <div class="container-fluid">


          <div class="row">
            <div class="col-md-12 form-group">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h4>الكاشير</h4>
                <div class="right-column">
                  <div class="badge badge-primary h5 text-white" >
                    @if ($shift)
                    اليوم
                    {{$shift->date}}
                    فتح من الساعة
                    {{$shift->created_at->format('h:s A')}}
                    <a type="button" href="{{url('cashier-log/closeShift')}}" class="btn btn-danger">اغلاق اليوم</a>
                    @else
                    اليوم
                    {{$mytime->toDateString()}}
                    غير مفتوح
                    @endif

                    </div>
                </div>
              </div>
              <div class="table-responsive">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>الاسم</th>
                        <th>نوع</th>
                        <th>حالة الشيفت</th>
                        <th>تاريخ</th>
                        <th>المبلغ المستلم</th>
                        <th>اجمالي المبيعات</th>
                        <th>المبلغ المودر</th>
                        <th>اختصارات</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($cashiers as $key => $cashier)
                      <tr>
                        <td>{{$cashier->id}}</td>
                        <td>{{$cashier->name}}</td>
                        <td>{{ $cashier->role_name }}</td>
                        <td>{{ $cashier->active }}</td>
                        <td>{{ $cashier->log ? $cashier->log->date:"--" }}</td>
                        <td>{{ $cashier->log ? $cashier->log->amount_got:"0" }}</td>
                        <td>{{ $cashier->total_sale_amount}}</td>
                        <td>{{ $cashier->log ? $cashier->log->amount_deliver:"0" }}</td>
                        <td>
                          <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">اختصارات
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-left dropdown-default" user="menu">
                                <li>
                                	<a href="{{ route('cashierOrders.get', $cashier->id) }}" class="btn btn-link"><i class="dripicons-document-edit"></i>الاوردرات</a>
                                </li>
                                <li>
                                	<a href="{{ route('cashier-log.edit', $cashier->id) }}" class="btn btn-link"><i class="dripicons-document-edit"></i>الشيفتات</a>
                                </li>

                            </ul>
                        </div>
                        </td>
                        {{-- <td>{{$product->name}}<br>[{{$product->code}}]</td> --}}
                        {{-- <td>{{$sale->sold_qty}}</td> --}}
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                  <div class="row right m-3">
                  <a type="button" href="{{ route('general-report') }}" class="btn btn-primary">باقي التقارير</a>
                </div>
                </div>
            </div>

          </div>
        </div>


      </section>


@endsection

@push('scripts')
<script type="text/javascript">
    // Show and hide color-switcher
    $(".color-switcher .switcher-button").on('click', function() {
        $(".color-switcher").toggleClass("show-color-switcher", "hide-color-switcher", 300);
    });

    // Color Skins
    $('a.color').on('click', function() {
        /*var title = $(this).attr('title');
        $('#style-colors').attr('href', 'css/skin-' + title + '.css');
        return false;*/
        $.get('setting/general_setting/change-theme/' + $(this).data('color'), function(data) {
        });
        var style_link= $('#custom-style').attr('href').replace(/([^-]*)$/, $(this).data('color') );
        $('#custom-style').attr('href', style_link);
    });

    $(".date-btn").on("click", function() {
        $(".date-btn").removeClass("active");
        $(this).addClass("active");
        var start_date = $(this).data('start_date');
        var end_date = $(this).data('end_date');
        $.get('dashboard-filter/' + start_date + '/' + end_date, function(data) {
            dashboardFilter(data);
        });
    });

    function dashboardFilter(data){
        $('.revenue-data').hide();
        $('.revenue-data').html(parseFloat(data[0]).toFixed(2));
        $('.revenue-data').show(500);

        $('.return-data').hide();
        $('.return-data').html(parseFloat(data[1]).toFixed(2));
        $('.return-data').show(500);

        $('.profit-data').hide();
        $('.profit-data').html(parseFloat(data[2]).toFixed(2));
        $('.profit-data').show(500);

        $('.purchase_return-data').hide();
        $('.purchase_return-data').html(parseFloat(data[3]).toFixed(2));
        $('.purchase_return-data').show(500);
    }
</script>
@endpush
