<?php

namespace App\Http\Controllers;

use App\Models\InvoicePorfoma;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use DataTables;


class PorfomaController extends Controller
{
    var $invStatus = [['belum lunas', 'danger'], ['lunas', 'green'], ['expired', 'warning']];
    public function index(Request $request, $cust_number)
    {
        $title = 'Porfoma ' . $cust_number;
        $subTitle = '';

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

        $load['cust_number'] = $cust_number;
        $load['arr_field'] = $arrfield;
        $load['table_column'] = json_encode($tableColumn);
        //dd($load);

        return view('pages/porfoma/index', $load);
    }

    public function list(Request $request, $cust_number)
    {

        //dd($data);

        if ($request->ajax()) {
            $data = DB::table('t_invoice_porfoma')
                ->selectRaw('cust_number,t_invoice_porfoma.inv_number,inv_due,inv_start,inv_status,sum(t_inv_item_porfoma.ii_amount) as totals')
                ->where('cust_number', $cust_number)
                ->leftJoin('t_inv_item_porfoma', function ($join) {
                    $join->on('t_invoice_porfoma.inv_number', '=', 't_inv_item_porfoma.inv_number')->where('ii_recycle', '<>', 1);
                })
                ->groupByRaw('t_invoice_porfoma.inv_number')
                ->orderByDesc('inv_start');

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('terlambat', function ($user) {
                    $terlambat = 0;
                    if ($user->inv_status == 0) {
                        if (date('Y-m-d') > $user->inv_start) {
                            $diff = date_diff(date_create(date('Y-m-d')), date_create($user->inv_start));
                            $terlambat = $diff->format("%a");
                        }
                    }
                    return $terlambat . ' hari';
                })
                ->editColumn('inv_due', function ($user) {
                    return $user->inv_due ? with(new Carbon($user->inv_due))->isoFormat('dddd, D MMMM Y') : '';
                })
                ->editColumn('inv_start', function ($user) {
                    return $user->inv_start ? with(new Carbon($user->inv_start))->isoFormat('dddd, D MMMM Y') : '';
                })
                ->addColumn('badge_status', function ($user) {
                    return  '<span class="badge badge-' . $this->invStatus[$user->inv_status][1] . '">' . $this->invStatus[$user->inv_status][0] . '</span>';
                })
                ->addColumn('detail', function ($row) {
                    $actionBtn = '<a href="' . route('porfoma-detail', $row->inv_number) . '" class="btn btn-pink btn-icon btn-circle"><i class="fa fa-search-plus"></i></a>';
                    return $actionBtn;
                })

                ->rawColumns(['detail', 'badge_status'])
                ->make(true);
        }
    }

    public function detail($inv_number)
    {
        $title = 'Detail Poroma ' . $inv_number;
        $subTitle = '';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;

        $data = InvoicePorfoma::where('t_invoice_porfoma.inv_number', $inv_number)
            ->leftJoin('t_customer', 't_invoice_porfoma.cust_number', '=', 't_customer.cust_number')
            ->leftJoin('trel_cust_pkg', 't_invoice_porfoma.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->leftJoin('t_inv_item_porfoma', function ($join) {
                $join->on('t_invoice_porfoma.inv_number', '=', 't_inv_item_porfoma.inv_number')->where('ii_recycle', '<>', 1);
            })->get();
        $data = $data->toArray();

        $susunData = [];
        $susunDataSummary = [];

        $totals = 0;
        

        foreach ($data as $key => $value) {
            foreach ($value as $keys => $values) {
                $susunData[$keys] = $values;
            }
            $totals += $value['ii_amount'];


            $susunDataSummary[$key]['ii_order'] = $value['ii_order'];
            $susunDataSummary[$key]['ii_type'] = $value['ii_type'];
            $susunDataSummary[$key]['ii_amount'] = 'Rp. '.SchRp($value['ii_amount']);
            $susunDataSummary[$key]['ii_info'] = $value['ii_info'];
        }
        
        $susunData2 = [];
        $arrfield = $this->arrFieldDetail();

        foreach ($arrfield[0]['data'] as $key => $value) {
            //print_r($value);die;
            if ($value['form_type'] == 'text') {
                $susunData2[$key] = $susunData[$key];
            }else if ($value['form_type'] == 'date') {
                $susunData2[$key] = with(new Carbon($susunData[$key]))->isoFormat('dddd, D MMMM Y');
            }else if ($value['form_type'] == 'date_time') {
                $susunData2[$key] = with(new Carbon($susunData[$key]))->isoFormat('dddd, D MMMM Y H:m');
            }else if ($value['form_type'] == 'select_status') {
                $susunData2[$key] = '<span class="badge badge-' . $this->invStatus[$susunData[$key]][1] . '">' . $this->invStatus[$susunData[$key]][0] . '</span>';
            }
        }

        $susunData2['totals'] = 'Rp. '.SchRp($totals);
        //dd($susunData2); 
        $load['inv_status'] = $susunData['inv_status'];
        $load['cust_number'] = $susunData['cust_number'];
        $load['datas'] = $susunData2;
        $load['data_summary'] = $susunDataSummary;
        $load['arr_field'] = $arrfield;

        return view('pages/porfoma/detail', $load);
    }

    private function arrField()
    {
        return [
            'cust_number' => [
                'label' => 'Nomor',
                'orderable' => true,
                'searchable' => true,
                'form_type' => 'text',
            ],
            'inv_number' => [
                'label' => 'PI',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
            ],
            'totals' => [
                'label' => 'Jumlah',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',
            ],
            'inv_due' => [
                'label' => 'Jatuh Tempo',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
            ],

            'badge_status' => [
                'label' => 'Status',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $this->invStatus,
            ],
            'terlambat' => [
                'label' => 'Terlambat',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
            ],
            'inv_start' => [
                'label' => 'Mulai Layanan',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
            ],
           
        ];
    }

    public function arrFieldDetail()
    {

        return $arrfield = [
            [
                'title' => 'Informasi Pelanggan',
                'data' => [
                    'cust_number' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Nomor Pelanggan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_name' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Nama Pelanggan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'inv_number' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Nomor Invoice',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'sp_code' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Layanan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'inv_status' => [
                        'form' => true,
                        'form_type' => 'select_status',
                        'label' => 'Status',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'inv_start' => [
                        'form' => true,
                        'form_type' => 'date',
                        'label' => 'Awal Periode',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'inv_end' => [
                        'form' => true,
                        'form_type' => 'date',
                        'label' => 'Akhir Periode',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'inv_due' => [
                        'form' => true,
                        'form_type' => 'date',
                        'label' => 'Jatuh Tempo',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'inv_due' => [
                        'form' => true,
                        'form_type' => 'date',
                        'label' => 'Jatuh Tempo',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'inv_post' => [
                        'form' => true,
                        'form_type' => 'date_time',
                        'label' => 'Posted',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'inv_paid' => [
                        'form' => true,
                        'form_type' => 'date_time',
                        'label' => 'Dibayar',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'inv_info' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Info',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'inv_updated' => [
                        'form' => true,
                        'form_type' => 'date_time',
                        'label' => 'Update',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'inv_updated_by' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Update By',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],

                ],

            ],
            [
                'title' => 'Summaray',
                'data' => [
                    'ii_order' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Nomor',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'ii_type' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Tipe',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'ii_amount' => [
                        'form' => true,
                        'form_type' => 'rp',
                        'label' => 'Jumlah',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'ii_info' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'info',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                ],

            ],

        ];
    }
}
