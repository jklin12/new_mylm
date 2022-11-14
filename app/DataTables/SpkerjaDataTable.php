<?php

namespace App\DataTables;

use App\Models\Spkerja;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class SpkerjaDataTable extends DataTable
{
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
            
            ->addColumn('action', function ($row) {
                $actionBtn = '<a href="' . route('porfoma-detail', $row->ft_number) . '" class="btn btn-pink btn-icon btn-circle"><i class="fa fa-search-plus"></i></a>';
                return $actionBtn;
            })
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Spkerja $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Spkerja $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('spkerja-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(1)
            ->buttons(
                Button::make('create'),
                Button::make('export'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
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
        return 'Spkerja_' . date('YmdHis');
    }

    protected function arrField()
    {
        return [
            'ft_number' => [
                'label' => 'Nomor SPK',
                'orderable' => true,
                'searchable' => true,
                'form_type' => 'text',
            ],
            /*'ft_svc_type' => [
                'label' => 'Diterima',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',
            ],**/
            'ft_received' => [
                'label' => 'Diterima',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',
            ],
            'ft_updated' => [
                'label' => 'Diupdate',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',
            ],
            'ft_type' => [
                'label' => 'Type',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',
            ],
            'ft_status' => [
                'label' => 'Status',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',
            ],
           
        ];
    }
}
