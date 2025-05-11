@extends('layouts.app')

@section('content_header')
<h4><a href="{{route('master.company')}}" class="me-2"><i class="bi bi-chevron-left"></i></a>@if($mode == 'add') Tambah @else Edit @endif Company Grading</h4>
@endsection

@section('content')
<form class="row" method="POST" action="{{route('master.company.upsert')}}">
    {{csrf_field()}}
    @if(isset($company))
    <input type="hidden" name="company_id" value="{{$company->company_id}}">
    @endif
    <div class="col-12 mb-2">
        <div class="form-group">
            <label>Tipe Uang</label>
            <select class="form-control" name="company_type" required>
                <option value="">Pilih Type</option>
                @foreach($types as $type)
                <option value="{{$type->type_id}}" @if(old('company_type') && old('company_type') == $type->type_id) selected @elseif(isset($company) && $company->company_type == $type->type_id) selected @endif>{{$type->type_name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-12 mb-2">
        <div class="form-group">
            <label>Nama Company</label>
            <input class="form-control" type="text" name="company_name" required placeholder="Isi Nama Company" @if(isset($company)) value="{{$company->company_name}}" @endif>
        </div>
    </div>
    @if($mode == 'edit' && isset($company))
    <div class="col-12">
        <table class="table bordered">
            <thead>
                <th>Tier</th>
                <th style="width:1%;">
                    <a href="{{route('master.company.detail.add', ['id' => $company->company_id])}}" class="btn btn-primary btn-sm" style="white-space:nowrap;">Tambah Tier</a>
                </th>
            </thead>
            <tbody>
                @if(count($company->details) > 0)
                @foreach($company->details as $detail)
                <tr>
                    <td>
                        <div class="row">
                            <div class="col-2">Nama Tier</div>
                            <div class="col-auto">: {{$detail->detail_name}}</div>
                        </div>
                        <div class="row">
                            <div class="col-2">Type</div>
                            <div class="col-auto">: {{$detail->type->type_name}}</div>
                        </div>
                        <div class="row">
                            <div class="col-2">Base Fee</div>
                            <div class="col-auto">: {{comma_separated($detail->base_fee)}}</div>
                        </div>
                        <div class="row">
                            <div class="col-2">Error Fee</div>
                            <div class="col-auto">: {{comma_separated($detail->error_fee)}}</div>
                        </div>
                    </td>
                    <td class="text-center">
                        <a href="{{route('master.company.detail.view', ['detail_id' => $detail->detail_id])}}" class="btn btn-info"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="2">
                        Belum ada Tier
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endif
    <div class="col-12 mt-2">
        <div class="row mx-0">
            <div class="col"></div>
            <div class="col-auto">
                <button type="submit" class="btn btn-success">Save</button>
            </div>
        </div>
    </div>
</form>
@endsection