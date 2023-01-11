<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\WaitingList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;

class WaitinglistController extends Controller
{
    var $arrPop = ['Bogor Valley', 'LIFEMEDIA', 'HABITAT', 'SINDUADI', 'GREENNET', 'X-LIFEMEDIA', 'LDP LIFEMEDIA', 'LDP X-LIFEMEDIA', 'JIP', 'Jogja Tronik', 'LDP JIP'];
    var $arrPopCode = ['BV', 'LM', 'HB', 'SN',  'GN', 'XM', 'LD', 'LX',  'JP', 'JT', 'LJ'];
    var $arrPiStatus = [['Blum Bayar', 'danger'], ['Lunas', 'green'], ['Expired', 'dark']];


    public function index()
    {

        $title = 'Waiting List';
        $subTitle = '';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $arrfield = $this->arrFieldIndex();
        $i = 0;
        $tableColumn[$i]['data'] = 'DT_RowIndex';
        $tableColumn[$i]['name'] = 'DT_RowIndex';
        $tableColumn[$i]['orderable'] = 'false';
        $tableColumn[$i]['searchable'] = 'false';
        $tableColumn[$i]['visible'] = true;
        $i += 1;
        $tableColumn[$i]['data'] = 'wi_number';
        $tableColumn[$i]['name'] = 'wi_number';
        $tableColumn[$i]['orderable'] = 'true';
        $tableColumn[$i]['searchable'] = 'true';
        $tableColumn[$i]['visible'] = true;

        foreach ($arrfield as  $key => $value) {
            $i++;
            if ($value['visible']) {
                $tableColumn[$i]['data'] = $key;
                $tableColumn[$i]['name'] = $value['label'];
                $tableColumn[$i]['orderable'] = $value['orderable'];
                $tableColumn[$i]['searchable'] = $value['searchable'];
                $tableColumn[$i]['visible'] = $value['visible'];
            }
        }


        $tableColumn[$i + 1]['data'] = 'detail';
        $tableColumn[$i + 1]['name'] = 'detail';
        $tableColumn[$i + 1]['visible'] = true;


        $load['arr_field'] = $arrfield;
        $load['table_column'] = json_encode(array_values($tableColumn));

        return view('pages/waitinglist/index', $load);
    }

    public function form(Request $request, $wiId = '')
    {
        $title = 'Add Waiting List';
        $subTitle = '';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $arrfield = $this->arrField();

        $load['arr_field'] = $arrfield;
        if ($wiId) {
            $action = 'editData';
        } else {
            $action = 'addData';
        }
        $load['action'] = $action;


        return view('pages/waitinglist/form', $load);
    }

    public function store(Request $request)
    {

        request()->validate([
            'file_identitas' => 'required',
            'form_berlanggan' => 'required',
            'lembar_survei' => 'required',
            'foto_rumah' => 'required',
        ]);


        $data = $request->input('data');
        $action = $request->input('action');
        $files = $request->input('file');

        $arrfield = $this->arrField();
        $allowedfileExtension = ['jpg', 'png', 'jpeg', 'svg'];

        $postVal = [];
        foreach ($data as $key => $value) {
            if ($value) {
                $postVal[$key] = $value;
            }
        }

        $fileKeyy = [];
        foreach ($arrfield as $key => $value) {
            foreach ($value[1] as $kf => $vf) {
                if ($vf['form_type'] == 'file') {
                    $fileKeyy[] = $kf;
                }
            }
        }

        if ($action == 'addData') {
            $lastId = DB::table('t_waiting_list_new')->orderByDesc('wi_number')->first();

            if (str_contains($lastId->wi_number, 'WI')) {
                $num = substr($lastId->wi_number, 2);
                $newWiNum = 'WI' . sprintf('%06d', $num + 1);
            } else {
                $newWiNum = 'Wi' . sprintf('%06d', $lastId->wi_number + 1);
            }
            //dd($newWiNum,$lastId);
            $postVal['wi_number'] = $newWiNum;

            DB::table('t_waiting_list_new')->insertGetId($postVal);

            $postfile = [];
            $path = 'files/pelanggan_baru';
            foreach ($fileKeyy as $key => $value) {
                $file = $request->file($value);

                if ($file) {
                    $fileName = $value . '_' . rand();
                    $extension = $file->getClientOriginalExtension();
                    $check = in_array($extension, $allowedfileExtension);

                    if ($check) {
                        $file->move($path, $fileName);
                        $fullPath = $path . '/' . $fileName;

                        $postfile[$value]['wi_number'] = $newWiNum;
                        $postfile[$value]['wi_file_key'] = $value;
                        $postfile[$value]['wi_file_title'] = $arrfield[3][1][$value]['label'];
                        $postfile[$value]['wi_file_name'] = $fullPath;
                    }
                }
            }

            DB::table('t_waiting_list_fie')->insert($postfile);
            $this->createInv($newWiNum, $postVal['wi_bill_instalinv'], $postVal['wi_bill_regular'] ?? '', $postVal['wi_bill_period'] ?? '');
	    die;
            $request->session()->flash('success', 'Add Waitinglist Success!');

            return redirect(route('waitinglist-index'));
        }
    }

