<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Customer;
use App\Models\Master\Item;
use App\Models\Master\Country;
use App\Models\Company;
use App\Models\Master\MSPF;
use App\Models\Master\Material;

use App\Models\Transaction_Header;
use App\Models\Transaction_Detail;
use App\Models\Transaction_Payment;
use App\Models\Transaction_Payment_Attachment;

use Auth;
use DB;
use Carbon\Carbon;
use Exception;
use Validator;

class TransactionController extends Controller
{
    public function index(){
        return view('transaction');
    }

    public function search(Request $req){
        $qr_data = DB::table('transaction_header as a')
                ->select(
                    'a.*', 'b.name as nm_created_by', 'c.name', 'd.customer_name',
                    DB::raw("(a.grand_total*a.usd_rate) as fee_total")
                )
                ->leftJoin('users as b', 'a.created_by', 'b.id')
                ->leftJoin('users as c', 'a.updated_by', 'c.id')
                ->join('mst_customers as d', 'a.customer_id', 'd.customer_id')
                ->where('a.fg_aktif', 1);

        if(isset($req->search)){
            $search = $req->search;
            $qr_data = $qr_data->where('a.inv_number', 'like', '%'.$search.'%')
                        ->orWhere('d.customer_name', 'like', '%'.$search.'%')
                        ->orWhere('d.customer_phone', 'like', '%'.$search.'%');
        }

        $qrData = $qr_data->paginate($req->max_row);

        $data['data'] = $qrData;
        $data['pagination'] =  (string) $qrData->links();

        return json_encode($data);
    }

    public function company_details(Request $req){
        $company_id = $req->company_id;
        $company = Company::find($req->company_id);

        $list_details = $company->details;
        return json_encode($list_details);
    }

    public function add(){
        $data['customers'] = Customer::where('fg_aktif', 1)->get();
        $data['countries'] = Country::all();
        $data['companies_bn'] = Company::where('fg_aktif', 1)->where('company_type', 1)->get();
        $data['companies_cn'] = Company::where('fg_aktif', 1)->where('company_type', 2)->get();
        $data['items'] = Item::all();
        $data['mspf'] = MSPF::where('fg_aktif', 1)->get();
        $data['materials'] = Material::where('fg_aktif', 1)->get();
        $data['mode'] = 'add';
        return view('transaction_form', $data);
    }

    public function view(Request $req){
        $data['transaction'] = Transaction_Header::find($req->header_id);
        $data['customers'] = Customer::where('fg_aktif', 1)->get();
        $data['countries'] = Country::all();
        $data['companies_bn'] = Company::where('fg_aktif', 1)->where('company_type', 1)->get();
        $data['companies_cn'] = Company::where('fg_aktif', 1)->where('company_type', 2)->get();
        $data['items'] = Item::all();
        $data['mspf'] = MSPF::where('fg_aktif', 1)->get();
        $data['materials'] = Material::where('fg_aktif', 1)->get();
        $data['mode'] = 'view';
        return view('transaction_form', $data);
    }

