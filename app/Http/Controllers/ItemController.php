<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use DB;
use Validator;
use Carbon\Carbon;
use App\Models\Master\Item;

class ItemController extends Controller
{
    public function index(){
        return view('master.item');
    }

    public function search(Request $req){
        $qr_data = DB::table('mst_item as a')
                ->select(
                    'a.*', 'b.name as create_name', 'c.name as update_name', 'd.country_name'
                )
                ->leftJoin('users as b', 'a.created_by', 'b.id')
                ->leftJoin('users as c', 'a.updated_by', 'c.id')
                ->leftJoin('apps_countries as d', 'a.kd_negara', 'd.id');

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where(function($query) use($search){
                $query->where('a.nominal', 'like', '%'.$search.'%')
                ->orWhere('a.item_code', 'like', '%'.$search.'%');
            });
        }

        if(isset($req->country)){
            $qr_data = $qr_data->where('d.id', $req->country);
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();
        
        return json_encode($data);
    }

    public function view(Request $req){
        $item_id = $req->item_id;
        $qr_data = DB::table('mst_item')
                    ->where('item_id', $item_id)
                    ->first();

        return json_encode($qr_data);
    }

    public function upsert(Request $req){
        $validator = Validator::make($req->all(), [
            'item_code' => 'required|max:255',
            'tahun' => 'required|numeric',
            'kd_negara' => 'required|numeric',
            'nominal' => 'required|numeric',
        ],[
            'item_code.required' => 'Kode barang harus dimasukan',
            'item_code.max' => 'Kode barang tidak boleh lebih dari :max karakter',
            
            'tahun.required' => 'Data tahun harus dipilih',
            'tahun.numeric' => 'Tahun harus berupa angka',
            
            'kd_negara.required' => 'Kode Negara harus dipilih',
            'kd_negara.numeric' => 'Kode Negara harus berupa angka',
            
            'nominal.required' => 'Nominal harus dimasukan',
            'nominal.numeric' => 'Nominal harus berupa angka',
        ]);

        if($validator->fails()) {
            $errorArr = json_decode($validator->errors());//$validator->messages();
            $errorStr ='';

            foreach ($errorArr as $item) {
                $errorStr .= '<div>'.$item[0].'</div>';
            }

            http_response_code(405);
            exit(json_encode(['Message' => $errorStr]));
        }

        $user = Auth::user()->id;
        DB::beginTransaction();
        try{
            if(isset($req->item_id)){
                $item = Item::find($req->item_id);
                $item->updated_by = $user;
            }else{
                $item = new Item();
                $item->created_by = $user;
            }

            $item->item_code = $req->item_code;
            $item->tahun = $req->tahun;
            $item->kd_negara = $req->kd_negara;
            $item->nominal = $req->nominal;
            $item->save();

            DB::commit();
            http_response_code(200);
            exit(json_encode(['Message' => 'Data berhasil disimpan']));
        }catch(Exception $e){
            DB::rollback();
            http_response_code(405);
            exit(json_encode(['Message' => "Terjadi kesalahan, ".$e->getMessage()]));
        }
    }

    public function delete(Request $req){
        $item_id = $req->item_id;

        DB::beginTransaction();
        try{
            $item = Item::find($item);
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
}
