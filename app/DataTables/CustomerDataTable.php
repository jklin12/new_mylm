<?php

namespace App\DataTables;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CustomerDataTable extends DataTable
{
    var $arrPop = ['Bogor Valley', 'LIFEMEDIA', 'HABITAT', 'SINDUADI', 'GREENNET', 'X-LIFEMEDIA', 'LDP LIFEMEDIA', 'LDP X-LIFEMEDIA', 'JIP', 'Jogja Tronik', 'LDP JIP'];
    var $arrStatus = [1 => 'Registrasi', 'Instalasi', 'Setup', 'Sistem Aktif', 'Tidak Aktif', 'Trial', 'Sewa Khusus', 'Blokir', 'Ekslusif', 'CSR'];

    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('status_plg', function ($user) {
                $status = arrCustStatus($user->cupkg_status);
                if ($user->cupkg_status != 5) {
                    return $user->cupkg_status ? '<h5><span class="badge badge-' . $status[1] . '">' . $status[0]  . '</span></h5>'  : '';
                } else {
                    $reason = $user->cuin_reason == 1 ? 'Menunggak' : 'Permintaan Senidiri';
                    return $user->cupkg_status ? '<h5><a href="javascript:;" class="status_btn" data-cuindate="' . with(new Carbon($user->cuin_date))->isoFormat('dddd, D MMMM YYYY') . '" data-cuinreason="' . $reason . '" data-cuininfo="' . $user->cuin_info . '" ><span class="badge badge-' . $status[1] . '">' . $status[0]  . '</span></a></h5>'  : '';
                }
            })
            ->editColumn('cust_pop', function ($user) {
                return $user->cust_pop ? $this->arrPop[$user->cust_pop] : '';
            })
            ->editColumn('cuin_reason', function ($user) {
                return  $user->cuin_reason == 1 ?   'Permintaan Sendiri' : 'Menunggak';
            })
            ->editColumn('cupkg_svc_begin', function ($user) {
                return $user->cupkg_svc_begin ? with(new Carbon($user->cupkg_svc_begin))->isoFormat('ddd, D MMM YY') : '';
            })
            ->addColumn('durasi', function ($user) {
                $interval = '';
                if ($user->cupkg_status != 5) {
                    $datetime1 = date_create($user->cupkg_svc_begin);
                    $datetime2 = date_create(date('Y-m-d'));
                    $interval = date_diff($datetime1, $datetime2);
                    return $interval->format('%m bulan, %d hari');
                } else {
                    $datetime1 = date_create($user->cupkg_svc_begin);
                    $datetime2 = date_create($user->cuin_date);
                    $interval = date_diff($datetime1, $datetime2);
                    return $interval->format('%m bulan, %d hari');
                }
                return $interval;
            })
            ->addColumn('action', function ($row) {
                $actionBtn = '<a href="' . route('customer-detail', $row->cust_number) . '" class="btn btn-pink btn-icon btn-circle"><i class="fa fa-search-plus"></i></a>';
                return $actionBtn;
            })
            ->rawColumns(['status_plg', 'durasi', 'action'])
            ->setRowId('cust_number');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Customer $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Customer $model): QueryBuilder
    {
        return $model->select('t_customer.cust_number', 'cust_name', 'cust_address', 'cust_phone', DB::raw('trel_cust_pkg.sp_code'), 'cust_hp', 'cupkg_status', 'cupkg_svc_begin', 'cust_pop', 'cupkg_acct_manager', 'cuin_type', 'cuin_date', 'cuin_reason', 'cuin_info', 'cust_member_card', 'cust_kecamatan', 'cust_kelurahan', 'cust_rw', 'cust_rt')
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->leftJoin('t_customer_inactive', 't_customer.cust_number', '=', 't_customer_inactive.cust_number')
            ->groupBy('trel_cust_pkg._nomor')
            ->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        $button =[];
        if (Auth::user()->level >= 8) {
           $button[] = 'export';
        }
        return $this->builder()
            ->setTableId('customer-table')
            ->columns($this->getColumns())
            //->minifiedAjax()
            ->ajax([
                'url'  => route('customer-index'),
                'type' => 'GET',
                'data' => "function(data){
                    _token            = '{{ csrf_token() }}',
                    data.cupkg_status    = $('#filter_cupkg_status').val();
                    data.cust_pop    = $('#filter_cust_pop').val(); 
                    data.cust_kelurahan    = $('#filter_kelurahan').val(); 
                    data.cust_kecamatan    = $('#filter_kecamatan').val(); 
                }",
            ])
            ->dom('Bfrtip')
            ->orderBy(1)
            ->buttons(
                Button::make($button),
                Button::make('reload'),
            );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns(): array
    {
        $arrfield = $this->arrField();
        $i = 0;
        $tableColumn[$i]['data'] = 'DT_RowIndex';
        $tableColumn[$i]['name'] = 'DT_RowIndex';
        $tableColumn[$i]['title'] = 'No.';
        $tableColumn[$i]['orderable'] = 'false';
        $tableColumn[$i]['searchable'] = 'false';
        foreach ($arrfield as $key => $value) {

            $i++;
            $tableColumn[$i]['data'] = $key;
            $tableColumn[$i]['name'] = $key;
            $tableColumn[$i]['title'] = $value['label'];
            $tableColumn[$i]['orderable'] = $value['orderable'];
            $tableColumn[$i]['searchable'] = $value['searchable'];
            $tableColumn[$i]['visible'] = $value['visible'];
        }
        $tableColumn[$i + 1]['data'] = 'action';
        $tableColumn[$i + 1]['name'] = 'action';


        return $tableColumn;
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'Customer_' . date('YmdHis');
    }

    private function arrField()
    {
        return [
            'cust_number' => [
                'label' => 'Nomor',
                'orderable' => true,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => true
            ],
            'cust_name' => [
                'label' => 'Nama',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => true
            ],
            'cust_kecamatan' => [
                'label' => 'Kecamatan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => false
            ],
            'cust_kelurahan' => [
                'label' => 'Kelurahan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => false
            ],
            'cust_rw' => [
                'label' => 'RW',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => false
            ],
            'cust_rt' => [
                'label' => 'RT',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => false
            ],
            'cust_address' => [
                'label' => 'Alamat',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => false
            ],
            'cust_phone' => [
                'label' => 'No Telp',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => true
            ],
            'cust_member_card' => [
                'label' => 'Member Card',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => false
            ],
            'cupkg_acct_manager' => [
                'label' => 'AM',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => true
            ],
            'sp_code' => [
                'label' => 'Layanan',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => true
            ],

            'status_plg' => [
                'label' => 'Status',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'select',
                //'keyvaldata' => $this->arrStatus
                'visible' => true
            ],
            'cust_pop' => [
                'label' => 'POP',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'select',
                'visible' => true
                //'keyvaldata' => $this->arrPop

            ],
            'cupkg_svc_begin' => [
                'label' => 'Mulai Layanan',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => true
            ],
            'durasi' => [
                'label' => 'Lama Langganan',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => false
            ],
            'cuin_date' => [
                'label' => 'Tanggal Berhenti',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => false
            ], 
            'cuin_reason' => [
                'label' => 'Alasan Berhenti',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => false
            ],
             'cuin_info' => [
                'label' => 'Info Behenti',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => false
            ]
        ];
    }
}
