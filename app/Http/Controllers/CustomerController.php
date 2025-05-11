<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Customer;

use DB;
use Auth;
use Exception;
use Carbon\Carbon;
use Validator;

class CustomerController extends Controller
{
    public function index(){
        return view('master.customer');
    }

    public function search(Request $req){
        $qr_data = DB::table('mst_customers as a')
                ->select(
                    'a.*', 'b.name', 'c.name'
                )
                ->leftJoin('users as b', 'a.created_by', 'b.id')
                ->leftJoin('users as c', 'a.updated_by', 'c.id')
                ->where('a.fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where('a.customer_name', 'like', '%'.$search.'%')
                        ->orWhere('a.customer_email', 'like', '%'.$search.'%')
                        ->orWhere('a.customer_phone', 'like', '%'.$search.'%')
                        ->orWhere('a.customer_address', 'like', '%'.$search.'%');
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();

        return json_encode($data);
    }

    public function add(){
        $data['mode'] = 'add';
        return view('master.customer_form', $data);
    }

    public function view(Request $req){
        $data['mode'] = 'view';
        $data['customer'] = Customer::find($req->customer_id);
        return view('master.customer_form', $data);
    }

    public function edit(Request $req){
        $data['mode'] = 'edit';
        $data['customer'] = Customer::find($req->customer_id);
        return view('master.form.customer', $data);
    }

    public function upsert(Request $req){
        $validator = Validator::make($req->all(), [
            'customer_name' => 'required|max:255',
            'code' => 'required|max:6',
            // 'customer_email' => 'required|email',
            // 'customer_phone' => 'required|numeric',
        ],[
            'customer_name.required' => 'Nama customer harus diisi',
            'customer_name.max' => 'Nama customer maksimal :max karakter',
            'code.max' => 'Customer code maksimal :max karakter',
            'code.required' => 'Customer code harus diisi',
            // 'customer_email.required' => 'Email customer harus diisi',
            // 'customer_email.email' => 'Format email salah',
            // 'customer_phone.required' => 'No. Hp harus diisi',
            // 'customer_phone.numeric' => 'No. Hp harus berupa angka',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try{
            if(!isset($req->customer_id)){
                $customer = new Customer();
                $customer->created_by = Auth::user()->id;
                //cek code
                if(!isset($req->code)){
                    throw new Exception('Customer Code harus diisi');
                }
                if(strlen($req->code) > 6){
                    throw new Exception('Customer Code terlalu panjang!');
                }
                $qr_cek = Customer::where('fg_aktif', 1)->where('customer_code', $req->code)->first();
                if(isset($qr_cek)){
                    throw new Exception('Customer Code sudah ada di database');
                }
                $customer->customer_code = $req->code;
            }else{
                $customer = Customer::find($req->customer_id);
                $customer->updated_by = Auth::user()->id;
            }

            $customer->customer_name = $req->customer_name;
            $customer->customer_email = $req->customer_email;
            $customer->customer_phone = $req->customer_phone;
            $customer->customer_address = $req->customer_address;
            $customer->save();

            DB::commit();
            return redirect()->route('master.customer')->with(['success_message' => 'Berhasil menyimpan data']);
        }catch(Exception $e){
            DB::rollback();
            return redirect()->back()->withInput()->with(['error_message' => 'Terjadi kesalahan'.$e->getMessage()]);
        }
    }

    public function delete(Request $req){
        $customer_id = $req->customer_id;

        DB::beginTransaction();
        try{
            $customer = Customer::find($customer_id);
            $customer->fg_aktif = 0;
            $customer->save();

            DB::commit();
            http_response_code(200);
            exit(json_encode(['Message' => 'Data berhasil dihapus']));
        }catch(Exception $e){
            DB::rollback();
            http_response_code(405);
            exit(json_encode(['Message' => "Terjadi kesalahan, ".$e->getMessage()]));
        }
    }
}