    public function createInv($wiNumber, $invInstalasi, $wiBillReguler, $wiBillPeriod)
    {
        $wiData = WaitingList::where('wi_number', $wiNumber)->first();

        $lastPi = DB::table('t_invoice_wi')
            ->whereRaw("YEAR(inv_post) = '" . date('Y') . "'")
            ->whereRaw("MONTH(inv_post) = '" . date('m') . "'")
            ->orderByDesc('inv_post')
            ->first();

        if (isset($lastPi->inv_number)) {
            $lastPiNum = substr($lastPi->inv_number, -3);
            $lastPiNum += 1;
            $inv_number = 'PI' . $wiData->wi_number . date('ym') . sprintf('%02d', $lastPiNum);
        } else {
            $lastPiNum = '01';
            $inv_number = 'PI' .  $wiData->wi_number . date('ym') . $lastPiNum;
        }

        $invStart = $wiData->wi_svc_begin ?? date('Y-m-d');
        $invEnd = date('Y-m-d', strtotime($invStart . ' +29 days'));

        $postPi[0]['inv_number'] = $inv_number;
        $postPi[0]['cust_number'] = $wiNumber;
        $postPi[0]['sp_code'] =  $wiData->sp_code;
        $postPi[0]['inv_type'] = '2';
        $postPi[0]['inv_due'] = $invStart;
        $postPi[0]['inv_post'] = date('Y-m-d H:m:s');
        $postPi[0]['inv_start'] = $invStart;
        $postPi[0]['inv_end'] = $invEnd;
        $postPi[0]['inv_currency'] = "IDR";
        $postPi[0]['reaktivasi_pi'] = 10;

        $pkg = DB::table('t_service_pkg')->where('sp_name', $wiData->sp_code)->first();

        $svcAmount = '';
        if ($wiBillPeriod == '1') {
            $svcAmount = $pkg->sp_reguler;
        } elseif ($wiBillPeriod == '3') {
            $svcAmount = $pkg->sp_reguler3;
        } elseif ($wiBillPeriod == '6') {
            $svcAmount = $pkg->sp_reguler6;
        } elseif ($wiBillPeriod == '12') {
            $svcAmount = $pkg->sp_reguler12;
        }
        $amount = intval($wiBillReguler ? $wiBillReguler : $svcAmount);
        //dd($amount);
        $postValItem[0]['ii_type'] = '2';
        $postValItem[0]['inv_number'] = $inv_number;
        $postValItem[0]['ii_info'] = 'Biaya Layanan ' . $wiData->sp_code . '  Periode Pertama';
        $postValItem[0]['ii_amount'] = $amount;
        $postValItem[0]['ii_order'] = '1';
        $postValItem[0]['ii_recycle'] = '2';

        $postValItem[1]['ii_type'] = '7';
        $postValItem[1]['inv_number'] = $inv_number;
        $postValItem[1]['ii_info'] = 'PPN 11 %';
        $postValItem[1]['ii_amount'] = ($amount * 11) / 100;
        $postValItem[1]['ii_order'] = '2';
        $postValItem[1]['ii_recycle'] = '2';

        if ($invInstalasi == 1) {
            $postValItem[2]['ii_type'] = '1';
            $postValItem[2]['inv_number'] = $inv_number;
            $postValItem[2]['ii_info'] = 'Biaya Instalasi Layanan ' . $wiData->sp_code;
            $postValItem[2]['ii_amount'] = $pkg->sp_setup;
            $postValItem[2]['ii_order'] = '1';
            $postValItem[2]['ii_recycle'] = '2';

            $postValItem[1]['ii_amount'] = (($amount + $pkg->sp_setup) * 11) / 100;
        }

        //dd($postPi, $postValItem);

        DB::table('t_invoice_porfoma')->insert($postPi);
        DB::table('t_inv_item_porfoma')->insert($postValItem);
    }



