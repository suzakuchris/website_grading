@extends('layouts.app')

@section('content_header')
<h4><a href="{{url()->previous()}}" class="me-2"><i class="bi bi-chevron-left"></i></a>@if($mode == 'add') Tambah @else Edit @endif Tier</h4>
@endsection

@section('content')
<form class="row" method="POST" action="{{route('master.company.details.upsert')}}">
    {{csrf_field()}}
    @if(isset($detail))
    <input type="hidden" name="detail_id" value="{{$detail->detail_id}}">
    @endif
    <input type="hidden" name="company_id" value="{{$company_id}}">
    <div class="col-12 mb-2">
        <div class="form-group">
            <label>Nama Tier</label>
            <input class="form-control" type="text" name="detail_name" required placeholder="Isi Nama Tier" @if(isset($detail)) value="{{$detail->detail_name}}" @endif>
        </div>
    </div>
    <div class="col-12 mb-2">
        <div class="form-group">
            <label>Type</label>
            <select class="form-control" name="detail_type" required>
                <option value="">Pilih Type</option>
                @foreach($types as $type)
                <option value="{{$type->type_id}}" @if(old('detail_type') && old('detail_type') == $type->type_id) selected @elseif(isset($detail) && $detail->detail_type == $type->type_id) selected @endif>{{$type->type_name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-12 mb-2">
        <div class="form-group">
            <label>Base Fee</label>
            <input data-target="base_fee" class="form-control comma-separated" type="text" name="base_fee_txt" required placeholder="Isi Base Fee" @if(old('base_fee_txt')) value="{{old('base_fee_txt')}}" @elseif(isset($detail)) value="{{comma_separated($detail->base_fee)}}" @endif>
            <input type="hidden" id="base_fee" name="base_fee" @if(old('base_fee')) value="{{old('base_fee')}}" @elseif(isset($detail)) value="{{($detail->base_fee)}}" @endif>
        </div>
    </div>
    <div class="col-12 mb-2">
        <div class="form-group">
            <label>Error Fee</label>
            <input data-target="error_fee" class="form-control comma-separated" type="text" name="error_fee_txt" required placeholder="Isi Base Fee" @if(old('error_fee_txt')) value="{{old('error_fee_txt')}}" @elseif(isset($detail)) value="{{comma_separated($detail->error_fee)}}" @endif>
            <input type="hidden" id="error_fee" name="error_fee" @if(old('error_fee')) value="{{old('error_fee')}}" @elseif(isset($detail)) value="{{($detail->error_fee)}}" @endif>
        </div>
    </div>
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