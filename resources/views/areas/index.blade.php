@extends('layout.main') @section('content')
@if($errors->has('coupon_no'))
<div class="alert alert-danger alert-dismissible text-center">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ $errors->first('coupon_no') }}</div>
@endif
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section>
    <div class="container-fluid">
        <button class="btn btn-info" data-toggle="modal" data-target="#create-modal"><i class="dripicons-plus"></i>اضف منطقة</button>
    </div>
    <div class="table-responsive">
        <table id="coupon-table" class="table">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>اسم المنطقة</th>
                    <th>سعر التوصيل</th>
                    <th class="not-exported">{{trans('file.action')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lims_coupon_all as $key=>$coupon)
                <tr data-id="{{$coupon->id}}">
                    <td>{{$key}}</td>
                    <td>{{ $coupon->name_ar }}</td>
                    <td>{{ $coupon->fee }}</td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{trans('file.action')}}
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li><button type="button" data-id="{{$coupon->id}}" data-name_ar="{{$coupon->name_ar}}" data-name_en="{{$coupon->name_en}}" data-fee="{{$coupon->fee}}"  class="edit-btn btn btn-link" data-toggle="modal" data-target="#editModal"><i class="dripicons-document-edit"></i> {{trans('file.edit')}}</button></li>
                                {{ Form::open(['route' => ['coupons.destroy', $coupon->id], 'method' => 'DELETE'] ) }}
                                <li>
                                    <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> {{trans('file.delete')}}</button>
                                </li>
                                {{ Form::close() }}
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="tfoot active">
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tfoot>
        </table>
    </div>
</section>

<div id="create-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">اضف منطقة</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
              <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                {!! Form::open(['route' => 'areas.store', 'method' => 'post']) !!}
                  <div class="row">
                      <div class="col-md-6 form-group">
                          <label>الاسم الانجليزي*</label>
                          <div class="input-group">
                              {{Form::text('name_en',null,array('required' => 'required', 'class' => 'form-control'))}}
                          </div>
                      </div>
                      <div class="col-md-6 form-group">
                            <label>الاسم العربي*</label>
                            <div class="input-group">
                                {{Form::text('name_ar',null,array('required' => 'required', 'class' => 'form-control'))}}
                            </div>
                      </div>
                      <div class="col-md-6 form-group">
                            <label>سعر التوصيل*</label>
                            <div class="input-group">
                                {{Form::text('fee',null,array('required' => 'required', 'class' => 'form-control'))}}
                            </div>
                      </div>
                  </div>
                  <div class="form-group">
                      <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                  </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
  <div role="document" class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 id="exampleModalLabel" class="modal-title">تحديث المنطقة</h5>
              <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
          </div>
          <div class="modal-body">
            <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
              {!! Form::open(['route' => ['areas.update', 1], 'method' => 'put']) !!}
              <input type="hidden" name="area_id"/>
              <div class="row">
                <div class="col-md-6 form-group">
                    <label>الاسم الانجليزي*</label>
                    <div class="input-group">
                        {{Form::text('name_en',null,array('required' => 'required', 'class' => 'form-control'))}}
                    </div>
                </div>
                <div class="col-md-6 form-group">
                      <label>الاسم العربي*</label>
                      <div class="input-group">
                          {{Form::text('name_ar',null,array('required' => 'required', 'class' => 'form-control'))}}
                      </div>
                </div>
                <div class="col-md-6 form-group">
                      <label>سعر التوصيل*</label>
                      <div class="input-group">
                          {{Form::text('fee',null,array('required' => 'required', 'class' => 'form-control'))}}
                      </div>
                </div>
            </div>
              <div class="form-group">
                  <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
              </div>
              {{ Form::close() }}
          </div>
      </div>
  </div>
</div>


@endsection

@push('scripts')
<script type="text/javascript">

    $("ul#sale").siblings('a').attr('aria-expanded','true');
    $("ul#sale").addClass("show");
    $("ul#sale #coupon-menu").addClass("active");

    var coupon_id = [];
    var user_verified = <?php echo json_encode(env('USER_VERIFIED')) ?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#create-modal .expired_date").val($.datepicker.formatDate('yy-mm-dd', new Date()));
    $(".minimum-amount").hide();

    $("#create-modal select[name='type']").on("change", function() {
      if ($(this).val() == 'fixed') {
          $("#create-modal .minimum-amount").show();
          $("#create-modal .minimum-amount").prop('required',true);
          $("#create-modal .icon-text").text('$');
      }
      else {
          $("#create-modal .minimum-amount").hide();
          $("#create-modal .minimum-amount").prop('required',false);
          $("#create-modal .icon-text").text('%');
      }
    });

    $(document).on("change", "#editModal select[name='type']", function() {
      alert('kire?');
      if ($(this).val() == 'fixed') {
          $("#editModal .minimum-amount").show();
          $("#editModal .minimum-amount").prop('required',true);
          $("#editModal .icon-text").text('$');
      }
      else {
          $("#editModal .minimum-amount").hide();
          $("#editModal .minimum-amount").prop('required',false);
          $("#editModal .icon-text").text('%');
      }
    });

    $(document).on("click", '#create-modal .genbutton', function(){
      $.get('coupons/gencode', function(data){
        $("input[name='code']").val(data);
      });
    });

    $(document).on("click", '#editModal .genbutton', function(){
      $.get('coupons/gencode', function(data){
        $("#editModal input[name='code']").val(data);
      });
    });

    $(document).ready(function() {
        $(document).on('click', '.edit-btn', function() {
            console.log($(this).data('name_ar'));
            $("#editModal input[name='name_ar']").val($(this).data('name_ar'));
            $("#editModal input[name='name_en']").val($(this).data('name_en'));
            $("#editModal input[name='area_id']").val($(this).data('id'));
            $("#editModal input[name='fee']").val($(this).data('fee'))
        });
    });

    var expired_date = $('.expired_date');
    expired_date.datepicker({
     format: "yyyy-mm-dd",
     startDate: "<?php echo date('Y-m-d'); ?>",
     autoclose: true,
     todayHighlight: true
     });

function confirmDelete() {
    if (confirm("Are you sure want to delete?")) {
        return true;
    }
    return false;
}

    var table = $('#coupon-table').DataTable( {
        responsive: true,
        fixedHeader: {
            header: true,
            footer: true
        },
        "order": [],
        'language': {
            'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
             "info":      '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{trans("file.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 3]
            },
            {
                'render': function(data, type, row, meta){
                    if(type === 'display'){
                        data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                    }

                   return data;
                },
                'checkboxes': {
                   'selectRow': true,
                   'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                },
                'targets': [0]
            }
        ],
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                }
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                }
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                }
            },
            {
                text: '<i title="delete" class="dripicons-cross"></i>',
                className: 'buttons-delete',
                action: function ( e, dt, node, config ) {
                    if(user_verified == '1') {
                        coupon_id.length = 0;
                        $(':checkbox:checked').each(function(i){
                            if(i){
                                coupon_id[i-1] = $(this).closest('tr').data('id');
                            }
                        });
                        if(coupon_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type:'POST',
                                url:'areas/deletebyselection',
                                data:{
                                    couponIdArray: coupon_id
                                },
                                success:function(data){
                                    alert(data);
                                }
                            });
                            dt.rows({ page: 'current', selected: true }).remove().draw(false);
                        }
                        else if(!coupon_id.length)
                            alert('No coupon is selected!');
                    }
                    else
                        alert('This feature is disable for demo!');
                }
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ]
    } );

</script>
@endpush