    public function detail(Request $request, $wiId)
    {

        $title = 'Detail Waitinglist ';
        $subTitle = '';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $wiData = WaitingList::where('wi_number', $wiId)->first();


        $invoice = DB::table('t_invoice_porfoma')->where('cust_number', $wiId)
            ->leftJoin('t_inv_item_porfoma', function ($join) {
                $join->on('t_invoice_porfoma.inv_number', '=', 't_inv_item_porfoma.inv_number')->where('ii_recycle', '<>', 1);
            })->get();

        $file = DB::table('t_waiting_list_fie')->where('wi_number', $wiId)
            ->get();

        //dd($wiId);

        $susunData = [];
        $susunDataSummary = [];

        $totals = 0;
        foreach ($invoice->toArray() as $key => $value) {
            foreach ($value as $keys => $values) {
                $susunData[$keys] = $values;
            }
            $susunDataSummary[$value->inv_number]['code'] = urlencode(base64_encode($value->inv_number . ';' . $value->inv_number));
            $susunDataSummary[$value->inv_number]['inv_number'] = $value->inv_number;
            $susunDataSummary[$value->inv_number]['inv_status'] = $this->arrPiStatus[$value->inv_status];
            $susunDataSummary[$value->inv_number]['inv_post'] = Carbon::parse($value->inv_post)->isoFormat('D MMMM Y');
            $susunDataSummary[$value->inv_number]['inv_due'] = Carbon::parse($value->inv_due)->isoFormat('D MMMM Y');
            $susunDataSummary[$value->inv_number]['inv_paid'] = Carbon::parse($value->inv_paid)->isoFormat('D MMMM Y - HH:mm');
            $susunDataSummary[$value->inv_number]['inv_start'] = Carbon::parse($value->inv_start)->isoFormat('D MMMM Y');
            $susunDataSummary[$value->inv_number]['inv_end'] = Carbon::parse($value->inv_end)->isoFormat('D MMMM Y');
            $susunDataSummary[$value->inv_number]['inv_info'] = $value->inv_info;
            $susunDataSummary[$value->inv_number]['inv_type'] = $value->inv_info;
            $susunDataSummary[$value->inv_number]['sp_code'] = $value->inv_info;
            $susunDataSummary[$value->inv_number]['item'][$key]['total'] = 0;
            $susunDataSummary[$value->inv_number]['item'][$key]['ii_order'] = $value->ii_order;
            $susunDataSummary[$value->inv_number]['item'][$key]['ii_type'] = $value->ii_type;
            $susunDataSummary[$value->inv_number]['item'][$key]['ii_amount'] = 'Rp. ' . SchRp($value->ii_amount);
            $susunDataSummary[$value->inv_number]['item'][$key]['ii_info'] = $value->ii_info;
            $susunDataSummary[$value->inv_number]['item'][$key]['total'] += $value->ii_amount;
        }

        //$originalCode = $susunData['inv_number'] . ';' . $susunData['inv_number'];
        //$encryptionCode = urlencode(base64_encode($originalCode));


        //dd($encryptionCode); 
        $load['inv_status'] = $susunData['inv_status'];
        $load['cust_number'] = $susunData['cust_number'];
        $load['wiData'] = $wiData->toArray();
        $load['datas'] = $susunData;
        $load['data_summary'] = $susunDataSummary;
        $load['data_file'] = $file;
        //$load['url'] = 'http://webhook.lifemedia.id/checkout?code=' . $encryptionCode;
        //dd($load);
        $load['arr_field'] = $this->arrField();

        return view('pages/waitinglist/detail', $load);
    }



