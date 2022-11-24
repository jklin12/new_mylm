<?php

namespace App\Http\Controllers;

use App\Models\ImportInvoiceResult;
use App\Models\Invoice;
use App\Models\InvoicePorfoma;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use DataTables;

class FinanceController extends Controller
{
    var $arrStatus = [1 => 'Registrasi', 'Instalasi', 'Setup', 'Sistem Aktif', 'Tidak Aktif', 'Trial', 'Sewa Khusus', 'Blokir', 'Ekslusif', 'CSR'];
    var $arrPiStatus = ['Blum Bayar', 'Lunas', 'Expired'];

    public function index(Request $request)
    {
        //$user = auth()->user();
        //print_r($user);die;
        $title = 'Generate invoice';
        $subTitle = 'Generate Incoe dari statement doku';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;

        $datas = ImportInvoiceResult::latest()->paginate(10);
        $load['datas'] = $datas;
        return view('pages/generateInv', $load);
    }

    public function importStatement(Request $request)
    {

        request()->validate([
            'file' => 'required',
            'note' => 'required',
            'tanggal' => 'required',
        ]);


        // upload ke folder file_siswa di dalam folder public
        $file = $request->file('file');
        $fileName = rand() . $file->getClientOriginalName();
        $file->move('files/statement', $fileName);

        $fullPath = 'files/statement/' . $fileName;
        $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet     = $reader->load($fullPath);
        $sheet_data     = $spreadsheet->getActiveSheet()->toArray();
        $arrPiNumber  = [];
        $newArrayPi = [];
        foreach ($sheet_data as $key => $value) {
            if ($key > 9) {
                $arrPiNumber[] = $value[4];
                $newArrayPi[$value[4]] = $value;
            }
        }
        //print_r($arrPiNumber);die;
        $piData = InvoicePorfoma::selectRaw('t_invoice_porfoma.*,t_invoice.inv_number as nomor_invoice,cupkg_status')
            ->whereIn('t_invoice_porfoma.inv_number', $arrPiNumber)
            ->leftJoin('t_invoice', 't_invoice_porfoma.inv_number', '=', 't_invoice.pi_number')
            ->leftJoin('trel_cust_pkg', function ($join) {
                $join->on('t_invoice_porfoma.cust_number', '=', 'trel_cust_pkg.cust_number')->on('trel_cust_pkg._nomor', '=', 't_invoice_porfoma.sp_nom');
            })
            ->get();

        $susunReport  = [];
        foreach ($piData as $key => $value) {
            //echo $value->inv_number.'||'.$value->inv_status.'<br>';
            $susunReport[$value->inv_number]['cust_number'] = $value->cust_number;
            $susunReport[$value->inv_number]['cupkg_status'] = $value->cupkg_status ? $this->arrStatus[$value->cupkg_status] : '';
            $susunReport[$value->inv_number]['pi_number'] = $value->inv_number;
            $susunReport[$value->inv_number]['pi_status'] = $value->inv_status == 1 ? 'lunas' : 'selain lunas';
            $status = 'Gagal';
            $message = 'Invoice Sudah ada ' . $value->nomor_invoice;
            $nomorInv = $value->nomor_invoice;

            if ($value->inv_status == 0) {
                $updateDataPi['inv_status'] = 1;
                $updateDataPi['inv_pay_method'] = $newArrayPi[$value->inv_number][2] == 'Alfa-VA' ? '13' : '12';
                $updateDataPi['inv_paid'] = date('Y-m-d H:m:i');
                $updateDataPi['inv_info'] = 'Di bayar dengan ' . $newArrayPi[$value->inv_number][2] . '  pada ' . Carbon::parse($newArrayPi[$value->inv_number][3])->isoFormat('dddd, D MMMM Y');
                //print_r($updateDataPi);
                InvoicePorfoma::where('inv_number', $value->inv_number)->update($updateDataPi);
            }

            if (!$value->nomor_invoice) {
                //echo $value->cust_number;
                $lastInvoice = Invoice::where('cust_number', $value->cust_number)->whereRaw('MONTH(inv_post) = ' . date('m'))->whereRaw('YEAR(inv_post) =' . date('Y'))->orderBy('inv_post', 'desc')->first();
                //print_r($lastInvoice);die;
                $newNum = 1;
                if ($lastInvoice) {
                    $lastNum = substr($lastInvoice->inv_number, -2);
                    $newNum =   sprintf('%02d', $lastNum + 1);
                }
                $newInvNumber = 'INV' . $value->cust_number . date('my') . sprintf('%02d', $newNum);
                $nomorInv = $newInvNumber;

                $reCheckInv = Invoice::find($newInvNumber);

                if (isset($reCheckInv->inv_number) && $reCheckInv->inv_number) {
                    $lastNum = substr($reCheckInv->inv_number, -2);
                    $newNum =   sprintf('%02d', $lastNum + 1);
                    $newInvNumber = 'INV' . $value->cust_number . date('my') . sprintf('%02d', $newNum);
                    $nomorInv = $newInvNumber;
                }

                $insertInv['inv_number'] = $newInvNumber;
                $insertInv['cust_number'] = $value->cust_number;
                $insertInv['sp_code'] = $value->sp_code;
                $insertInv['inv_type'] = 2;
                $insertInv['inv_due'] = $value->inv_end;
                $insertInv['inv_post'] = $request->post('tanggal');
                $insertInv['inv_paid'] = $request->post('tanggal').' 00:00:00';
                $insertInv['inv_status'] = 1;
                $insertInv['inv_start'] = $value->inv_start;
                $insertInv['inv_end'] = $value->inv_end;
                $insertInv['inv_info'] = 'Di bayar dengan ' . $newArrayPi[$value->inv_number][2] . '  pada ' . Carbon::parse($newArrayPi[$value->inv_number][3])->isoFormat('dddd, D MMMM Y') . '\n Digenerate otomatis pada ' . Carbon::parse(date('Y-m-d H:m:i'))->isoFormat('dddd, D MMMM Y H:mm');
                $insertInv['inv_pay_method'] = $newArrayPi[$value->inv_number][2] == 'Alfa-VA' ? '13' : '12';
                $insertInv['pi_number'] = $value->inv_number;
                $insertInv['sp_nom'] = $value->sp_nom;
                $insertInv['inv_receipt'] = 0;


                $qInsertInv = Invoice::create($insertInv);

                DB::table('t_pay_request')
                    ->where('inv_numb', $value->inv_number)
                    ->update(['check_inv' => 1]);

                $invItem = DB::table('t_inv_item_porfoma')
                    ->where('inv_number', $value->inv_number)
                    ->where('ii_recycle', '<>', '1')
                    ->get();
                $insertInvItem = [];
                foreach ($invItem as $keys => $values) {
                    $insertInvItem[$keys]['inv_number'] = $newInvNumber;
                    $insertInvItem[$keys]['ii_type'] = $values->ii_type;
                    $insertInvItem[$keys]['ii_info'] = $values->ii_info;
                    $insertInvItem[$keys]['ii_amount'] = $values->ii_amount;
                    $insertInvItem[$keys]['ii_order'] = $values->ii_order;
                }
                //print_r($insertInvItem);
                //print_r($insertInv);die;
                $qInsertInvItem = DB::table('t_inv_item')->insert($insertInvItem);

                $status = 'Sukses';
                $message = 'Generate Invoice sukses dengan nomor ' . $nomorInv;
            }
            $susunReport[$value->inv_number]['inv_status'] = $status;
            $susunReport[$value->inv_number]['inv_number'] = $nomorInv;
            $susunReport[$value->inv_number]['inv_message'] = $message;
        }
        //print_r($susunReport);die;
        $export = $this->downloadExcel(array_values($susunReport));

        if ($export['status']) {
            $request->session()->flash('success', 'Import Data berhasil!');
            $post = ImportInvoiceResult::create([
                'file_report' => $export['data'],
                'file_import' => $fullPath,
                'note' => $request->post('note'),
                'import_date' => date('Y-m-d'),

            ]);
            //$filepath = public_path($export);
            //return Response()->download($filepath);
        } else {
            $request->session()->flash('erorr', 'Import Data Gagal!');
        }

        return redirect(route('generateInv'));
    }

