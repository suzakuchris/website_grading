@extends('layouts.app')

@section('css')
<style>
    #image_table {
        counter-reset: row-num;
    }
    #image_table tbody tr  {
        counter-increment: row-num;
    }

    #image_table tbody tr:not(.no-data) td:first-child::before {
        content: counter(row-num) ". ";
    }
    #image_table tbody tr:not(.no-data) td:first-child {
        text-align: center;
    }

    #image_table tbody tr:first-child .btn-up{
        display:none;
    }

    #image_table tbody tr:last-child .btn-down{
        display:none;
    }
</style>
@endsection

@section('content_header')
<h4><a href="{{route('transaction')}}" class="me-2"><i class="bi bi-chevron-left"></i></a>@if($mode == 'add') Tambah @else Detail @endif Payment</h4>
@endsection

@section('content')
<form action="{{route('transaction.payment.save', ['header_id' => $header->header_id])}}" method="POST" onsubmit="pre_submit(event, this);">
    <fieldset class="row pt-3" @if(isset($payment)) disabled @endif>
        {{csrf_field()}}
        @if(isset($payment))
        <input type="hidden" name="payment_id" value="{{$payment->payment_id}}">
        @endif
        <input type="hidden" name="header_id" value="{{$header->header_id}}">
        <div class="col-12">
            <div class="form-group">
                <label>No. Invoice</label>
                <input type="text" class="form-control" value="{{$header->inv_number}}" disabled>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <label>Customer</label>
                <input type="text" class="form-control" value="{{$header->customer->customer_name}} - {{$header->customer->customer_phone}}" disabled>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <label>Nominal</label>
                <input type="text" class="form-control comma-separated" placeholder="Jumlah Bayar" name="payment_amount_text" data-target="payment_amount" @if(isset($payment)) value="{{comma_separated($payment->payment_amount)}}" @endif>
                <input type="hidden" id="payment_amount" name="payment_amount" @if(isset($payment)) value="{{$payment->payment_amount}}" @endif>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <label>Tanggal Payment</label>
                <input type="date" name="payment_date" class="form-control" @if(isset($payment)) value="{{$payment->payment_date}}" @endif>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <label>Bukti Payment</label>
                <table id="image_table" class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="auto-width">No.</th>
                            <th>Gambar / Images</th>
                            <th class="auto-width">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($payment) && isset($payment->images) && count($payment->images) > 0)
                            @foreach($payment->images as $i)
                            <tr class="input-row">
                                <td></td>
                                <td>
                                    <div class="image-wrapper">
                                        <img style="width:100px;height:auto;max-height:200px;" src="{{asset($i->image_path.$i->image_name)}}">
                                    </div>
                                    <div class="input-wrapper">
                                        <input type="hidden" name="payment_images_keep[]" value="{{$i->image_id}}">
                                    </div>
                                </td>
                                <td>
                                </td>
                            </tr>
                            @endforeach
                        @else
                        <tr class="no-data">
                            <td colspan="3">Tambahkan gambar..</td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="input-wrapper" colspan="2">
                                <input accept="image/*" type="file" class="form-control imageinput">
                                <input type="text" class="d-none base64input">
                            </td>
                            <td>
                                <button onclick="add_image(this);" type="button" class="btn btn-sm btn-primary d-flex align-items-center" type="button"><i class="bi bi-plus me-2"></i>Add</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <label>Keterangan</label>
                <textarea class="form-control" name="payment_notes" rows="5" placeholder="Isi Keterangan">@if(isset($payment)){{$payment->payment_notes}}@endif</textarea>
            </div>
        </div>
        <button type="submit" id="SubmitBtn" class="d-none">
    </fieldset>
</form>
@endsection

@section('content_footer')
<div class="row mx-0">
    <div class="col"></div>
    <div class="col-auto">
        @if($mode == 'add')
        <label for="SubmitBtn" class="btn btn-success"><i class="bi bi-save me-2"></i>Save</label>
        @endif
    </div>
</div>
@endsection


@section('js')
<script>
    function pre_submit(event, form){
        event.preventDefault();
        var isValid = form.reportValidity();
        if(isValid){
            Swal.fire({
                title: "Apakah anda yakin mau menyimpan data?",
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: "Yes",
                denyButtonText: `No`
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    showLoading();
                    $(form).removeAttr('onsubmit');
                    $(form).submit();
                } else if (result.isDenied) {
                    // Swal.fire("Changes are not saved", "", "info");
                }
            });
        }
    }
</script>
<script>
    File.prototype.convertToBase64 = function(callback){
        var reader = new FileReader();
        reader.onloadend = function (e) {
            callback(e.target.result, e.target.error);
        };   
        reader.readAsDataURL(this);
    };
    $(".imageinput").on('change', function(){
        var selectedFile = this.files[0];
        var input = $(this);
        selectedFile.convertToBase64(function(base64){
            var next = input.next().val(base64);
        });
    });

    function add_image(btn){
        var btn = $(btn);
        var row = btn.closest('tr');
        var table = row.closest('table');
        var body = table.find('tbody');
        var input = row.find('.imageinput');
        var input_b64 = row.find('.base64input');

        if(input_b64.val() == ""){
            Swal.fire("Silahkan pilih foto dulu");
            return;
        }

        var _row = $("#factory-table tr.input-row").clone();
        var image = `<img style="width:100px;height:auto;max-height:200px;" src="`+input_b64.val()+`">`;
        _row.find(".image-wrapper").html(image);
        // input.addClass('d-none').appendTo(_row.find(".input-wrapper"));
        input.val('');
        input_b64.attr('name', 'payment_images_add[]').appendTo(_row.find(".input-wrapper"));

        body.find('.no-data').remove();
        body.append(_row);

        row.find('.input-wrapper').append(`<input type="text" class="d-none base64input">`);
    }

    function remove(btn){
        var btn = $(btn);
        var tbody = btn.closest('tbody');
        btn.closest('tr').remove();

        if(tbody.children().length < 1){
            tbody.append($("#factory-table .no-data").clone());
        }
    }
</script>
@endsection

@section('footer')
<table class="d-none" id="factory-table">
    <tr class="input-row">
        <td></td>
        <td>
            <div class="image-wrapper"></div>
            <div class="input-wrapper"></div>
        </td>
        <td class="text-center">
            <div class="btn-group">
                <button type="button" onclick="remove(this);" class="btn btn-danger btn-delete"><i class="bi bi-trash"></i></button>
            </div>
        </td>
    </tr>
    <tr class="input-row-visible">
        <td>
            <input type="file" accept="image/*" class="form-control imageinput">
            <input type="text" class="d-none base64input">
        </td>
    </tr>
    <tr class="no-data">
        <td colspan="3">Tambahkan gambar..</td>
    </tr>
</table>
@endsection