    /*public function konfirmasi(Request $request)
    {

        $wiNumber = $request->input('wi_number');
        $wiData = WaitingList::where('wi_number', $wiNumber)
            ->leftJoin('t_invoice_porfoma', 't_waiting_list_new.wi_number', '=', 't_invoice_porfoma.cust_number')
            ->get();



        $pop = $this->arrPopCode[$wiData[0]->wi_pop];

        $lastCust = Customer::where('cust_pop', $wiData[0]->wi_pop)->orderByDesc('cust_number')->first();
        if (isset($lastCust->cust_number)) {
            $lastnum = $lastCust->cust_number;
        } else {
            $lastnum = 0;
        }
        $numCustNumber =  sprintf('%06d', substr($lastnum, -6) + 1);
        $newCustNumber = $pop . $numCustNumber;

        $getLastSpk = DB::table('t_field_task')
            ->select('ft_number')
            ->where('ft_number', 'like', 'SP%')
            ->whereRaw('MONTH(ft_received) =' . date('m'))
            ->whereRaw('YEAR(ft_received) =' . date('Y'))
            ->orderByDesc('ft_number')
            ->first();

        if (isset($getLastSpk->ft_number)) {
            $explodeSpkNumber = explode('/', $getLastSpk->ft_number);
            //echo $getLastSpk->ft_number.'<br>';
            $newSpkNumber1 = sprintf("%05d", substr($explodeSpkNumber[0], 2) + 1);
            $newSpkNumber2 = sprintf("%05d", substr($explodeSpkNumber[0], 2) + 2);
            //echo 'SP'.$newSpkNumber."/NOC/".date('m').'/'.date('y');die;
        } else {
            $newSpkNumber1 = '000001';
            $newSpkNumber2 = '000002';
        }

        foreach ($wiData as $key => $value) {
            DB::table('t_invoice_porfoma')->where('inv_number', $value->inv_number)->update(['cust_number' => $newCustNumber]);

            $postCust['cust_number'] = $newCustNumber;
            $postCust['hp_number'] = $value->wi_home_pass;
            $postCust['cust_member_card'] = $value->wi_member_card ?? '';
            $postCust['cust_pop'] = $value->wi_pop ?? '';
            $postCust['cust_job'] = $value->wi_job ?? '';
            $postCust['cust_city'] = $value->wi_city ?? '';
            $postCust['cust_prov'] = $value->wi_prov ?? '';
            $postCust['cust_name'] = $value->wi_name ?? '';
            $postCust['cust_company'] = $value->wi_company ?? '';
            $postCust['cust_business'] = $value->wi_business ?? '';
            $postCust['cust_npwp'] = $value->wi_npwp ?? '';
            $postCust['cust_sex'] = $value->wi_sex ?? '';
            $postCust['cust_birth_date'] = $value->wi_birth_date ?? '';
            $postCust['cust_ident_type'] = $value->wi_ident_type ?? '';
            $postCust['cust_ident_number'] = $value->wi_ident_number ?? '';
            $postCust['cust_address'] = $value->wi_address ?? '';
            $postCust['cust_zip'] = $value->wi_zip_code ?? '';
            $postCust['cust_phone'] = $value->wi_phone ?? '';
            $postCust['cust_hp'] = $value->wi_telp ?? '';
            $postCust['cust_hp_contact'] = $value->wi_phone_name ?? '';
            $postCust['cust_email'] = $value->wi_email ?? '';
            $postCust['created'] = date('Y-m-d') ?? '';
            $postCust['cust_kecamatan'] = $value->wi_kecamatan ?? '';
            $postCust['cust_kelurahan'] = $value->wi_kelurahan ?? '';
            $postCust['cust_bill_address'] = $value->wi_bill_address ?? '';
            $postCust['cust_bill_zip'] = $value->wi_bill_zip_code ?? '';
            $postCust['cust_bill_email'] = $value->wi_bill_email ?? '';
            $postCust['cust_bill_phone'] = $value->wi_bill_phone ?? '';
            $postCust['cust_bill_contact'] = $value->wi_bill_contact ?? '';
            $postCust['cust_bill_info'] = $value->wi_bill_desc ?? '';

            $postCupkg['cust_number'] = $newCustNumber;
            $postCupkg['sp_code'] = $value->sp_code;
            $postCupkg['cupkg_svc_begin'] = $value->wi_svc_begin ?? '';
            $postCupkg['cupkg_acc_type'] = $value->wi_type ?? '';
            $postCupkg['cupkg_status'] = 3;
            $postCupkg['cupkg_trial'] = $value->wi_trial ?? '';
            $postCupkg['cupkg_bill_period'] = $value->wi_bill_period ?? '';
            $postCupkg['cupkg_bill_type'] = $value->wi_bill_type ?? '';
            $postCupkg['cupkg_bill_lastpaid'] = $value->inv_number ?? '';
            $postCupkg['cupkg_bill_autogen'] = $value->wi_bill_autogen ?? '';
            $postCupkg['cupkg_bill_ppn'] = $value->wi_bill_ppn ?? '';
            $postCupkg['cupkg_bill_debt_accumulation'] = 1 ?? '';
            $postCupkg['cupkg_bill_installinv'] = $value->wi_bill_instalinv ?? '';
            $postCupkg['cupkg_cont_begin'] = $value->wi_cont_begin ?? '';
            $postCupkg['cupkg_cont_end'] = $value->wi_cont_end ?? '';
            $postCupkg['cupkg_acct_manager'] = $value->wi_acct_manager ?? '';
            $postCupkg['cupkg_tech_coord'] = $value->wi_tech_coord ?? '';
            $postCupkg['cupkg_bill_ppn'] = $value->wi_bill_ppn ?? '';
            $postCupkg['cupkg_recycle'] = 2 ?? '';

            $postSpk[0]['ft_number'] = 'SP' . $newSpkNumber1 . "/IKR/" . date('m') . '/' . date('y');
            $postSpk[0]['ft_received'] = date('Y-m-d H:m:i');
            $postSpk[0]['ft_type'] = 1;
            $postSpk[0]['ft_svc_type'] = 2;
            $postSpk[0]['cust_number'] = $newCustNumber;
            $postSpk[0]['sp_code'] = $value->sp_code;

            $postSpk[1]['ft_number'] = 'SP' . $newSpkNumber2 . "/NOC/" . date('m') . '/' . date('y');
            $postSpk[1]['ft_received'] = date('Y-m-d H:m:i');
            $postSpk[1]['ft_type'] = 2;
            $postSpk[1]['ft_svc_type'] = 2;
            $postSpk[1]['cust_number'] = $newCustNumber;
            $postSpk[1]['sp_code'] = $value->sp_code;
        }
        //dd($postCupkg);
        DB::table('t_customer')->insert($postCust);

        $spNom = DB::table('trel_cust_pkg')->insertGetId($postCupkg);
        $postSpk[0]['sp_nom'] = $spNom;
        $postSpk[1]['sp_nom'] = $spNom;
        DB::table('t_invoice_porfoma')->where('inv_number', $value->inv_number)->update(['sp_nom' => $spNom]);

        DB::table('t_field_task')->insert($postSpk);
        //dd($postCust, $postCupkg, $postPi, $postValItem, $postSpk);
        $wiData = WaitingList::where('wi_number', $wiNumber)
            ->update(['wi_status' => 1, 'new_cust_number' => $newCustNumber]);


        //$request->session()->flash('success', 'Add Customer Success!');

        return redirect(route('customer-detail', $newCustNumber));
    }
    public function import(Request $request)
    {
        request()->validate([
            'file' => 'required',
        ]);

        $file = $request->file('file');
        $fileName = rand() . $file->getClientOriginalName();
        $file->move('files/waiting_list', $fileName);

        $fullPath = 'files/waiting_list/' . $fileName;
        $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet     = $reader->load($fullPath);
        $sheet_data     = $spreadsheet->getActiveSheet()->toArray();

        $arrField = $this->arrField();
        $postVal = [];
        foreach ($sheet_data as $key => $value) {
            if ($key > 0) {

                $postVal[$key]['wi_am'] = $value[1];
                $postVal[$key]['sp_code'] = $value[2];
                $postVal[$key]['wi_name'] = $value[3];
                $postVal[$key]['wi_phone'] = $value[4];
                $postVal[$key]['wi_email'] = $value[5];
                $postVal[$key]['wi_address'] = $value[6];
                $postVal[$key]['wi_note'] = $value[7];
                $postVal[$key]['wi_file_identity'] = $value[8];
                $postVal[$key]['wi_file_lokasi'] = $value[9];
                $postVal[$key]['wi_file_survei'] = $value[10];
                $latLong = explode(',', $value[11]);
                $postVal[$key]['wi_lat'] = $latLong[0];
                $postVal[$key]['wi_long'] = $latLong[1];
            }
        }
        //dd($postVal);
        $insert = WaitingList::insert($postVal);
        $request->session()->flash('success', 'Import Data berhasil!');

        return redirect()->back();
    }
    */