    public function cekRequest(Request $request)
    {
        $title = 'Data Request Pembayaran';
        $subTitle = 'Via Doku Olny';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $arrfield = $this->arrField();
        $i = 0;
        $tableColumn[$i]['data'] = 'DT_RowIndex';
        $tableColumn[$i]['name'] = 'DT_RowIndex';
        $tableColumn[$i]['orderable'] = 'false';
        $tableColumn[$i]['searchable'] = 'false';
        foreach ($arrfield as $key => $value) {
            $i++;
            $tableColumn[$i]['data'] = $key;
            $tableColumn[$i]['name'] = $value['label'];
            $tableColumn[$i]['orderable'] = $value['orderable'];
            $tableColumn[$i]['searchable'] = $value['searchable'];
        }
        $tableColumn[$i + 1]['data'] = 'detail';
        $tableColumn[$i + 1]['name'] = 'detail';

        $load['arr_field'] = $arrfield;
        $load['table_column'] = json_encode(array_values($tableColumn));

        return view('pages/finance/cek', $load);
    }

    public function cekRequestList(Request $request)
    {

        if ($request->ajax()) {
            $data =   DB::table('t_pay_request')->selectRaw('inv_numb, status_type, result_msg, t_pay_channel.description as channel_pembayaran, cupkg_status,amount,t_customer.cust_number,inv_status')
                ->leftJoin('t_invoice_porfoma', 't_pay_request.inv_numb', '=', 't_invoice_porfoma.inv_number')
                ->leftJoin('t_customer', 't_invoice_porfoma.cust_number', '=', 't_customer.cust_number')
                ->leftJoin('trel_cust_pkg', function ($join) {
                    $join->on('t_customer.cust_number', '=', 'trel_cust_pkg.cust_number')->on('trel_cust_pkg._nomor', '=', 't_invoice_porfoma.sp_nom');
                })
                ->leftJoin('t_pay_channel', 't_pay_request.payment_channel', '=', 't_pay_channel.code')
                ->groupBy('inv_numb', 't_customer.cust_number')
                ->whereRaw("insert_date > '2022-09-30 00:00:00'")
                ->where('check_inv', 0)
                ->orderByDesc('payment_time')
                //->limit(1000)
                ->get();
            return Datatables::of($data)
                ->addIndexColumn()
                /*->editColumn('payment_status', function ($row) {
                    $badges = '';
                    if ($row->result_msg = 'SUCCESS') {
                        $badges = '<span class="label label-green">' . $row->result_msg . '</span>';
                    } else {
                        $badges = '<span class="label label-warning">' . $row->result_msg . '</span>';
                    }
                    return $badges;
                })*/
                ->addColumn('rp_amount', function ($row) {
                    return 'Rp. ' . (intval($row->amount));
                })
                ->addColumn('detail', function ($row) {
                    $actionBtn = '<a href="' . route('pay-request-detail', 'inv=' . $row->inv_numb) . '" class="btn btn-pink btn-icon btn-circle"><i class="fa fa-search-plus"></i></a>';
                    return $actionBtn;
                })
                ->addColumn('status_cust', function ($row) {
                    return $row->cupkg_status ? $this->arrStatus[$row->cupkg_status] : '';
                })
                ->addColumn('pi_status', function ($row) {
                    return isset($this->arrPiStatus[$row->inv_status]) ? $this->arrPiStatus[$row->inv_status] : $row->inv_status;
                })
                ->rawColumns(['detail', 'status_cust', 'rp_amount', 'pi_status'])
                ->toJson();
        }
    }

