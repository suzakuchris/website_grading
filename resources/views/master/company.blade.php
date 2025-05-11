@extends('layouts.app')

@section('content_header')
<h4>Master Company</h4>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="row mx-0 mb-4">
            <div class="col"></div>
            <div class="col-auto pe-0">
                <div class="row mx-0">
                    <div class="col-12 col-md-auto">
                        <div class="input-group">
                            <input id="text-search" type="text" class="form-control" placeholder="Search..">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto">
                        <a class="btn btn-primary" href="{{route('master.company.add')}}">Add New</a>
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
                    <th>Tipe Uang</th>
                    <th>Nama Company</th>
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

        $("#row_count, #text-search").change(function(){
            search_process();
        });

        
    });

    function search_process(){
        var search = $("#text-search").val();
        var max_row = $("#row_count").val();
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("master.company.search")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'page':curr_page,
                'max_row':max_row,
                'search':search,
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

            var type = "";
            if(y.company_type == 1){
                type = "<span class='badge bg-info'>Coin</span>";
            }

            rows += `
                <tr>
                    <td>`+(++page)+`.</td>
                    <td><span class='badge bg-info'>`+y.type_name+`</span></td>
                    <td>`+y.company_name+`</td>
                    <td>`+created+`</td>
                    <td>`+updated+`</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{route('master.company.view')}}/`+y.company_id+`" class="btn btn-outline-primary d-flex align-items-center"><i class="bi bi-pencil me-2"></i>Edit</a>
                            <button class="btn btn-outline-danger d-flex align-items-center" onclick="delete_data(`+y.company_id+`)"><i class="bi bi-trash me-2"></i>Delete</button>
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
                    url     : '{{route("master.company.delete")}}',
                    headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    dataType: 'JSON',
                    data    : {
                        'company_id':id,
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