@extends('layouts.app')

@section('content_header')
<h4>Master Item</h4>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="row mx-0 mb-4">
            <div class="col"></div>
            <div class="col-auto pe-0">
                <div class="row mx-0">
                    <div class="col-12 col-md-auto">
                        <select id="country-search" class="form-control select-searchable">
                            <option value="">Pilih Negara</option>
                            @foreach(country_lists() as $country)
                            <option value="{{$country->id}}">{{$country->country_code}} - {{$country->country_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-auto">
                        <div class="input-group">
                            <input id="text-search" type="text" class="form-control" placeholder="Search..">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button class="btn btn-primary" onclick="add_new();">Add New</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <table class="table table-bordered" id="data-table">
            <thead>
                <tr>
                    <th class="auto-width">No.</th>
                    <th>Kode Pick</th>
                    <th>Negara</th>
                    <th>Tahun</th>
                    <th>Nominal</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th class="auto-width">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@section('content_footer')
@include('components.common.paginator')
@endsection

@section('js')
<script>
    var max_row = 0;
    var curr_page = 1;
    $(document).ready(function(){
        search_process();
        $("form").on('submit', function(event){
            event.preventDefault();
        });

        $("#row_count, #text-search, #country-search").change(function(){
            search_process();
        });

        
    });

    function search_process(){
        var search = $("#text-search").val();
        var country = $("#country-search").val();
        var max_row = $("#row_count").val();
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("master.item.search")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'page':curr_page,
                'max_row':max_row,
                'search':search,
                'country':country
            },
            success : function(msg) {
                var rs = msg.data;
                show_data(rs["data"]);
                
                $(".pagination-links").html($(msg.pagination));
            },
            error     : function(xhr) {
                console.log(xhr);
            },
            complete : function(xhr,status){
                closeLoading();
            }
        });
    }

    function show_data(data){
        var rows = '';
        var _curr_page = curr_page;
        var page = (_curr_page * max_row) - max_row;
        $.each(data, function(x,y){
            var created = moment(y.created_at).format('DD MMM YYYY hh:mm:ss');
            var updated = '-';
            if(y.updated_at){
                updated = moment(y.updated_at).format('DD MMM YYYY hh:mm:ss');
            }
            rows += `
                <tr>
                    <td>`+(++page)+`.</td>
                    <td>`+y.item_code+`</td>
                    <td>`+y.country_name+`</td>
                    <td>`+y.tahun+`</td>
                    <td>`+numberWithCommas(y.nominal)+`</td>
                    <td>`+created+`</td>
                    <td>`+updated+`</td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-outline-primary d-flex align-items-center" onclick="edit_data(`+y.item_id+`)"><i class="bi bi-pencil me-2"></i>Edit</button>
                            <button class="btn btn-outline-danger d-flex align-items-center" onclick="delete_data(`+y.item_id+`)"><i class="bi bi-trash me-2"></i>Delete</button>
                        </div>
                    </td>
                </tr>
            `;
        });

        if(rows == ''){
            var length = $("#data-table thead th").length;
            rows = `
                <tr>
                    <td colspan="`+length+`">Data kosong</td>
                </tr>
            `
        }

        $("#data-table tbody").html(rows);
    }

    function add_new(){
        var form = $("#form_modal form");
        form.trigger('reset');

        $("#form_modal").modal('show');
    }

    function edit_data(id){
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("master.item.view")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'item_id':id,
            },
            success : function(msg) {
                console.log(msg);
                fill_form(msg);
                $("#form_modal").modal('show');
            },
            error     : function(xhr) {
                console.log(xhr);
            },
            complete : function(xhr,status){
                closeLoading();
            }
        })
    }

    function fill_form(data){
        var form = $("#form_modal form");
        form.trigger('reset');
        form.find("[name='item_id']").val(data.item_id);
        form.find("[name='item_code']").val(data.item_code)
        form.find("[name='kd_negara']").val(data.kd_negara)
        form.find("[name='tahun']").val(data.tahun)
        form.find("[name='nominal-text']").val(numberWithCommas(data.nominal))
        form.find("[name='nominal']").val(data.nominal)
    }

    function save_form(form){
        showLoading();
        var dtForm = $(form).serializeArray();
        $.ajax({
            type        : 'POST',
            url         : '{{route("master.item.upsert")}}',
            headers     : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType    : 'JSON',
            data        : dtForm,
            success     : function(msg) {
                $("#form_modal form").trigger("reset");
                $('#form_modal').modal('hide');
            },
            error       : function(xhr) {
                console.log(xhr);
            },
            complete    : function(xhr){
                closeLoading();
                search_process();
            }
        });
    }

    function delete_data(id){
        Swal.fire({
            title: "Apakah anda yakin mau menghapus data?",
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: "Yes",
            denyButtonText: `No`
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                showLoading();
                $.ajax({
                    type    : 'POST',
                    url     : '{{route("master.item.delete")}}',
                    headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    dataType: 'JSON',
                    data    : {
                        'item_id':id,
                    },
                    success : function(msg) {
                        Swal.fire("Saved!", "", "success");
                    },
                    error     : function(xhr) {
                        console.log(xhr);
                    },
                    complete : function(xhr,status){
                        closeLoading();
                    }
                });
            } else if (result.isDenied) {
                // Swal.fire("Changes are not saved", "", "info");
            }
        });
    }
</script>
@endsection

@section('footer')
<div class="modal fade" id="form_modal" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="formModalLabel">Data Item</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" tabindex="-1"></button>
            </div>
            <div class="modal-body">
                <form onsubmit="save_form(this);">
                    <input type="text" class="d-none" name="item_id">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Kode Pick</label>
                                <input type="text" class="form-control" name="item_code" required placeholder="Masukan Kode Item">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Negara</label>
                                <select class="form-control select-searchable" name="kd_negara" required>
                                    <option value="" disabled>Pilih Negara</option>
                                    @foreach(country_lists() as $country)
                                    <option value="{{$country->id}}">{{$country->country_code}} - {{$country->country_name}}</option>
                                    @endforeach 
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Tahun</label>
                                <select class="form-control select-searchable" name="tahun" required>
                                    <option value="" disabled>Pilih Tahun</option>
                                    @foreach(tahun_lists() as $tahun)
                                    <option value="{{$tahun}}">{{$tahun}}</option>
                                    @endforeach 
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Nominal</label>
                                <input type="text" data-target="nominal" class="form-control comma-separated" name="nominal-text" required placeholder="Masukan Nominal">
                                <input id="nominal" type="hidden" name="nominal" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="d-none" id="SubmitBtn"></button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <label for="SubmitBtn" class="btn btn-primary">Save</label>
            </div>
        </div>
    </div>
</div>
@endsection