    private function downloadExcel($data)
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $arrTitle = ['Nomor Pelanggan', 'Status Pelanggan', 'Nomor PI', 'Status PI', 'Status Generate', 'Nomor Invoice', 'Pesan'];
        $alphas = range('A', 'Z');
        $dateExport = Carbon::parse(date('Y-m-d H:m:i'))->isoFormat('dddd, D MMMM Y H:mm');

        $sheet->setCellValue('A1', 'Hasil Import Invoice'); // Set kolom A1 dengan tulisan "DATA SISWA"
        $sheet->setCellValue('A2', 'Pada ' . $dateExport); // Set kolom A1 dengan tulisan "DATA SISWA"
        $sheet->mergeCells('A1:' . $alphas[count($arrTitle) - 1] . '1'); // Set Merge Cell pada kolom A1 sampai E1
        $sheet->mergeCells('A2:' . $alphas[count($arrTitle) - 1] . '2'); // Set Merge Cell pada kolom A1 sampai E1
        $sheet->getStyle('A1')->getFont()->setBold(true); // Set bold kolom A1
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A4', 'No.');
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');

        foreach (array_values($arrTitle) as $key => $value) {

            $sheet->setCellValue($alphas[$key + 1] . '4', $value);
            $sheet->getStyle($alphas[$key + 1] . '4')->getFont()->setBold(true);
            $sheet->getStyle($alphas[$key + 1] . '4')->getAlignment()->setHorizontal('center');
        }

        $num = 5;
        $numAlpa = 1;
        foreach ($data as $dKey => $dVal) {
            $sheet->setCellValue('A' . $num, $dKey + 1);
            $sheet->getStyle('A' . $num)->getAlignment()->setHorizontal('center');
            foreach ($dVal as $key => $value) {
                $sheet->setCellValue($alphas[$numAlpa] . $num, $value);
                $sheet->getStyle($alphas[$numAlpa] . $num)->getAlignment()->setHorizontal('left');
                $sheet->getColumnDimension($alphas[$numAlpa])->setAutoSize(true);
                $numAlpa++;
            }
            $numAlpa = 1;
            $num++;
        }
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filePath = 'files/export/Generate Inv' . date(('Y-m-d')) . rand()  . '.xlsx';
        $writer->save($filePath);

        $export['status'] = true;
        $export['data'] = $filePath;
        return $export;
    }

    private function arrField()
    {
        return [
            'cust_number' => [
                'label' => 'Nomor Pelanggan',
                'orderable' => true,
                'searchable' => true
            ],
            'status_cust' => [
                'label' => 'Status Pelanggan',
                'orderable' => true,
                'searchable' => true
            ],
            'inv_numb' => [
                'label' => 'Nomor Invoice',
                'orderable' => true,
                'searchable' => true
            ],
            'pi_status' => [
                'label' => 'Status Invoice',
                'orderable' => true,
                'searchable' => true
            ],
            'result_msg' => [
                'label' => 'Status Pemabayaran',
                'orderable' => false,
                'searchable' => true
            ],

            'channel_pembayaran' => [
                'label' => 'Metode Pembayaran',
                'orderable' => false,
                'searchable' => true
            ],

            'rp_amount' => [
                'label' => 'Total',
                'orderable' => false,
                'searchable' => true
            ],

        ];
    }
}