    public function list()
    {
        $arrfield = $this->arrField();

        $data = WaitingList::orderByDesc('wi_number')->get();
        $datatables = Datatables::of($data)
            ->addIndexColumn();

        /*foreach ($arrfield as $key => $value) {
            if ($value['form_type'] == 'date') {
          
            }
        }*/

        //dd($data->toArray());

        return $datatables->addColumn('status', function ($row) {
            $status = '';
            if ($row->wi_status == 0) {
                $status = 'Menuggu';
                $warna = 'yellow';
            } elseif ($row->wi_status == 1) {
                $status = 'Selesai';
                $warna = 'blue';
            }

            $actionBtn = '<a href="#" class="btn btn-' . $warna . '  ">' . $status  . '</a>';
            return  $actionBtn;
        })->editColumn('wi_type', function ($row) {
            $type = [1 => 'Personal', 'Perusahaan', 'Pemkot'];
            return $type[$row->wi_type];
        })->editColumn('wi_pop', function ($row) {
            return $this->arrPop[$row->wi_pop];
        })->addColumn('detail', function ($row) {
            if ($row->wi_status == 1) {
                $route =   route('customer-detail', $row->new_cust_number);
            } else {
                $route = route('waitinglist-detail', $row->wi_number);
            }
            $actionBtn = '<a href="' . $route  . '" class="btn btn-green btn-icon btn-circle btn_konfirmasi" ><i class="fa fa-search-plus"></i></a>';
            return $actionBtn;
        })

            ->rawColumns(['detail', 'status'])
            ->make(true);
    }

