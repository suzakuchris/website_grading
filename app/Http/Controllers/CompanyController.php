<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use DB;
use Validator;
use Carbon\Carbon;
use App\Models\Master\Item;

use App\Models\Company;
use App\Models\Company_Type;
use App\Models\Company_Detail;
use App\Models\Company_Detail_Type;

class CompanyController extends Controller
{
    public function index(){
        return view('master.company');
    }

    public function search(Request $req){
        $qr_data = DB::table('mst_company as a')
                ->select(
                    'a.*', 'b.name as create_name', 'c.name as update_name', 'd.type_name'
                )
                ->join('mst_company_type as d', 'a.company_type', 'd.type_id')
                ->leftJoin('users as b', 'a.created_by', 'b.id')
                ->leftJoin('users as c', 'a.updated_by', 'c.id')
                ->where('a.fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where(function($query) use($search){
                $query->where('a.company_name', 'like', '%'.$search.'%');
            });
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();
        
        return json_encode($data);
    }

    public function delete(Request $req){
        $company_id = $req->company_id;

        $qr_transaction = DB::table('transaction_detail as a')
                          ->join('transaction_header as b', 'a.header_id', 'b.header_id')
                          ->where('a.company_id', $company_id)
                          ->where('a.fg_aktif', 1)
                          ->where('b.fg_aktif', 1)
                          ->first();

        if(isset($qr_transaction)){
            http_response_code(405);
            exit(json_encode(['Message' => "Terjadi kesalahan, data ini masih terhubung ke data transaksi."]));
        }

        DB::beginTransaction();
        try{
            $item = Company::find($company_id);
            $item->fg_aktif = 0;
            $item->save();

            DB::commit();
            http_response_code(200);
            exit(json_encode(['Message' => 'Data berhasil dihapus']));
        }catch(Exception $e){
            DB::rollback();
            http_response_code(405);
            exit(json_encode(['Message' => "Terjadi kesalahan, ".$e->getMessage()]));
        }
    }

    public function add(){
        $data['types'] = Company_Type::where('fg_aktif', 1)->get();
        $data['mode'] = 'add';
        return view('master.company_form', $data);
    }

    public function view(Request $req){
        $data['types'] = Company_Type::where('fg_aktif', 1)->get();
        $data['company'] = Company::find($req->id);
        $data['mode'] = 'edit';
        return view('master.company_form', $data);
    }

    public function upsert(Request $req){
        $validator = Validator::make($req->all(), [
            'company_name' => 'required|max:255',
            'company_type' => 'required'
        ], [
            'company_name.required' => 'Nama company harus diisi',
            'company_name.max' => 'Nama company maksimal :max kata',
            'company_type.required' => 'Tipe company harus dipilih'
        ]);

        if($validator->fails()) {
            $errorArr = json_decode($validator->errors());//$validator->messages();
            $errorStr ='';

            foreach ($errorArr as $k=>$item) {
                if($k != 0){
                    $errorStr .= ", ".$item[0];
                }else{
                    $errorStr .= $item[0];
                }
            }

            return redirect()->back()->withInput()->with(['error_message' => $errorStr]);
        }

        $user = Auth::user()->id;
        DB::beginTransaction();
        try{

            if(isset($req->company_id)){
                $company = Company::find($req->company_id);
                $company->updated_by = $user;
            }else{
                $company = new Company();
                $company->created_by = $user;
            }

            $company->company_type = $req->company_type;
            $company->company_name = $req->company_name;
            $company->save();

            DB::commit();
            return redirect()->route('master.company.view', ['id' => $company->company_id])->with(['success_message' => 'Transaksi berhasil dibuat']);
        }catch(Exception $e){
            DB::rollback();
            return redirect()->back()->withInput()->with(['error_message' => 'Terjadi kesalahan, '.$e->getMessage()]);
        }
    }

    public function detail_add(Request $req){
        $data['types'] = Company_Detail_Type::where('fg_aktif', 1)->get();
        $data['company_id'] = $req->id;
        $data['mode'] = 'add';
        return view('master.company_detail_form', $data);
    }

    public function detail_view(Request $req){
        $data['types'] = Company_Detail_Type::where('fg_aktif', 1)->get();
        $data['detail'] = Company_Detail::find($req->detail_id);
        $data['company_id'] = $data['detail']->detail_company;
        $data['mode'] = 'add';
        return view('master.company_detail_form', $data);
    }

    public function detail_upsert(Request $req){
        $validator = Validator::make($req->all(), [
            'detail_name' => 'required|max:255',
            'detail_type' => 'required',
            'base_fee' => 'required', 
            'error_fee' => 'required'
        ], [
            'detail_name.required' => 'Nama tier harus diisi',
            'detail_name.max' => 'Nama tier maksimal :max kata',
            'detail_type.required' => 'Tipe tier harus dipilih',
            'base_fee.required' => 'Base fee harus dimasukan',
            'error_fee.required' => 'Error fee harus dimasukan'
        ]);

        if($validator->fails()) {
            $errorArr = json_decode($validator->errors());//$validator->messages();
            $errorStr ='';

            foreach ($errorArr as $k=>$item) {
                if($k != 0){
                    $errorStr .= ", ".$item[0];
                }else{
                    $errorStr .= $item[0];
                }
            }

            return redirect()->back()->withInput()->with(['error_message' => $errorStr]);
        }

        $user = Auth::user()->id;
        $company = Company::find($req->company_id);
        DB::beginTransaction();
        try{

            if(isset($req->detail_id)){
                $detail = Company_Detail::find($req->detail_id);
                $detail->updated_by = $user;
            }else{
                $detail = new Company_Detail();
                $detail->detail_company = $company->company_id;
                $detail->created_by = $user;
            }

            $detail->detail_name = $req->detail_name;
            $detail->detail_type = $req->detail_type;
            $detail->base_fee = $req->base_fee;
            $detail->error_fee = $req->error_fee;
            $detail->save();

            DB::commit();
            return redirect()->route('master.company.view', ['id' => $company->company_id])->with(['success_message' => 'Transaksi berhasil dibuat']);
        }catch(Exception $e){
            DB::rollback();
            return redirect()->back()->withInput()->with(['error_message' => 'Terjadi kesalahan, '.$e->getMessage()]);
        }
    }
}
