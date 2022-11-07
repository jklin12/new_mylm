<?php

namespace App\DataTables;

use App\Models\InvoicePorfoma;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Spatie\FlareClient\Flare;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class PorfomaReportDatatable extends DataTable
{

    var $arrPiStatus = [['Blum Bayar', 'danger'], ['Lunas', 'green'], ['Expired', 'dark']];
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
            ->editColumn('cupkg_status', function ($user) {
                return $user->cupkg_status ? $this->arrStatus[$user->cupkg_status] : '';
            })
            ->editColumn('inv_status', function ($user) {
                return isset($user->inv_status) ? $this->arrPiStatus[$user->inv_status][0] :  $user->inv_status;
            })
            ->editColumn('inv_post', function ($user) {
                return Carbon::parse($user->inv_post)->isoFormat('D MMMM YYYY HH:mm');
            })
            ->editColumn('inv_start', function ($user) {
                return Carbon::parse($user->inv_start)->isoFormat('D MMMM YYYY');
            })
            ->addColumn('action', function ($row) {
                $actionBtn = '<a href="' . route('porfoma-detail', $row->inv_number) . '" class="btn btn-pink btn-icon btn-circle"><i class="fa fa-search-plus"></i></a>';
                return $actionBtn;
            })
            ->setRowId('cust_number');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\PorfomaReportDatatable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(InvoicePorfoma $model): QueryBuilder
    {



        return $model->select(DB::raw('t_customer.cust_number, t_invoice_porfoma.sp_code'), 'inv_number', 'cust_name', 'cupkg_status', 'inv_status', 'inv_post', 'inv_start', 'wa_sent', 'wa_sent_number','cupkg_acct_manager')
            //->where('inv_start', '2022-10-21')
            //->whereRaw("MONTH(inv_start) = '10'")
            ->whereRaw("YEAR(inv_start) = '2022'")
            ->leftJoin('t_customer', 't_invoice_porfoma.cust_number', '=', 't_customer.cust_number')
            ->leftJoin('trel_cust_pkg', function ($join) {
                $join->on('t_customer.cust_number', '=', 'trel_cust_pkg.cust_number')->on('trel_cust_pkg._nomor', '=', 't_invoice_porfoma.sp_nom');
            })->orderByDesc('inv_start')
            ->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('porfomareportdatatable-table')
            ->columns($this->getColumns())
            //->minifiedAjax()
            ->ajax([
                'url'  => route('report-porfoma-detail'),
                'type' => 'GET',
                'data' => "function(data){
                    _token            = '{{ csrf_token() }}',
                    data.inv_status    = $('#filter_inv_status').val();
                    data.cupkg_status    = $('#filter_cupkg_status').val();
                    data.inv_start    = $('#daterange-filter input').val();
                    data.bulan    = $('#month-filter input').val();
                    data.tidak_terkirim    = $('#tidak_terkirim').val();
                }",
            ])
            //->searchPanes(false)
            ->dom('Bfrtip')
            ->orderBy(1)
            ->buttons(
                Button::make(['export']),
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
        $arrfield = $this->arrFieldPorfoma();
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
        return 'PorfomaReport_' . date('YmdHis');
    }

    protected function arrFieldPorfoma()
    {
        return [
            'cust_number' => [
                'label' => 'Cust Number',
                'orderable' => true,
                'searchable' => true,
                'form_type' => 'text',
            ],
            'cust_name' => [
                'label' => 'Nama',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',
            ],
            'sp_code' => [
                'label' => 'Layanan',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',
            ],
            'cupkg_status' => [
                'label' => 'Status',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'select',
                'keyvaldata' => $this->arrStatus
            ],
            'inv_number' => [
                'label' => 'Nomor PI',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',

            ],
            'inv_status' => [
                'label' => 'Status PI',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'select',
                'keyvaldata' => $this->arrPiStatus
            ],
            'inv_post' => [
                'label' => 'Posted',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'date',
            ],
            'inv_start' => [
                'label' => 'Mulai Layanan',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'date',
            ],
            'cupkg_acct_manager' => [
                'label' => 'AM',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
            ],
            'wa_sent' => [
                'label' => 'Kirim Invoice',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
            ],
            'wa_sent_number' => [
                'label' => 'Kirim Nomor',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
            ],
        ];
    }
}
