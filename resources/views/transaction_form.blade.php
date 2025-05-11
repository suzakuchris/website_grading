@extends('layouts.app')

@section('content_header')
<h4><a href="{{route('transaction')}}" class="me-2"><i class="bi bi-chevron-left"></i></a>@if($mode == 'add') Tambah @else Detail @endif Invoice</h4>
@endsection

@section('content')
<form action="{{route('transaction.save')}}" method="POST" onsubmit="pre_submit(event, this);">
    <fieldset @if($mode != 'add') disabled @endif class="border-0 p-2">
        @php
            if(isset($transaction)){
                $total = 0;
                $total_idr = 0;
            }
        @endphp
        {{csrf_field()}}
        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <label>No. Invoice</label>
                    <input class="form-control" type="text" name="no_invoice" placeholder="00001/YYYY/MM/DD/CCCCC" @if(isset($transaction)) value="{{$transaction->inv_number}}" @endif disabled>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label>Customer</label>
                    @if(!isset($transaction))
                    <select name="customer_id" class="form-control w-100 select-searchable">
                        <option value="" disabled selected>Pilih Customer</option>
                        @foreach($customers as $customer)
                        <option value="{{$customer->customer_id}}" @if(isset($transaction) && $transaction->customer_id == $customer->customer_id) selected @endif>{{$customer->customer_name}} - {{$customer->customer_phone}}</option>
                        @endforeach
                    </select>
                    @else
                    <input type="text" class="form-control" value="{{$transaction->customer->customer_name}} - {{$transaction->customer->customer_phone}}" readonly>
                    @endif
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">Uang yang akan di grading</div>
            <div class="col-12 my-4">
                <div class="card">
                    <div class="card-header">Bank Notes</div>
                    <div class="card-body">
                        <table id="bank_note_list" class="table borderless">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th style="width:1%;"></th>
                                </tr>
                            </thead>
                            <tbody class="data-wrapper">
                                @if(isset($transaction))
                                @foreach($transaction->bank_notes as $bank)
                                 @php 
                                    $subtotal = $bank->detail_onsite_fee+$bank->detail_pedigree_fee+$bank->detail_oversize_fee;
                                    $total += $subtotal;
                                @endphp
                                <tr class="parent_row">
                                    <td colspan="2">
                                        <table class="table table-bordered">
                                            <tr>
                                                <td class="auto-width">Company</td>
                                                <td>
                                                    <div>{{$bank->company->company_name}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Negara</td>
                                                <td>
                                                    <div>{{$bank->country->country_code}} - {{$bank->country->country_name}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Details</td>
                                                <td>
                                                    <div>{{$bank->item->item_code}} - {{$bank->item->country->country_code}} - {{comma_separated($bank->item->nominal)}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Serial Number</td>
                                                <td>
                                                    <div>{{$bank->detail_serial_number}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Tahun</td>
                                                <td>
                                                    <div>{{$bank->detail_year}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Tier</td>
                                                <td>
                                                    <div>{{$bank->tier->detail_name}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Error?</td>
                                                <td>
                                                    <div>@if($bank->detail_has_error == 1) Ya @else Tidak @endif</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Oversize Fee</td>
                                                <td>
                                                    <div>{{comma_separated($bank->detail_oversize_fee)}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Pedigree Fee</td>
                                                <td>
                                                    <div>{{comma_separated($bank->detail_pedigree_fee)}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Onsite Fee</td>
                                                <td>
                                                    <div>{{comma_separated($bank->detail_onsite_fee)}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Total Fee</td>
                                                <td>
                                                    <div>{{comma_separated($subtotal)}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Deskripsi</td>
                                                <td>
                                                    <div>{{$bank->detail_description}}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if(!isset($transaction))
                    <div class="card-footer text-end">
                        <button onclick="open_modal('bank_notes');" class="btn btn-success btn-sm" type="button"><i class="bi bi-plus me-2"></i>Tambah</button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">Coin</div>
                    <div class="card-body">
                        <table id="coin_list" class="table borderless">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th style="width:1%;"></th>
                                </tr>
                            </thead>
                            <tbody class="data-wrapper">
                                @if(isset($transaction))
                                @foreach($transaction->coins as $coin)
                                @php 
                                    $subtotal = $coin->detail_onsite_fee+$coin->detail_ncs_fee+$coin->detail_pedigree_fee+$coin->detail_oversize_fee;
                                    $total += $subtotal;
                                @endphp
                                <tr class="parent_row">
                                    <td colspan="2">
                                        <table class="table table-bordered">
                                            <tr>
                                                <td class="auto-width">Company</td>
                                                <td>
                                                    <div>{{$coin->company->company_name}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Negara</td>
                                                <td>
                                                    <div>{{$coin->country->country_code}} - {{$coin->country->country_name}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Denomination</td>
                                                <td>
                                                    <div>{{comma_separated($coin->detail_denomination)}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Tahun</td>
                                                <td>
                                                    <div>{{$coin->detail_year}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Material</td>
                                                <td>
                                                    <div>{{$coin->material->material_name}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">MS/PF</td>
                                                <td>
                                                    <div>{{$coin->mspf->row_name}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Tier</td>
                                                <td>
                                                    <div>{{$coin->tier->detail_name}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Error?</td>
                                                <td>
                                                    <div>@if($coin->detail_has_error == 1) Ya @else Tidak @endif</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">NCS Fee</td>
                                                <td>
                                                    <div>{{comma_separated($coin->detail_ncs_fee)}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Oversize Fee</td>
                                                <td>
                                                    <div>{{comma_separated($coin->detail_oversize_fee)}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Pedigree Fee</td>
                                                <td>
                                                    <div>{{comma_separated($coin->detail_pedigree_fee)}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Onsite Fee</td>
                                                <td>
                                                    <div>{{comma_separated($coin->detail_onsite_fee)}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Total Fee</td>
                                                <td>
                                                    <div>{{comma_separated($subtotal)}}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="auto-width">Deskripsi</td>
                                                <td>
                                                    <div>{{$coin->detail_description}}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if(!isset($transaction))
                    <div class="card-footer text-end">
                        <button onclick="open_modal('coins');" class="btn btn-success btn-sm" type="button"><i class="bi bi-plus me-2"></i>Tambah</button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <div class="form-group">
                    <label>Comment / Catatan</label>
                    <textarea name="notes" rows="4" class="form-control" placeholder="Masukan komentar atau catatan tambahan">@if(isset($transaction)){{$transaction->header_comment}}@endif</textarea>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label>Grand total fee</label>
                    <input placeholder="0" class="form-control" type="text" name="grand_total" readonly @if(isset($transaction)) value="{{comma_separated($total)}}" @endif>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label>Rate USD to IDR</label>
                    <input placeholder="0" class="form-control comma-separated" data-target="usd_rate" type="text" name="usd_rate_text" @if(isset($transaction)) value="{{comma_separated($transaction->usd_rate)}}" @endif>
                    <input type="hidden" id="usd_rate" name="usd_rate" @if(isset($transaction)) value="{{($transaction->usd_rate)}}" @endif>
                </div>
            </div>
            @if(isset($transaction))
            @php
                $total_idr = $total * $transaction->usd_rate;
            @endphp
            @endif
            <div class="col-12">
                <div class="form-group">
                    <label>Fee in IDR</label>
                    <input placeholder="0" class="form-control" type="text" name="grand_total_idr" @if(isset($transaction)) value="{{comma_separated($total_idr)}}" @endif readonly>
                </div>
            </div>
        </div>
        @if($mode == 'add')
        <button type="submit" id="SubmitBtn" class="d-none">
        @endif
    </fieldset>

    @if(isset($transaction) && count($transaction->payments) > 0)
    <fieldset>
        <div class="row">
            <div class="col-12">
                <table id="payment-table" class="table">
                    <thead>
                        <tr>
                            <td colspan="8">
                                <div class="row my-2">
                                    <div class="col-auto"><h5>History Payment</h5></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>No.</th>
                            <th>Nominal</th>
                            <th>Keterangan</th>
                            <th>Tanggal Payment</th>
                            <th>Lampiran</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaction->payments as $k=>$payment)
                        <tr>
                            <td>{{$k+1}}.</td>
                            <td>{{comma_separated($payment->payment_amount)}}</td>
                            <td>{{$payment->payment_notes}}</td>
                            <td>{{\Carbon\Carbon::parse($payment->payment_date)->format('d M Y')}}</td>
                            <td>
                                <button class="btn btn-primary btn-sm" type="button" onclick="show_lampiran_payment({{$payment->payment_id}});">Lihat Bukti Pembayaran</button>
                            </td>
                            <td>
                                <a class="btn btn-primary btn-sm" href="{{route('transaction.payment.view', ['header_id' => $transaction->header_id, 'payment_id' => $payment->payment_id])}}"><i class="bi bi-eye me-2"></i>View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </fieldset>
    @endif
</form>
@endsection

@section('content_footer')
<div class="row mx-0">
    <div class="col"></div>
    <div class="col-auto">
        @if($mode == 'add')
        <label for="SubmitBtn" class="btn btn-success"><i class="bi bi-save me-2"></i>Save</label>
        @else
        <a href="{{route('transaction.payment.add', ['header_id' => $transaction->header_id])}}" class="btn btn-success"><i class="bi bi-plus me-2"></i>Tambah Pembayaran</a>
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

    $("#usd_rate").on('change', function(){
        calculate_total();
    });

    function open_modal(type){
        var modal_id = "#form_modal_banknotes";
        if(type == 'coins'){
            modal_id = '#form_modal_coin';
        }
        var form = $(modal_id).find('form');
        form.trigger('reset');
        form.find('.select-searchable').trigger('change');

        $(modal_id).modal('show');
    }

    function fetch_tier(target, select){
        var select = $(select);
        var value = select.val();
        if(!value){
            select.closest('form').find("."+target).html(`<option value="" selected disabled>Pilih Company</option>`);
            init_select();
            return;
        }
        showLoading();
        $.ajax({
            type        : 'POST',
            url         : '{{route("transaction.tier")}}',
            headers     : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType    : 'JSON',
            data        : {
                "company_id": value
            },
            success     : function(msg) {
                var options = '<option value="" selected disabled>Pilih Tier</option>';
                $.each(msg, function(a,b){
                    options += `
                        <option value="`+b.detail_id+`" data-basefee="`+b.base_fee+`" data-errorfee="`+b.error_fee+`">`+b.detail_name+`</option>
                    `;
                });
                select.closest('form').find("."+target).html(options);
                init_select();
            },
            error       : function(xhr) {
                console.log(xhr);
            },
            complete    : function(xhr){
                closeLoading();
            }
        });
    }

    function calculate_total(){
        var subtotals = $(".subtotal_fee");
        var total_fee = 0;
        $.each(subtotals, function(a,b){
            total_fee += parseFloat($(b).val());
        });

        $("[name='grand_total']").val(numberWithCommas(total_fee));

        var rate = $("[name='usd_rate']").val();
        if(!rate){
            rate = 0;
        }
        
        var grand_total_idr = parseFloat(total_fee) * parseFloat(rate);
        console.log(total_fee);
        console.log(rate);
        $("[name='grand_total_idr']").val(numberWithCommas(grand_total_idr));
    }

    function remove_row(btn){
        var btn = $(btn);
        btn.closest('.parent_row').remove();
        calculate_total();
    }

    function save_bank(event, form){
        event.preventDefault();
        var form = $(form);
        var table = $("#bank_note_list tbody.data-wrapper");
        var rowid = "DATA_"+moment().unix();

        var company_select = form.find("[name='company']");
        var company_data = company_select.select2('data')[0];
        var company_id = company_select.val();
        var company_text = company_data.text;

        var negara_select = form.find("[name='kd_negara']");
        var negara_data = negara_select.select2('data')[0];
        var negara_id = negara_select.val();
        var negara_text = negara_data.text;

        var details_select = form.find("[name='item_id']");
        var details_data = details_select.select2('data')[0];
        var detail_id = details_select.val();
        var detail_text = details_data.text;

        var serial_number = form.find("[name='serial_number']").val();
        var tahun = form.find("[name='tahun']").val();
        var description = form.find("[name='description']").val();

        var tier_select = form.find("[name='tier_id']");
        var tier_data = tier_select.select2('data')[0];
        var tier_id = tier_select.val();
        var tier_text = tier_data.text;
        var tier_basefee = tier_data.basefee;
        var tier_errorfee = tier_data.errorfee;

        var error_select = form.find("[name='has_error']");
        var error_data = error_select.select2('data')[0];
        var error_id = error_select.val();
        var error_text = error_data.text;

        var oversize_fee = form.find("[name='oversize']").val();
        var pedigree_fee = form.find("[name='pedigree']").val();
        var onsite_fee = form.find("[name='onsite']").val();

        console.log(oversize_fee);
        console.log(pedigree_fee);
        console.log(onsite_fee);

        var subtotal_fee = parseFloat(oversize_fee)+parseFloat(pedigree_fee)+parseFloat(onsite_fee);

        var row = `
            <tr class="parent_row">
                <td>
                    <table class="table table-bordered">
                        <tr>
                            <td class="auto-width">Company</td>
                            <td>
                                <div>`+company_text+`</div>
                                <input type="hidden" name="grading_banknotes[`+rowid+`][company_id]" value="`+company_id+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Negara</td>
                            <td>
                                <div>`+negara_text+`</div>
                                <input type="hidden" name="grading_banknotes[`+rowid+`][kd_negara]" value="`+negara_id+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Details</td>
                            <td>
                                <div>`+detail_text+`</div>
                                <input type="hidden" name="grading_banknotes[`+rowid+`][item_id]" value="`+detail_id+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Serial Number</td>
                            <td>
                                <div>`+serial_number+`</div>
                                <input type="hidden" name="grading_banknotes[`+rowid+`][detail_serial_number]" value="`+serial_number+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Tahun</td>
                            <td>
                                <div>`+tahun+`</div>
                                <input type="hidden" name="grading_banknotes[`+rowid+`][detail_year]" value="`+tahun+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Tier</td>
                            <td>
                                <div>`+tier_text+`</div>
                                <input type="hidden" name="grading_banknotes[`+rowid+`][company_detail_id]" value="`+tier_id+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Error?</td>
                            <td>
                                <div>`+error_text+`</div>
                                <input type="hidden" name="grading_banknotes[`+rowid+`][detail_has_error]" value="`+error_id+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Oversize Fee</td>
                            <td>
                                <div>`+numberWithCommas(oversize_fee)+`</div>
                                <input type="hidden" name="grading_banknotes[`+rowid+`][detail_oversize_fee]" value="`+oversize_fee+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Pedigree Fee</td>
                            <td>
                                <div>`+numberWithCommas(pedigree_fee)+`</div>
                                <input type="hidden" name="grading_banknotes[`+rowid+`][detail_pedigree_fee]" value="`+pedigree_fee+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Onsite Fee</td>
                            <td>
                                <div>`+numberWithCommas(onsite_fee)+`</div>
                                <input type="hidden" name="grading_banknotes[`+rowid+`][detail_onsite_fee]" value="`+onsite_fee+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Total Fee</td>
                            <td>
                                <div>`+numberWithCommas(subtotal_fee)+`</div>
                                <input type="hidden" type="hidden" class="subtotal_fee" value="`+subtotal_fee+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Deskripsi</td>
                            <td>
                                <div>`+description+`</div>
                                <textarea class="d-none" type="hidden" name="grading_banknotes[`+rowid+`][description]">`+description+`</textarea>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <button type="button" onclick="remove_row(this);" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `;

        table.append(row);
        calculate_total();
        form.closest('.modal').modal('hide');
    }

    function save_coin(event, form){
        event.preventDefault();
        var form = $(form);
        var table = $("#coin_list tbody.data-wrapper");
        var rowid = "DATA_"+moment().unix();

        var company_select = form.find("[name='company']");
        var company_data = company_select.select2('data')[0];
        var company_id = company_select.val();
        var company_text = company_data.text;

        var negara_select = form.find("[name='kd_negara']");
        var negara_data = negara_select.select2('data')[0];
        var negara_id = negara_select.val();
        var negara_text = negara_data.text;

        var material_select = form.find("[name='material_id']");
        var material_data = material_select.select2('data')[0];
        var material_id = material_select.val();
        var material_text = material_data.text;

        var mspf_select = form.find("[name='mspf']");
        var mspf_data = mspf_select.select2('data')[0];
        var mspf_id = mspf_select.val();
        var mspf_text = mspf_data.text;

        var denomination = form.find("[name='denomination']").val();
        var tahun = form.find("[name='tahun']").val();
        var description = form.find("[name='description']").val();

        var tier_select = form.find("[name='tier_id']");
        var tier_data = tier_select.select2('data')[0];
        var tier_id = tier_select.val();
        var tier_text = tier_data.text;
        var tier_basefee = tier_data.basefee;
        var tier_errorfee = tier_data.errorfee;

        var error_select = form.find("[name='has_error']");
        var error_data = error_select.select2('data')[0];
        var error_id = error_select.val();
        var error_text = error_data.text;

        var oversize_fee = form.find("[name='oversize']").val();
        var pedigree_fee = form.find("[name='pedigree']").val();
        var onsite_fee = form.find("[name='onsite']").val();
        var ncs_fee = form.find("[name='ncs']").val();

        var subtotal_fee = parseFloat(oversize_fee)+parseFloat(pedigree_fee)+parseFloat(onsite_fee)+parseFloat(ncs_fee);

        var row = `
            <tr class="parent_row">
                <td>
                    <table class="table table-bordered">
                        <tr>
                            <td class="auto-width">Company</td>
                            <td>
                                <div>`+company_text+`</div>
                                <input type="hidden" name="grading_coins[`+rowid+`][company_id]" value="`+company_id+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Negara</td>
                            <td>
                                <div>`+negara_text+`</div>
                                <input type="hidden" name="grading_coins[`+rowid+`][kd_negara]" value="`+negara_id+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Serial Number</td>
                            <td>
                                <div>`+denomination+`</div>
                                <input type="hidden" name="grading_coins[`+rowid+`][detail_denomination]" value="`+denomination+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Tahun</td>
                            <td>
                                <div>`+tahun+`</div>
                                <input type="hidden" name="grading_coins[`+rowid+`][detail_year]" value="`+tahun+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Details</td>
                            <td>
                                <div>`+material_text+`</div>
                                <input type="hidden" name="grading_coins[`+rowid+`][detail_material]" value="`+material_id+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Details</td>
                            <td>
                                <div>`+mspf_text+`</div>
                                <input type="hidden" name="grading_coins[`+rowid+`][detail_mspf]" value="`+mspf_id+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Tier</td>
                            <td>
                                <div>`+tier_text+`</div>
                                <input type="hidden" name="grading_coins[`+rowid+`][company_detail_id]" value="`+tier_id+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Error?</td>
                            <td>
                                <div>`+error_text+`</div>
                                <input type="hidden" name="grading_coins[`+rowid+`][detail_has_error]" value="`+error_id+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">NCS Fee</td>
                            <td>
                                <div>`+numberWithCommas(ncs_fee)+`</div>
                                <input type="hidden" name="grading_coins[`+rowid+`][detail_ncs_fee]" value="`+ncs_fee+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Oversize Fee</td>
                            <td>
                                <div>`+numberWithCommas(oversize_fee)+`</div>
                                <input type="hidden" name="grading_coins[`+rowid+`][detail_oversize_fee]" value="`+oversize_fee+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Pedigree Fee</td>
                            <td>
                                <div>`+numberWithCommas(pedigree_fee)+`</div>
                                <input type="hidden" name="grading_coins[`+rowid+`][detail_pedigree_fee]" value="`+pedigree_fee+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Onsite Fee</td>
                            <td>
                                <div>`+numberWithCommas(onsite_fee)+`</div>
                                <input type="hidden" name="grading_coins[`+rowid+`][detail_onsite_fee]" value="`+onsite_fee+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Total Fee</td>
                            <td>
                                <div>`+numberWithCommas(subtotal_fee)+`</div>
                                <input type="hidden" type="hidden" class="subtotal_fee" value="`+subtotal_fee+`">
                            </td>
                        </tr>
                        <tr>
                            <td class="auto-width">Deskripsi</td>
                            <td>
                                <div>`+description+`</div>
                                <textarea class="d-none" type="hidden" name="grading_coins[`+rowid+`][description]">`+description+`</textarea>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <button type="button" onclick="remove_row(this);" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `;

        table.append(row);
        calculate_total();
        form.closest('.modal').modal('hide');
    }
</script>
<script>
    function show_lampiran_payment(payment_id){
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("transaction.payment.attachment")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'payment_id':payment_id
            },
            success : function(msg) {
                var images_data = msg;
                var images = '';
                $.each(images_data, function(a,b){
                    var active = '';
                    if(a==0){
                        active = 'active';
                    }
                    images += `
                        <div class="carousel-item `+active+`">
                            <img src="`+b+`" class="d-block w-100">
                        </div>
                    `;
                });

                $("#payment_container").html(images);
                $("#payment_modal").modal('show');
                $('#payment_modal .carousel').carousel({
                    interval: 0
                });
            },
            error     : function(xhr) {
                console.log(xhr);
            },
            complete : function(xhr,status){
                closeLoading();
            }
        });
    }
</script>
@endsection

@section('footer')
<div class="modal fade" id="form_modal_banknotes" aria-labelledby="formModalBank" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="formModalBank">Data Bank Notes</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" tabindex="-1"></button>
            </div>
            <div class="modal-body">
                <form onsubmit="save_bank(event, this);">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Company</label>
                                <select class="form-control select-searchable" name="company" required onchange="fetch_tier('tier-select', this);">
                                    <option value="" selected disabled>Pilih Company</option>
                                    @foreach($companies_bn as $company)
                                    <option value="{{$company->company_id}}">{{$company->company_name}}</option>
                                    @endforeach 
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Negara</label>
                                <select class="form-control select-searchable" name="kd_negara" required>
                                    <option value="" selected disabled>Pilih Negara</option>
                                    @foreach(country_lists() as $country)
                                    <option value="{{$country->id}}">{{$country->country_code}} - {{$country->country_name}}</option>
                                    @endforeach 
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Details</label>
                                <select class="form-control select-searchable" name="item_id" required>
                                    <option value="" selected disabled>Pilih Item</option>
                                    @foreach($items as $item)
                                    <option value="{{$item->item_id}}">{{$item->item_code}} - {{$item->country->country_code}} - {{comma_separated($item->nominal)}}</option>
                                    @endforeach 
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Serial Number</label>
                                <input type="text" class="form-control" name="serial_number" required placeholder="Masukan Serial Number">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Tahun</label>
                                <select class="form-control select-searchable" name="tahun" required>
                                    <option value="" selected disabled>Pilih Tahun</option>
                                    @foreach(tahun_lists() as $tahun)
                                    <option value="{{$tahun}}">{{$tahun}}</option>
                                    @endforeach 
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Tier</label>
                                <select name="tier_id" class="tier-select form-control select-searchable">
                                    <option value="" selected disabled>Pilih Company</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Error?</label>
                                <select name="has_error" class="form-control select-searchable">
                                    <option value="" selected disabled>Pilih Ya/Tidak</option>
                                    <option value="0">Tidak</option>
                                    <option value="1">Ya</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Oversize Fee</label>
                                <input type="text" data-target="oversize" class="form-control comma-separated" name="oversize-text" required placeholder="Masukan Nominal">
                                <input id="oversize" type="hidden" name="oversize" value="0" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Pedigree Fee</label>
                                <input type="text" data-target="pedigree" class="form-control comma-separated" name="pedigree-text" required placeholder="Masukan Nominal">
                                <input id="pedigree" type="hidden" name="pedigree" value="0" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Onsite Fee</label>
                                <input type="text" data-target="onsite" class="form-control comma-separated" name="onsite-text" required placeholder="Masukan Nominal">
                                <input id="onsite" type="hidden" name="onsite" value="0" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea rows="4" name="description" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="d-none" id="SubmitBtnFormModalBN"></button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <label for="SubmitBtnFormModalBN" class="btn btn-primary">Save</label>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="form_modal_coin" aria-labelledby="formModalCoin" aria-hidden="true" data-keyboard="false" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="formModalCoin">Data Coin</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" tabindex="-1"></button>
            </div>
            <div class="modal-body">
                <form onsubmit="save_coin(event, this);">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Company</label>
                                <select class="form-control select-searchable" name="company" required onchange="fetch_tier('tier-select', this);">
                                    <option value="" selected disabled>Pilih Company</option>
                                    @foreach($companies_cn as $company)
                                    <option value="{{$company->company_id}}">{{$company->company_name}}</option>
                                    @endforeach 
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Negara</label>
                                <select class="form-control select-searchable" name="kd_negara" required>
                                    <option value="" selected disabled>Pilih Negara</option>
                                    @foreach(country_lists() as $country)
                                    <option value="{{$country->id}}">{{$country->country_code}} - {{$country->country_name}}</option>
                                    @endforeach 
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Denomination</label>
                                <input type="text" data-target="denomination_coin" class="form-control comma-separated" name="denomination_text" required placeholder="Masukan Denominasi">
                                <input id="denomination_coin" type="hidden" name="denomination" value="0" required>
                            </div>
                        </div>
                         <div class="col-12">
                            <div class="form-group">
                                <label>Tahun</label>
                                <select class="form-control select-searchable" name="tahun" required>
                                    <option value="" selected disabled>Pilih Tahun</option>
                                    @foreach(tahun_lists() as $tahun)
                                    <option value="{{$tahun}}">{{$tahun}}</option>
                                    @endforeach 
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Material</label>
                                <select class="form-control select-searchable" name="material_id" required>
                                    <option value="" selected disabled>Pilih Material</option>
                                    @foreach($materials as $material)
                                    <option value="{{$material->material_id}}">{{$material->material_name}}</option>
                                    @endforeach 
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>MS/PF</label>
                                <select class="form-control select-searchable" name="mspf" required>
                                    <option value="" selected disabled>Pilih Data</option>
                                    @foreach($mspf as $m)
                                    <option value="{{$m->row_id}}">{{$m->row_name}}</option>
                                    @endforeach 
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Tier</label>
                                <select name="tier_id" class="tier-select form-control select-searchable">
                                    <option value="" selected disabled>Pilih Company</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Error?</label>
                                <select name="has_error" class="form-control select-searchable">
                                    <option value="" selected disabled>Pilih Ya/Tidak</option>
                                    <option value="0">Tidak</option>
                                    <option value="1">Ya</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>NCS Fee</label>
                                <input type="text" data-target="ncs_coin" class="form-control comma-separated" name="ncs-text" required placeholder="Masukan Nominal">
                                <input id="ncs_coin" type="hidden" name="ncs" value="0" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Oversize Fee</label>
                                <input type="text" data-target="oversize_coin" class="form-control comma-separated" name="oversize-text" required placeholder="Masukan Nominal">
                                <input id="oversize_coin" type="hidden" name="oversize" value="0" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Pedigree Fee</label>
                                <input type="text" data-target="pedigree_coin" class="form-control comma-separated" name="pedigree-text" required placeholder="Masukan Nominal">
                                <input id="pedigree_coin" type="hidden" name="pedigree" value="0" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Onsite Fee</label>
                                <input type="text" data-target="onsite_coin" class="form-control comma-separated" name="onsite-text" required placeholder="Masukan Nominal">
                                <input id="onsite_coin" type="hidden" name="onsite" value="0" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea rows="4" name="description" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="d-none" id="SubmitBtnFormModalCN"></button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <label for="SubmitBtnFormModalCN" class="btn btn-primary">Save</label>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="payment_modal" tabindex="-1" aria-labelledby="payment_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="payment_modal_label">Lampiran Payment</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="payment_container_wrapper" class="carousel slide">
                    <div class="carousel-inner" id="payment_container">
                        
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#payment_container_wrapper" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#payment_container_wrapper" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection