@extends('layouts.app', ['no_sidebar' => true, 'allow_scroll' => true])

@section('css')
<style>
    @media print {
        /* visible when printed */
        .hide-for-print {
            display: none;
        }

        body {
            margin: 0;
            color: #000;
            background-color: #fff;
        }

        @page { margin: 0; }
        body { margin: 0; }
    }
</style>
@endsection

@section('content')
<div class="container w-100" style="color:black;">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header">
                    <div class="row mx-0 d-flex align-items-center">
                        <div class="col-3">
                            <img src="{{asset(site_config()->site_banner)}}" style="width:75px;">
                        </div>
                        <div class="col-9 d-flex justify-content-end align-items-center">
                            <h5 class="me-2 mb-0">{{$header->inv_number}}</h5>
                            <button id="print_btn" class="btn btn-primary hide-for-print" onclick="window.print();"><i class=""></i>Print</button>
                        </div>
                        <div class="col-12">
                            <hr/>
                        </div>
                    </div>
                </div>
                <div class="card-body position-relative">
                    <div class="row">
                        <div class="col-8">
                            @php
                                $date_start = format_time($header->created_at, "d F Y");
                            @endphp
                            <div><span class="fw-semibold">Submitted On</span>: {{$date_start}}</div>
                            <div class="fw-semibold">Bill To:</div>
                            <div class="border p-2">
                                <table>
                                    <tr>
                                        <td class="auto-width">Name&nbsp;</td>
                                        <td>: {{$header->customer->customer_name}}</td>
                                    </tr>
                                    <tr>
                                        <td class="auto-width">E-mail &nbsp;</td>
                                        <td>: {{$header->customer->customer_email}}</td>
                                    </tr>
                                    <tr>
                                        <td class="auto-width">Company &nbsp;</td>
                                        <td>: </td>
                                    </tr>
                                    <tr>
                                        <td class="auto-width">Street Address&nbsp;</td>
                                        <td>: {{$header->customer->customer_address}}</td>
                                    </tr>
                                    <tr>
                                        <td class="auto-width">City, State, Zip&nbsp;</td>
                                        <td>:</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="fw-semibold">&nbsp;</div>
                            <div class="fw-semibold">NOTES:</div>
                            <div class="border p-2">
                                <ol>
                                    <li>Total biaya tertera bersifat sementara.</li>
                                    <li>Bisa terjadi penyesuaian Tier dan Biaya jika ada perubahan dari final invoice PMG atau PCGS.</li>
                                </ol>
                            </div>
                            <div class="pt-2"><i>(USD Rate: {{comma_separated($header->usd_rate)}})</i></div>
                        </div>
                    </div>
                    @php
                        $grand_grand_total = 0;
                    @endphp
                    <div class="row py-3">
                        <div class="col-12">
                            <table class="table table-bordered text-center">
                                <thead>
                                    <tr>
                                        <th colspan="12" class="text-center">
                                            <h3>Form Grading Banknote</h3>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>No.</th>
                                        <th>Company</th>
                                        <th>Country</th>
                                        <th>Details</th>
                                        <th>Serial No.</th>
                                        <th>Tier</th>
                                        <th>Comment</th>
                                        <th>Base Fee</th>
                                        <th>Oversize</th>
                                        <th>Pedigree</th>
                                        <th>Onsite Fee</th>
                                        <th>Total Fee</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $grand_total = 0;
                                    @endphp
                                    @foreach($header->bank_notes as $i=>$item)
                                    @php
                                        $total_fee = $item->detail_base_fee 
                                        + $item->detail_oversize_fee
                                        + $item->detail_pedigree_fee
                                        + $item->detail_onsite_fee;
                                        $grand_total += $total_fee;
                                        $grand_grand_total += $total_fee;
                                    @endphp
                                    <tr>
                                        <td>{{++$i}}.</td>
                                        <td>{{$item->company->company_name}}</td>
                                        <td>{{$item->country->country_name}}</td>
                                        <td>{{$item->item->item_code}} - {{$item->item->country->country_code}} - {{comma_separated($item->item->nominal)}}</td>
                                        <td>{{$item->detail_serial_number}}</td>
                                        <td>{{$item->tier->detail_name}}</td>
                                        <td>{{$item->detail_description}}</td>
                                        <td>$&nbsp;{{comma_separated($item->detail_base_fee)}}</td>
                                        <td>$&nbsp;{{comma_separated($item->detail_oversize_fee)}}</td>
                                        <td>$&nbsp;{{comma_separated($item->detail_pedigree_fee)}}</td>
                                        <td>$&nbsp;{{comma_separated($item->detail_onsite_fee)}}</td>
                                        <td>$&nbsp;{{comma_separated($total_fee)}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="11" class="text-center"><b>TOTAL</b></td>
                                        <td>Rp.&nbsp;{{comma_separated($grand_total * $header->usd_rate)}}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="row py-3">
                        <div class="col-12">
                            <table class="table table-bordered text-center">
                                <thead>
                                    <tr>
                                        <th colspan="15" class="text-center">
                                            <h3>Form Grading Coin</h3>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>No.</th>
                                        <th>Company</th>
                                        <th>Country</th>
                                        <th>Denomination</th>
                                        <th>Tahun</th>
                                        <th>Material</th>
                                        <th>MS/PF</th>
                                        <th>Tier</th>
                                        <th>Error?</th>
                                        <th>NCS Fee</th>
                                        <th>Base Fee</th>
                                        <th>Oversize</th>
                                        <th>Pedigree</th>
                                        <th>Onsite Fee</th>
                                        <th>Total Fee</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $grand_total = 0;
                                    @endphp
                                    @foreach($header->coins as $i=>$item)
                                    @php
                                        $total_fee = $item->detail_base_fee 
                                        + $item->detail_ncs_fee
                                        + $item->detail_oversize_fee
                                        + $item->detail_pedigree_fee
                                        + $item->detail_onsite_fee;
                                        $grand_total += $total_fee;
                                        $grand_grand_total += $total_fee;
                                    @endphp
                                    <tr>
                                        <td>{{++$i}}.</td>
                                        <td>{{$item->company->company_name}}</td>
                                        <td>{{$item->country->country_name}}</td>
                                        <td>{{$item->detail_denomination}}</td>
                                        <td>{{$item->detail_year}}</td>
                                        <td>{{$item->material->material_name}}</td>
                                        <td>{{$item->mspf->row_name}}</td>
                                        <td>{{$item->tier->detail_name}}</td>
                                        <td>{{$item->detail_has_error}}</td>
                                        <td>$&nbsp;{{comma_separated($item->detail_ncs_fee)}}</td>
                                        <td>$&nbsp;{{comma_separated($item->detail_base_fee)}}</td>
                                        <td>$&nbsp;{{comma_separated($item->detail_oversize_fee)}}</td>
                                        <td>$&nbsp;{{comma_separated($item->detail_pedigree_fee)}}</td>
                                        <td>$&nbsp;{{comma_separated($item->detail_onsite_fee)}}</td>
                                        <td>$&nbsp;{{comma_separated($total_fee)}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="14" class="text-center"><b>TOTAL</b></td>
                                        <td>Rp.&nbsp;{{comma_separated($grand_total * $header->usd_rate)}}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="row py-3">
                        <div class="col-12">
                            <table class="table table-bordered text-center">
                                <tfoot>
                                    <tr>
                                        <td colspan="14" class="text-center"><b>Grand Total</b></td>
                                        <td>Rp.&nbsp;{{comma_separated($grand_grand_total * $header->usd_rate)}}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <i class="text-muted small">Generated by system.</i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function(){
        $("body").removeClass("dark");
        $("html").removeAttr('data-bs-theme');
    });
</script>
@endsection