    private function arrField()
    {

        $bussinesType = DB::table('tlkp_business')->where('bsn_recycle', 2)->get();
        $susunBusines = [];
        $susunBusines2 = [];
        foreach ($bussinesType as $key => $value) {
            $susunBusines2[$value->bsn_id] = $value->bsn_name;
            if ($value->bsn_level == 1) {
                $susunBusines[$value->bsn_id]['parent'] = $value->bsn_name;
            } elseif ($value->bsn_level == 2) {
                $susunBusines[$value->bsn_parent]['child'][$value->bsn_id] = $value->bsn_name;
            }
        }
        //dd($susunBusines);

        $prov = DB::table('tlkp_prov')->where('prov_recycle', 2)->get();
        $susunProv  = [];
        foreach ($prov as $key => $value) {
            $susunProv[$value->prov_name] = $value->prov_name;
        }

        $city = DB::table('tlkp_city')->where('cty_recycle', 2)->get();
        $susunCity  = [];
        foreach ($city as $key => $value) {
            $susunCity[$value->cty_name] = $value->cty_name;
        }

        $kecamatan = DB::table('tlkp_kecamatan')->where('area_recycle', 0)->get();
        $susunKecamatan  = [];
        foreach ($kecamatan as $key => $value) {
            $susunKecamatan[$value->area_name] = $value->area_name;
        }

        $kelurahan = DB::table('tlkp_kelurahan')->where('area_recycle', 0)->get();
        $susunKelurahan  = [];
        foreach ($kelurahan as $key => $value) {
            $susunKelurahan[$value->area_name] = $value->area_name;
        }

        $job = DB::table('tlkp_job')->where('job_recycle', 2)->get();
        $susunJob  = [];
        foreach ($job as $key => $value) {
            $susunJob[$value->job_name] = $value->job_name;
        }

        $pkg = DB::table('t_service_pkg')->where('sp_recycle', 2)->get();
        $susunPkg  = [];
        foreach ($pkg as $key => $value) {
            $susunPkg[$value->sp_code] = $value->sp_name;
        }

        $infoPelanggan = [
            'wi_member_card' => [
                'label' => 'Member Card',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,

            ],
            'wi_svc_begin' => [
                'label' => 'Mulai Layanan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'date',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
            ],
            'wi_type' => [
                'label' => 'Jenis Account',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => [1 => 'Personal', 'Perusahaan', 'Pemkot'],
                'visible' => true,
                'required' => true,
            ],
            'wi_business' => [
                'label' => 'Nama Perusahaan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'wi_business_type' => [
                'label' => 'Jenis Usaha',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select_bsn',
                'keyvaldata' => $susunBusines,
                'keyvaldata2' => $susunBusines2,
                'visible' => true,
                'required' => false,
            ],
            'wi_name' => [
                'label' => 'Nama Pelanggan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
            ],
            'wi_pop' => [
                'label' => 'POP',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select2',
                'keyvaldata' => $this->arrPop,
                'visible' => true,
                'required' => true,
            ],
            'wi_acct_manager' => [
                'label' => 'AM',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
            ],
            'wi_address' => [
                'label' => 'Alamat',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'area',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
            ],
            'wi_zip_code' => [
                'label' => 'Kode POS',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'wi_prov' => [
                'label' => 'Kabupaten',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select2',
                'keyvaldata' => $susunCity,
                'visible' => true,
                'required' => true,
            ],
            'wi_city' => [
                'label' => 'Kota',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select2',
                'keyvaldata' => $susunProv,
                'visible' => true,
                'required' => true,
            ],
            'wi_kecamatan' => [
                'label' => 'Kecamatan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select2',
                'keyvaldata' => $susunKecamatan,
                'visible' => true,
                'required' => true,
            ],
            'wi_kelurahan' => [
                'label' => 'Kelurahan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select2',
                'keyvaldata' => $susunKelurahan,
                'visible' => true,
                'required' => true,
            ],
            'wi_rw' => [
                'label' => 'RW',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
                'small'=>'Contoh : 04'
            ],
            'wi_rt' => [
                'label' => 'RT',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
                'small'=>'Contoh : 04'
            ],
            'wi_phone' => [
                'label' => 'Nomor HP',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
            ],
            'wi_phone_name' => [
                'label' => 'Nama Contact',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'wi_telp' => [
                'label' => 'Nomor Telpon',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'wi_email' => [
                'label' => 'Email',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'wi_birth_date' => [
                'label' => 'Tanggal Lahir',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
                'small'=>'Format Tahun-Bulan-Tanggal contoh : 1995-12-01'
            ],
            'wi_sex' => [
                'label' => 'Jenis Kelamin',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => [1 => "Laki-Laki", 'Perempuan'],
                'visible' => true,
                'required' => true,
            ],
            'wi_job' => [
                'label' => 'Pekerjaan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $susunJob,
                'visible' => true,
                'required' => false,
            ],
            'wi_ident_type' => [
                'label' => 'Jenis Identitas',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => [1 => 'KTP', 'KITAS', 'NPWP', 'SIM', 'Paspor'],
                'visible' => true,
                'required' => true,
            ],
            'wi_ident_number' => [
                'label' => 'Nomor Identitas',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
            ],
            'wi_npwp' => [
                'label' => 'NPWP',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
        ];
        $infoLayanan = [
            'wi_home_pass' => [
                'label' => 'Homepass',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'sp_code' => [
                'label' => 'Layanan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select2',
                'keyvaldata' => $susunPkg,
                'visible' => true,
                'required' => true,
            ],
            'wi_cont_begin' => [
                'label' => 'Kontrak Mulai',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'date',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'wi_cont_end' => [
                'label' => 'Kontrak Selesai',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'date',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'wi_trial' => [
                'label' => 'Perjanjian Trial',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'date',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'wi_tech_coord' => [
                'label' => 'Koordinat',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
            ],
        ];
        $berkas = [
            'file_identitas' => [
                'label' => 'File Identitas',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'file',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
            ],
            'form_berlanggan' => [
                'label' => 'Form Berlangganan',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'file',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
            ],
            'lembar_survei' => [
                'label' => 'Lembar Survei',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'file',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
            ],
            'foto_rumah' => [
                'label' => 'Foto Rumah',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'file',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
            ],
            'file_1' => [
                'label' => 'Tambahan 1',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'file',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'file_2' => [
                'label' => 'Tambahan 2',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'file',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'file_3' => [
                'label' => 'Tambahan 3',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'file',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
        ];

        $infoPenagihan = [
            'wi_bill_contact' => [
                'label' => 'Kontak Pembayaran',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'wi_bill_address' => [
                'label' => 'Alamat',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'area',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
            ],
            'wi_bill_zip_code' => [
                'label' => 'Kode POS',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'wil_bill_prov' => [
                'label' => 'Kabupaten',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $susunCity,
                'visible' => true,
                'required' => true,
            ],
            'wi_bill_city' => [
                'label' => 'Kota',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $susunProv,
                'visible' => true,
                'required' => true,
            ],
            'wi_bill_phone' => [
                'label' => 'Nomor HP',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
            ],
            'wi_bill_telp' => [
                'label' => 'Nomor Telpon',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'wi_bill_email' => [
                'label' => 'Email',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'wi_bill_type' => [
                'label' => 'Cara Pembayaran',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => [1 => 'Cash', 'Giro/Check', 'Transfer Bank', 'Kartu Debit/Kredit'],
                'visible' => true,
                'required' => true,
            ],
            'wi_bill_period' => [
                'label' => 'Periode Pembayaran',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => [1 => 'Bulanan',  3 => '3 Bulan', 6 => '6 Bulan', 12 => '12 Bulan'],
                'visible' => true,
                'required' => true,
            ],
            'wi_bill_instalinv' => [
                'label' => 'Invoice Instalasi',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => [1 => 'Terbit', 'Tidak Terbit'],
                'value' => '2',
                'visible' => true,
                'required' => true,
            ],
            'wi_bill_autogen' => [
                'label' => 'Invoice Reguler',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => ['Tidak Auto Generate Invoice', 'Auto Generate Invoice'],
                'visible' => true,
                'required' => true,
            ],
            'wi_inv_info' => [
                'label' => 'Kirim Invoice',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => ['Tidak', 'Ya'],
                'visible' => true,
                'required' => true,
            ],

            'wi_bill_curency' => [
                'label' => 'Mata Uang',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => ['IDR' => 'IDR', 'USD' => 'USD'],
                'value' => 'IDR',
                'visible' => true,
                'required' => true,
            ],
            'wi_bill_regular' => [
                'label' => 'Biaya Reguler',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'int',
                'keyvaldata' => '',
                'visible' => true,
                'required' => false,
            ],
            'wi_bill_ppn' => [
                'label' => 'PPN',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => ['Tidak', 'Ya'],
                'value' => '1',
                'visible' => true,
                'required' => true,
            ],
            'wi_bill_desc' => [
                'label' => 'Keterangan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'area',
                'keyvaldata' => '',
                'visible' => true,
                'required' => true,
            ],
        ];
        return [
            ['Informasi Pelanggan', $infoPelanggan],
            ['Informasi Layanan', $infoLayanan],
            ['Informasi Penagihan', $infoPenagihan],
            ['Berkas', $berkas],
        ];
    }
    private function arrFieldIndex()
    {

        $prov = DB::table('tlkp_prov')->where('prov_recycle', 2)->get();
        $susunProv  = [];
        foreach ($prov as $key => $value) {
            $susunProv[$value->prov_name] = $value->prov_name;
        }

        $city = DB::table('tlkp_city')->where('cty_recycle', 2)->get();
        $susunCity  = [];
        foreach ($city as $key => $value) {
            $susunCity[$value->cty_name] = $value->cty_name;
        }

        $kecamatan = DB::table('tlkp_kecamatan')->where('area_recycle', 0)->get();
        $susunKecamatan  = [];
        foreach ($kecamatan as $key => $value) {
            $susunKecamatan[$value->area_name] = $value->area_name;
        }

        $kelurahan = DB::table('tlkp_kelurahan')->where('area_recycle', 0)->get();
        $susunKelurahan  = [];
        foreach ($kelurahan as $key => $value) {
            $susunKelurahan[$value->area_name] = $value->area_name;
        }



        $pkg = DB::table('t_service_pkg')->where('sp_recycle', 2)->get();
        $susunPkg  = [];
        foreach ($pkg as $key => $value) {
            $susunPkg[$value->sp_code] = $value->sp_name;
        }

        $infoPelanggan = [
            'wi_name' => [
                'label' => 'Nama Pelanggan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_phone' => [
                'label' => 'No Telpon',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'area',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_address' => [
                'label' => 'Alamat',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'area',
                'keyvaldata' => '',
                'visible' => true
            ],
            'sp_code' => [
                'label' => 'Layanan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $this->arrPop,
                'visible' => true
            ],
            'wi_pop' => [
                'label' => 'POP',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $this->arrPop,
                'visible' => true
            ],
            /*'wi_member_card' => [
                'label' => 'Member Card',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_svc_begin' => [
                'label' => 'Mulai Layanan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'date',
                'keyvaldata' => '',
                'visible' => true
            ],*/
            'wi_type' => [
                'label' => 'Jenis Account',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => [1 => 'Personal', 'Perusahaan', 'Pemkot'],
                'visible' => true
            ],

            'wi_acct_manager' => [
                'label' => 'AM',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],

            /*  'wi_city' => [
                'label' => 'Kota',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $susunProv,
                'visible' => true
            ],
            'wi_kecamatan' => [
                'label' => 'Kecamatan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $susunKecamatan,
                'visible' => true
            ],
            'wi_kelurahan' => [
                'label' => 'Kelurahan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $susunKelurahan,
                'visible' => true
            ],
            'wi_phone' => [
                'label' => 'Nomor HP',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],*/
            'status' => [
                'label' => 'Status',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'new_cust_number' => [
                'label' => 'Nomor pelanggan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],

        ];

        return $infoPelanggan;
    }
}
