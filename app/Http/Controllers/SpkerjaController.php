<?php

namespace App\Http\Controllers;

use App\DataTables\SpkerjaDataTable;
use App\Models\Spkerja;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DataTables;

class SpkerjaController extends Controller
{
    public function index(SpkerjaDataTable $dataTable, Request $request, $cust_number)
    {
        $title = 'Sp Kerja ' . $cust_number;
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

        return view('pages/spkerja/index', $load);
    }

    public function list(Request $request, $cust_number)
    {

        //dd($data);

        if ($request->ajax()) {
            $data = Spkerja::where('cust_number', $cust_number)->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('ft_received', function ($user) {
                    return $user->ft_received ? with(new Carbon($user->ft_received))->isoFormat('dddd, D MMMM Y H:m') : '';
                })
                ->editColumn('ft_updated', function ($user) {
                    return $user->ft_updated ? with(new Carbon($user->ft_updated))->isoFormat('dddd, D MMMM Y H:m') : '';
                })
                ->editColumn('ft_type', function ($user) {
                    return $user->ft_type ? spkType($user->ft_type) : '';
                })
                ->editColumn('ft_status', function ($user) {
                    return $user->ft_status ? spkVal($user->ft_type) : '';
                })
                ->addColumn('detail', function ($row) {
                    $actionBtn = '<a href="' . route('spk-detail', 'spk='.urlencode($row->ft_number)) . '" class="btn btn-pink btn-icon btn-circle"><i class="fa fa-search-plus"></i></a>';
                    return $actionBtn;
                })

                ->rawColumns(['detail', 'badge_status'])
                ->make(true);
        }
    }

    public function detail(Request $request){

        $spk = $request->get('spk');
        $data = Spkerja::find($spk)
            ->leftJoin('t_employee','t_field_task.ft_updated_by','=','t_employee.emp_number')
            ->first();

        $title = 'Sp Kerja ' . $spk;
        $subTitle = $data->cust_number;

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $load['datas'] = $data;

        //dd($data->toArray());
        return view('pages/spkerja/detail', $load);
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