    public function save(Request $req){
        $validator = Validator::make($req->all(), [
            'customer_id' => 'nullable|required_without:header_id',
            'usd_rate' => 'nullable|required_without:header_id',
            'grading_banknotes' => 'nullable|required_without:grading_coins',
            'grading_coins' => 'nullable|required_without:grading_banknotes'
        ], [
            'customer_id.required_without' => 'Customer harus dipilih',
            'usd_rate.required_without' => 'Rate USD harus diisi', 
            'grading_banknotes.required_without' => 'Uang yang akan di grading harus diisi',
            'grading_coins.required_without' => 'Uang yang akan di grading harus diisi'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user()->id;
        DB::beginTransaction();
        try{
            if(isset($req->header_id)){
                throw new Exception('Tidak dapat melakukan update pada data transaksi');
            }

            $header = new Transaction_Header();
            $header->customer_id = $req->customer_id;
            $header->usd_rate = $req->usd_rate;
            $header->inv_number = "";
            $header->header_comment = $req->notes;
            $header->grand_total = 0;
            $header->created_by = $user;
            $header->save();

            $customer = Customer::find($req->customer_id);
            $date = Carbon::now();
            $runningNumber = str_pad($header->header_id, 5, '0', STR_PAD_LEFT);
            $header->inv_number = $runningNumber."/".$date->format('Y/m/d')."/".$customer->customer_code;

            if(isset($req->grading_banknotes)){
                foreach($req->grading_banknotes as $bank_notes){
                    $detail = new Transaction_Detail();
                    $detail->header_id = $header->header_id;
                    $detail->detail_type = 0;
                    $detail->company_id = $bank_notes['company_id'];
                    $detail->kd_negara = $bank_notes['kd_negara'];
                    $detail->item_id = $bank_notes['item_id'];
                    $detail->detail_year = $bank_notes['detail_year'];
                    $detail->company_detail_id = $bank_notes['company_detail_id'];
                    $detail->detail_serial_number = $bank_notes['detail_serial_number'];

                    $detail->detail_has_error = $bank_notes['detail_has_error'];
                    $detail->detail_oversize_fee = $bank_notes['detail_oversize_fee'];
                    $detail->detail_pedigree_fee = $bank_notes['detail_pedigree_fee'];
                    $detail->detail_onsite_fee = $bank_notes['detail_onsite_fee'];

                    $detail->detail_description = $bank_notes['description'];

                    $detail->created_by = $user;
                    $detail->save();

                    $subtotal = $detail->detail_oversize_fee
                    +
                    $detail->detail_pedigree_fee
                    +
                    $detail->detail_onsite_fee;

                    $header->grand_total += $subtotal;
                }
            }

            if(isset($req->grading_coins)){
                foreach($req->grading_coins as $coins){
                    $detail = new Transaction_Detail();
                    $detail->header_id = $header->header_id;
                    $detail->detail_type = 1;
                    $detail->company_id = $coins['company_id'];
                    $detail->kd_negara = $coins['kd_negara'];
                    $detail->detail_denomination = $coins['detail_denomination'];
                    $detail->company_detail_id = $coins['company_detail_id'];
                    $detail->detail_description = $bank_notes['description'];

                    $detail->detail_has_error = $coins['detail_has_error'];
                    $detail->detail_ncs_fee = $coins['detail_ncs_fee'];
                    $detail->detail_oversize_fee = $coins['detail_oversize_fee'];
                    $detail->detail_pedigree_fee = $coins['detail_pedigree_fee'];
                    $detail->detail_onsite_fee = $coins['detail_onsite_fee'];

                    $detail->detail_year = $coins['detail_year'];
                    $detail->detail_material = $coins['detail_material'];
                    $detail->detail_mspf = $coins['detail_mspf'];

                    $detail->created_by = $user;
                    $detail->save();

                    $subtotal = $detail->detail_ncs_fee
                    +
                    $detail->detail_oversize_fee
                    +
                    $detail->detail_pedigree_fee
                    +
                    $detail->detail_onsite_fee;

                    $header->grand_total += $subtotal;
                }
            }

            $header->save();

            DB::commit();
            return redirect()->route('transaction.view', ['header_id' => $header->header_id])->with(['success_message' => 'Berhasil menyimpan data']);
        }catch(Exception $e){
            DB::rollback();
            return redirect()->back()->withInput()->with(['error_message' => 'Terjadi kesalahan'.$e->getMessage()]);
        }
    }

    public function delete(Request $req){
        $header_id = $req->header_id;

        DB::beginTransaction();
        try{
            $item = Transaction_Header::find($header_id);
            $item->fg_aktif = 0;
            $item->save();

            foreach($item->details as $detail){
                $detail->fg_aktif = 0;
                $detail->save();
            }

            foreach($item->payments as $payment){
                $payment->fg_aktif = 0;
                $payment->save();
            }

            DB::commit();
            http_response_code(200);
            exit(json_encode(['Message' => 'Data berhasil dihapus']));
        }catch(Exception $e){
            DB::rollback();
            http_response_code(405);
            exit(json_encode(['Message' => "Terjadi kesalahan, ".$e->getMessage()]));
        }
    }

    public function add_payment(Request $req){
        $header_id = $req->header_id;
        $data['header'] = Transaction_Header::find($header_id);
        $data['mode'] = 'add';
        return view('payment_form', $data);
    }

    public function view_payment(Request $req){
        $header_id = $req->header_id;
        $payment_id = $req->payment_id;
        $data['header'] = Transaction_Header::find($header_id);
        $data['payment'] = Transaction_Payment::find($payment_id);
        $data['mode'] = 'edit';
        return view('payment_form', $data);
    }

    public function search_payment_attachment(Request $req){
        $payment_id = $req->payment_id;
        $attachments = Transaction_Payment_Attachment::where('payment_id', $payment_id)->get();

        $return_data = [];
        foreach($attachments as $attachment){
            array_push($return_data, asset("/").$attachment->image_path.$attachment->image_name);
        }
        return json_encode($return_data);
    }

    public function save_payment(Request $req){
        $validator = Validator::make($req->all(), [
            'header_id' => 'required',
            'payment_date' => 'required',
            'payment_amount' => 'required|numeric',
            'payment_images_add' => 'required_without:payment_images_keep',
            'payment_images_keep' => 'required_without:payment_images_add'
        ],[
            'header_id.required' => 'Data transaksi tidak ditemukan',
            'payment_date.required' => 'Tanggal pembayaran harus diisi',
            'payment_amount.required' => 'Silahkan masukan jumlah pembayaran',
            'payment_amount.numeric' => 'Jumlah bayar harus berupa angka'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $header_id = $req->header_id;
        $header = Transaction_Header::find($header_id);

        DB::beginTransaction();
        try{
            if(!isset($req->payment_id)){
                $payment = new Transaction_Payment();
                $payment->header_id = $req->header_id;
                $payment->created_by = Auth::user()->id;
            }else{
                $payment = Transaction_Payment::find($req->payment_id);
                $payment->updated_by = Auth::user()->id;
            }

            $payment->payment_date = Carbon::parse($req->payment_date);
            $payment->payment_notes = $req->payment_notes;
            $payment->payment_amount = $req->payment_amount;
            $payment->save();

            $image_to_keep = [];
            if(isset($req->payment_images_keep)){
                foreach($req->payment_images_keep as $k=>$keep_image){
                    $image = Transaction_Payment_Attachment::find($keep_image);
                    $image->updated_by = Auth::user()->id;
                    $image->save();
                    array_push($image_to_keep, $image->image_id);
                }
            }

            if(isset($req->payment_images_add)){
                foreach($req->payment_images_add as $k=>$add_image){
                    $image = new Transaction_Payment_Attachment();
                    $image->payment_id = $payment->payment_id;
                    $ext = getAllowedBase64Extension($add_image);
                    $name = uniqid()."_".date('dMY')."_".$payment->payment_id.".".$ext;
                    $path = "/images/payment/";

                    $exp = explode(',', $add_image);
                    //we just get the last element with array_pop
                    $base64 = array_pop($exp);
                    //decode the image and finally save it
                    $file = base64_decode($base64);
                    // $file = str_replace('data:image/png;base64,', '', $add_image);
                    $success = file_put_contents(public_path().$path.$name, $file);

                    $image->image_name = $name;
                    $image->image_path = $path;
                    $image->created_by = Auth::user()->id;
                    $image->save();

                    array_push($image_to_keep, $image->image_id);
                }
            }

            $discarding = Transaction_Payment_Attachment::where('payment_id', $payment->payment_id)
            ->whereNotIn('attachment_id', $image_to_keep)
            ->get();

            foreach($discarding as $d){
                $path = $d->image_path;
                $name = $d->image_name;
                $file = public_path().$path.$name;
                if (file_exists($file)) {
                    unlink($file);
                }

                $d->delete();
            }

            DB::commit();
            return redirect()->route('transaction.payment.view', ['header_id' => $header->header_id, 'payment_id' => $payment->payment_id])->with(['success_message' => 'Transaksi berhasil dibuat']);
        }catch(Exception $e){
            DB::rollback();
            return redirect()->back()->withInput()->with(['error_message' => 'Terjadi kesalahan'.$e->getMessage()]);
        }
    }

    public function delete_payment(Request $req){

    }
}
