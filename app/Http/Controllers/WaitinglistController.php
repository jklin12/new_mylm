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

    public function index()
    {

        $title = 'Waiting List';
        $subTitle = '';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $arrfield = $this->arrField();
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
        foreach ($arrfield as $kf => $vf) {
            if ($kf < 1) {
                foreach ($vf[1] as  $key => $value) {

                    $i++;
                    if ($value['visible']) {
                        $tableColumn[$i]['data'] = $key;
                        $tableColumn[$i]['name'] = $value['label'];
                        $tableColumn[$i]['orderable'] = $value['orderable'];
                        $tableColumn[$i]['searchable'] = $value['searchable'];
                        $tableColumn[$i]['visible'] = $value['visible'];
                    }
                }
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
            $insert = DB::table('t_waiting_list_new')->insertGetId($postVal);

            $postfile = [];
            $path = 'files/pelanggan_baru';
            foreach ($fileKeyy as $key => $value) {
                $file = $request->file($value);


                $fileName = $value . '_' . rand() . $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $check = in_array($extension, $allowedfileExtension);

                if ($check) {
                    $file->move($path, $fileName);
                    $fullPath = $path . '/' . $fileName;

                    $postfile[$value]['wi_number'] = $insert;
                    $postfile[$value]['wi_file_key'] = $value;
                    $postfile[$value]['wi_file_title'] = $arrfield[3][1][$value]['label'];
                    $postfile[$value]['wi_file_name'] = $fullPath;
                }
            }
            DB::table('t_waiting_list_fie')->insert($postfile);
            $this->createInv($insert);

            $request->session()->flash('success', 'Add Waitinglist Success!');

            return redirect(route('waitinglist-index'));
        }
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

    public function detail(Request $request, $wiId)
    {

        $title = 'Detail Waitinglist ';
        $subTitle = '';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $wiData = WaitingList::where('wi_number', $wiId)->first();


        $invoice = DB::table('t_invoice_wi')->where('cust_number', $wiId)
            ->leftJoin('t_inv_item_wi', function ($join) {
                $join->on('t_invoice_wi.inv_number', '=', 't_inv_item_wi.inv_number')->where('ii_recycle', '<>', 1);
            })->get();

        $file = DB::table('t_waiting_list_fie')->where('wi_number', $wiId)
            ->get();

        //dd($file);


        $susunData = [];
        $susunDataSummary = [];

        $totals = 0;
        foreach ($invoice->toArray() as $key => $value) {
            foreach ($value as $keys => $values) {
                $susunData[$keys] = $values;
            }
            $totals += $value->ii_amount;


            $susunDataSummary[$key]['ii_order'] = $value->ii_order;
            $susunDataSummary[$key]['ii_type'] = $value->ii_type;
            $susunDataSummary[$key]['ii_amount'] = 'Rp. ' . SchRp($value->ii_amount);
            $susunDataSummary[$key]['ii_info'] = $value->ii_info;
        }

        $originalCode = $susunData['inv_number'] . ';' . $susunData['inv_number'];
        $encryptionCode = urlencode(base64_encode($originalCode));

        $susunData['totals'] = 'Rp. ' . SchRp($totals);
        //dd($susunData2); 
        $load['inv_status'] = $susunData['inv_status'];
        $load['cust_number'] = $susunData['cust_number'];
        $load['wiData'] = $wiData->toArray();
        $load['datas'] = $susunData;
        $load['data_summary'] = $susunDataSummary;
        $load['data_file'] = $file;
        $load['url'] = 'http://localhost/paylifemedia/registrationPay?code=' . $encryptionCode;
        //dd($load);
        $load['arr_field'] = $this->arrField();

        return view('pages/waitinglist/detail', $load);
    }

    public function konfirmasi(Request $request)
    {

        $wiNumber = $request->input('wi_number');
        $wiData = WaitingList::where('wi_number', $wiNumber)->first();


        $pop = $this->arrPopCode[$wiData->wi_pop];

        $lastCust = Customer::where('cust_pop', $wiData->wi_pop)->orderByDesc('cust_number')->first();
        if (isset($lastCust->cust_number)) {
            $lastnum = $lastCust->cust_number;
        } else {
            $lastnum = 0;
        }
        $numCustNumber =  sprintf('%06d', substr($lastnum, -6) + 1);
        $newCustNumber = $pop . $numCustNumber;

        $postCust['cust_number'] = $newCustNumber;
        $postCust['hp_number'] = $wiData->wi_home_pass;
        $postCust['cust_member_card'] = $wiData->wi_member_card;
        $postCust['cust_pop'] = $wiData->wi_pop;
        $postCust['cust_job'] = $wiData->wi_job;
        $postCust['cust_city'] = $wiData->wi_city;
        $postCust['cust_prov'] = $wiData->wi_prov;
        $postCust['cust_name'] = $wiData->wi_name;
        $postCust['cust_company'] = $wiData->wi_company;
        $postCust['cust_business'] = $wiData->wi_business;
        $postCust['cust_npwp'] = $wiData->wi_npwp;
        $postCust['cust_sex'] = $wiData->wi_sex;
        $postCust['cust_birth_date'] = $wiData->wi_birth_date;
        $postCust['cust_ident_type'] = $wiData->wi_ident_type;
        $postCust['cust_ident_number'] = $wiData->wi_ident_number;
        $postCust['cust_address'] = $wiData->wi_address;
        $postCust['cust_zip'] = $wiData->wi_zip_code;
        $postCust['cust_phone'] = $wiData->wi_phone;
        $postCust['cust_hp'] = $wiData->wi_telp;
        $postCust['cust_hp_contact'] = $wiData->wi_phone_name;
        $postCust['cust_email'] = $wiData->wi_email;
        $postCust['created'] = date('Y-m-d');
        $postCust['cust_kecamatan'] = $wiData->wi_kecamatan;
        $postCust['cust_kelurahan'] = $wiData->wi_kelurahan;
        $postCust['cust_bill_address'] = $wiData->wi_bill_address;
        $postCust['cust_bill_zip'] = $wiData->wi_bill_zip_code;
        $postCust['cust_bill_email'] = $wiData->wi_bill_email;
        $postCust['cust_bill_phone'] = $wiData->wi_bill_phone;
        $postCust['cust_bill_contact'] = $wiData->wi_bill_contact;
        $postCust['cust_bill_info'] = $wiData->wi_bill_desc;

        DB::table('t_customer')->insert($postCust);

        $piNumber = 'INV' . $newCustNumber . date('ym') . '01';

        $postCupkg['cust_number'] = $newCustNumber;
        $postCupkg['sp_code'] = $wiData->sp_code;
        $postCupkg['cupkg_svc_begin'] = $wiData->wi_svc_begin;
        $postCupkg['cupkg_acc_type'] = $wiData->wi_type;
        $postCupkg['cupkg_status'] = 3;
        $postCupkg['cupkg_trial'] = $wiData->wi_trial;
        $postCupkg['cupkg_bill_period'] = $wiData->wi_bill_period;
        $postCupkg['cupkg_bill_type'] = $wiData->wi_bill_type;
        $postCupkg['cupkg_bill_lastpaid'] = $piNumber;
        $postCupkg['cupkg_bill_autogen'] = $wiData->wi_bill_autogen;
        $postCupkg['cupkg_bill_ppn'] = $wiData->wi_bill_ppn;
        $postCupkg['cupkg_bill_debt_accumulation'] = 1;
        $postCupkg['cupkg_bill_installinv'] = $wiData->wi_bill_instalinv;
        $postCupkg['cupkg_cont_begin'] = $wiData->wi_cont_begin;
        $postCupkg['cupkg_cont_end'] = $wiData->wi_cont_end;
        $postCupkg['cupkg_acct_manager'] = $wiData->wi_acct_manager;
        $postCupkg['cupkg_tech_coord'] = $wiData->wi_tech_coord;
        $postCupkg['cupkg_bill_ppn'] = $wiData->wi_bill_ppn;
        $postCupkg['cupkg_recycle'] = 2;


        $spNom = DB::table('trel_cust_pkg')->insertGetId($postCupkg);

        $invStart = $wiData->wi_svc_begin ?? date('Y-m-d');
        $invEnd = date('Y-m-d', strtotime($invStart . ' +29 days'));

        $postPi['inv_number'] = $piNumber;
        $postPi['cust_number'] = $newCustNumber;
        $postPi['sp_code'] =  $wiData->sp_code;
        $postPi['inv_type'] = '2';
        $postPi['inv_due'] = $invStart;
        $postPi['inv_post'] = date('Y-m-d H:m:s');
        $postPi['inv_status'] = '1';
        $postPi['inv_start'] = $invStart;
        $postPi['inv_end'] = $invEnd;
        $postPi['inv_currency'] = "IDR";
        $postPi['sp_nom'] = $spNom;
        $postPi['reaktivasi_pi'] = 0;

        DB::table('t_invoice_porfoma')->insert($postPi);

        $pkg = DB::table('t_service_pkg')->where('sp_name', $wiData->sp_code)->first();

        $postValItem[0]['ii_type'] = '2';
        $postValItem[0]['inv_number'] = $piNumber;
        $postValItem[0]['ii_info'] = 'Biaya Layanan ' . $wiData->sp_code . '  Periode Pertama';
        $postValItem[0]['ii_amount'] = $pkg->sp_reguler;
        $postValItem[0]['ii_order'] = '1';
        $postValItem[0]['ii_recycle'] = '2';

        $postValItem[1]['ii_type'] = '7';
        $postValItem[1]['inv_number'] = $piNumber;
        $postValItem[1]['ii_info'] = 'PPN 11 %';
        $postValItem[1]['ii_amount'] = ($pkg->sp_reguler * 11) / 100;
        $postValItem[1]['ii_order'] = '2';
        $postValItem[1]['ii_recycle'] = '2';

        DB::table('t_inv_item_porfoma')->insert($postValItem);

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
        $postSpk[0]['ft_number'] = 'SP' . $newSpkNumber1 . "/IKR/" . date('m') . '/' . date('y');
        $postSpk[0]['ft_received'] = date('Y-m-d H:m:i');
        $postSpk[0]['ft_type'] = 1;
        $postSpk[0]['ft_svc_type'] = 2;
        $postSpk[0]['cust_number'] = $newCustNumber;
        $postSpk[0]['sp_code'] = $wiData->sp_code;
        $postSpk[0]['sp_nom'] = $spNom;

        $postSpk[1]['ft_number'] = 'SP' . $newSpkNumber2 . "/NOC/" . date('m') . '/' . date('y');
        $postSpk[1]['ft_received'] = date('Y-m-d H:m:i');
        $postSpk[1]['ft_type'] = 2;
        $postSpk[1]['ft_svc_type'] = 2;
        $postSpk[1]['cust_number'] = $newCustNumber;
        $postSpk[1]['sp_code'] = $wiData->sp_code;
        $postSpk[1]['sp_nom'] = $spNom;

        DB::table('t_field_task')->insert($postSpk);
        //dd($postCust, $postCupkg, $postPi, $postValItem, $postSpk);


        $request->session()->flash('success', 'Add Customer Success!');

        //return redirect(route('customer-detail', $newCustNumber));
    }

    public function createInv($wiNumber)
    {
        //dd($request->all());

        $wiData = WaitingList::where('wi_number', $wiNumber)->first();

        $lastPi = DB::table('t_invoice_wi')
            ->whereRaw("YEAR(inv_post) = '" . date('Y') . "'")
            ->whereRaw("MONTH(inv_post) = '" . date('m') . "'")
            ->orderByDesc('inv_post')
            ->first();

        if (isset($lastPi->inv_number)) {
            $lastPiNum = substr($lastPi->inv_number, -3);
            $lastPiNum += 1;
            $inv_number = 'PIWI' . sprintf('%06d', $wiData->wi_number) . date('ym') . sprintf('%06d', $lastPiNum);
        } else {
            $inv_number = 'PIWI' . sprintf('%06d', $wiData->wi_number) . date('ym') . '001';
        }

        $invStart = $wiData->wi_svc_begin ?? date('Y-m-d');
        $invEnd = date('Y-m-d', strtotime($invStart . ' +29 days'));

        $postVal['inv_number'] = $inv_number;
        $postVal['cust_number'] = $wiData->wi_number;
        $postVal['sp_code'] = $wiData->sp_code;
        $postVal['inv_type'] = '2';
        $postVal['inv_due'] = $invStart;
        $postVal['inv_post'] = date('Y-m-d H:m:s');
        $postVal['inv_status'] = '0';
        $postVal['inv_start'] = $invStart;
        $postVal['inv_end'] = $invEnd;
        $postVal['inv_currency'] = "IDR";

        $pkg = DB::table('t_service_pkg')->where('sp_code', $wiData->sp_code)->first();

        //dd($postVal,$pkg);

        $postValItem[0]['ii_type'] = '2';
        $postValItem[0]['inv_number'] = $inv_number;
        $postValItem[0]['ii_info'] = 'Biaya Layanan ' . $wiData->sp_code . '  ' . Carbon::parse($postVal['inv_start'])->isoFormat('D MMMM Y') . '-' . Carbon::parse($postVal['inv_end'])->isoFormat('D MMMM Y');
        $postValItem[0]['ii_amount'] = $pkg->sp_reguler;
        $postValItem[0]['ii_order'] = '1';
        $postValItem[0]['ii_recycle'] = '2';

        $postValItem[1]['ii_type'] = '7';
        $postValItem[1]['inv_number'] = $inv_number;
        $postValItem[1]['ii_info'] = 'PPN 11 %';
        $postValItem[1]['ii_amount'] = ($pkg->sp_reguler * 11) / 100;
        $postValItem[1]['ii_order'] = '2';
        $postValItem[1]['ii_recycle'] = '2';


        WaitingList::where('wi_number', $wiData->wi_number)->update(['wi_status' => 1]);
        $insertPi = DB::table('t_invoice_wi')->insert($postVal);
        $insertItemPi = DB::table('t_inv_item_wi')->insert($postValItem);
    }

    public function list()
    {
        $arrfield = $this->arrField();

        $data = WaitingList::get();
        $datatables = Datatables::of($data)
            ->addIndexColumn();

        /*foreach ($arrfield as $key => $value) {
            if ($value['form_type'] == 'date') {
          
            }
        }*/

        $datatables->editColumn('wi_number', function ($row) {

            return 'WI' . sprintf('%06d', $row->wi_number);
        });

        return $datatables->addColumn('status', function ($row) {
            $status = '';
            if ($row->wi_status == 0) {
                $status = 'Baru';
                $warna = 'yellow';
            } elseif ($row->wi_status == 1) {
                $status = 'Menunggu';
                $warna = 'blue';
            }

            $actionBtn = '<a href="#" class="btn btn-' . $warna . '  ">' . $status  . '</a>';
            return  $actionBtn;
        })->addColumn('detail', function ($row) {
            $actionBtn = '<a href="' . route('waitinglist-detail', $row->wi_number) . '" class="btn btn-green btn-icon btn-circle btn_konfirmasi" ><i class="fa fa-search-plus"></i></a>';
            return $actionBtn;
        })

            ->rawColumns(['detail', 'lokasi', 'foto_ktp', 'foto_lokasi', 'form_survei', 'status'])
            ->make(true);
    }

    private function arrField()
    {

        $bussinesType = DB::table('tlkp_business')->where('bsn_recycle', 2)->get();
        $susunBusines = [];
        foreach ($bussinesType as $key => $value) {
            if ($value->bsn_level == 1) {
                $susunBusines[$value->bsn_id]['parent'] = $value->bsn_name;
            } elseif ($value->bsn_level == 2) {
                $susunBusines[$value->bsn_parent]['child'][$value->bsn_id] = $value->bsn_name;
            }
        }

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
                'visible' => true
            ],
            'wi_svc_begin' => [
                'label' => 'Mulai Layanan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'date',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_type' => [
                'label' => 'Jenis Account',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => [1 => 'Personal', 'Perusahaan', 'Pemkot'],
                'visible' => true
            ],
            'wi_business' => [
                'label' => 'Nama Perusahaan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_business_type' => [
                'label' => 'Jenis Usaha',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select_bsn',
                'keyvaldata' => $susunBusines,
                'visible' => true
            ],
            'wi_name' => [
                'label' => 'Nama Pelanggan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
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
            'wi_acct_manager' => [
                'label' => 'AM',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
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
            'wi_zip_code' => [
                'label' => 'Kode POS',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_prov' => [
                'label' => 'Kabupaten',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $susunProv,
                'visible' => true
            ],
            'wi_city' => [
                'label' => 'Kota',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $susunCity,
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
            'wi_rw' => [
                'label' => 'RW',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_rt' => [
                'label' => 'RT',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_phone' => [
                'label' => 'Nomor HP',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_phone_name' => [
                'label' => 'Nama Contact',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_telp' => [
                'label' => 'Nomor Telpon',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_email' => [
                'label' => 'Email',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_birth_date' => [
                'label' => 'Tanggal Lahir',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'date',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_sex' => [
                'label' => 'Jenis Kelamin',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => [1 => "Laki-Laki", 'Perempuan'],
                'visible' => true
            ],
            'wi_job' => [
                'label' => 'Pekerjaan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => $susunJob,
                'visible' => true
            ],
            'wi_ident_type' => [
                'label' => 'Jenis Identitas',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => [1 => 'KTP', 'KITAS', 'NPWP', 'SIM', 'Paspor'],
                'visible' => true
            ],
            'wi_ident_number' => [
                'label' => 'Nomor Identitas',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_npwp' => [
                'label' => 'NPWP',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
        ];
        $infoLayanan = [
            'wi_home_pass' => [
                'label' => 'Homepass',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'sp_code' => [
                'label' => 'Layanan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $susunPkg,
                'visible' => true
            ],
            'wi_cont_begin' => [
                'label' => 'Kontrak Mulai',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'date',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_cont_end' => [
                'label' => 'Kontrak Selesai',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'date',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_trial' => [
                'label' => 'Perjanjian Trial',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'date',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_tech_coord' => [
                'label' => 'Koordinat',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
        ];
        $berkas = [
            'file_identitas' => [
                'label' => 'File Identitas',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'file',
                'keyvaldata' => '',
                'visible' => true
            ],
            'form_berlanggan' => [
                'label' => 'Form Berlangganan',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'file',
                'keyvaldata' => '',
                'visible' => true
            ],
            'lembar_survei' => [
                'label' => 'Lembar Survei',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'file',
                'keyvaldata' => '',
                'visible' => true
            ],
            'foto_rumah' => [
                'label' => 'Foto Rumah',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'file',
                'keyvaldata' => '',
                'visible' => true
            ],
        ];

        $infoPenagihan = [
            'wi_bill_contact' => [
                'label' => 'Kontak Pembayaran',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_bill_address' => [
                'label' => 'Alamat',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'area',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_bill_zip_code' => [
                'label' => 'Kode POS',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wil_bill_prov' => [
                'label' => 'Kabupaten',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $susunProv,
                'visible' => true
            ],
            'wi_bill_city' => [
                'label' => 'Kota',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $susunCity,
                'visible' => true
            ],
            'wi_bill_phone' => [
                'label' => 'Nomor HP',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_bill_telp' => [
                'label' => 'Nomor Telpon',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_bill_email' => [
                'label' => 'Email',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_bill_type' => [
                'label' => 'Cara Pembayaran',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => [1 => 'Cash', 'Giro/Check', 'Transfer Bank', 'Kartu Debit/Kredit'],
                'visible' => true
            ],
            'wi_bill_period' => [
                'label' => 'Periode Pembayaran',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => [1 => 'Bulanan', 2 => '2 Bulan', 3 => '3 Bulan', 6 => '6 Bulan', 12 => '12 Bulan'],
                'visible' => true
            ],
            'wi_bill_instalinv' => [
                'label' => 'Invoice Instalasi',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => [1 => 'Terbit', 'Tidak Terbit'],
                'visible' => true
            ],
            'wi_bill_autogen' => [
                'label' => 'Invoice Reguler',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => ['Tidak Auto Generate Invoice', 'Auto Generate Invoice'],
                'visible' => true
            ],
            'wi_inv_info' => [
                'label' => 'Kirim Invoice',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => ['Tidak', 'Ya'],
                'visible' => true
            ],

            'wi_bill_curency' => [
                'label' => 'Mata Uang',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => ['IDR' => 'IDR', 'USD' => 'USD'],
                'visible' => true
            ],
            'wi_bill_regular' => [
                'label' => 'Biaya Reguler',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'keyvaldata' => '',
                'visible' => true
            ],
            'wi_bill_ppn' => [
                'label' => 'PPN',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => ['Tidak', 'Ya'],
                'visible' => true
            ],
            'wi_bill_desc' => [
                'label' => 'Keterangan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'area',
                'keyvaldata' => '',
                'visible' => true
            ],
        ];
        return [
            ['Informasi Pelanggan', $infoPelanggan],
            ['Informasi Layanan', $infoLayanan],
            ['Informasi Penagihan', $infoPenagihan],
            ['Berkas', $berkas],
        ];
    }
}
