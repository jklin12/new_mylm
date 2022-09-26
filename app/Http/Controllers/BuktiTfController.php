<?php

namespace App\Http\Controllers;

use App\Models\BuktiTf;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BuktiTfController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Data Bukti Transfer';
        $subTitle = 'Metoda Pembayaran BCA & QRIS';

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
            if ($key == 'bukti_tf_file') {
                $tableColumn[$i]['data'] = 'image_bukti_tf';
                $tableColumn[$i]['name'] = $value['label'];
                $tableColumn[$i]['orderable'] = $value['orderable'];
                $tableColumn[$i]['searchable'] = $value['searchable'];
            } elseif ($key == 'bukti_tf_status') {
                $tableColumn[$i]['data'] = 'status_bukti_tf';
                $tableColumn[$i]['name'] = $value['label'];
                $tableColumn[$i]['orderable'] = $value['orderable'];
                $tableColumn[$i]['searchable'] = $value['searchable'];
            } else {
                $tableColumn[$i]['data'] = $key;
                $tableColumn[$i]['name'] = $value['label'];
                $tableColumn[$i]['orderable'] = $value['orderable'];
                $tableColumn[$i]['searchable'] = $value['searchable'];
            }
        }
        $tableColumn[$i + 1]['data'] = 'detail';
        $tableColumn[$i + 1]['name'] = 'detail';

        $load['arr_field'] = $arrfield;
        $load['table_column'] = json_encode(array_values($tableColumn));

        return view('pages/bukti_tf/index', $load);
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $data = BuktiTf::latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_bukti_tf', function ($row) {
                    $badge = '<span class="badge badge-warning">Baru</span>';
                    if ($row->bukti_tf_status > 0) {
                        $badge = '<span class="badge badge-green">Approve</span>';
                    }
                    return $badge;
                })
                ->addColumn('image_bukti_tf', function ($row) {
                    $image = '<a href="javascript:;" class="image_btn" data-toggle="modal" data-target="#modalImage"  data-img="' . $row->bukti_tf_file . '"><img src="' . $row->bukti_tf_file . '" alt="" srcset="" height="100"></a>';
                    return $image;
                })
                ->addColumn('detail', function ($row) {
                    $actionBtn = '<a href="' . route('bukti_tf.edit', $row->bukti_tf_id) . '" id="" class="edit btn btn-yellow btn-sm text-white">Edit</a> <a href="javascript:;" class="delete btn btn-danger btn-sm " data-toggle="modal" data-target="#modalDelete" data-route="' . route('bukti_tf.destroy', $row->bukti_tf_id) . '" data-title="' . $row->bukti_tf_cust . '">Delete</a>';
                    return $actionBtn;
                })
                ->rawColumns(['detail', 'image_bukti_tf', 'status_bukti_tf'])
                ->make(true);
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Tambah Bukti Transfer';
        $subTitle = '';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;

        $required = [];
        $form = [];
        $arrfield = $this->arrField();

        foreach ($arrfield as $key => $value) {
            if ($value['form']) {
                if ($value['required']) {
                    $required[$key] = 'required';
                }
                $form[$key] = $value;
            }
        }
        $load['required'] = $required;
        $load['form'] = $form;


        return view('pages.bukti_tf.form', $load);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $arrfield = $this->arrField();
        $validator = [];
        $validatorMessage = [];
        foreach ($arrfield as $key => $value) {
            if ($value['form']) {
                if ($value['required']) {

                    if ($value['form_type'] == 'file') {
                        $validator[$key] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
                        $validatorMessage[$key . '.required'] = $value['label'] . ' tidak boleh kosong';
                        $validatorMessage[$key . '.mimes'] = 'Jenis File tidak diijinkan';
                    } else {
                        $validator[$key] = 'required';
                        $validatorMessage[$key . '.required'] = $value['label'] . ' tidak boleh kosong';
                    }
                }
            }
        }
        //print_r($validator);die;

        $request->validate($validator, $validatorMessage);

        $input = $request->all();
        if ($image = $request->file('bukti_tf_file')) {

            $destinationPath = 'files/bukti_tf';

            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();

            $image->move($destinationPath, $profileImage);

            $input['bukti_tf_file'] = "$destinationPath/$profileImage";
        }

        BuktiTf::create($input);

        return redirect()->route('bukti_tf.index')
            ->with('success', 'Input data berhasul.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $title = 'Edit Bukti Transfer';
        $subTitle = '';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;

        $required = [];
        $form = [];
        $arrfield = $this->arrField();

        foreach ($arrfield as $key => $value) {
            if ($value['form']) {
                if ($value['required']) {
                    $required[$key] = 'required';
                }
                $form[$key] = $value;
            }
        }
        $load['required'] = $required;
        $load['form'] = $form;


        return view('pages.bukti_tf.form', $load);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cek = BuktiTf::find($id);

        if ($cek) {
            $cek->delete();
            return redirect()->back()
                ->with('success', 'Hapus data berhasul.');
        }
        return redirect()->back()
            ->with('error', 'Hapus data gagal.');
    }

    private function arrField()
    {
        return [
            'bukti_tf_cust' => [
                'form' => true,
                'form_type' => 'text',
                'label' => 'Nomor Pelanggan',
                'orderable' => true,
                'searchable' => true,
                'required' => true
            ],
            'bukti_tf_inv' => [
                'form' => true,
                'form_type' => 'text',
                'label' => 'Nomor Invoice',
                'orderable' => true,
                'searchable' => true,
                'required' => false
            ],
            'bukti_tf_status' => [
                'form' => false,
                'form_type' => '',
                'label' => 'Status',
                'orderable' => false,
                'searchable' => false,

            ],
            'bukti_tf_date' => [
                'form' => true,
                'form_type' => 'date',
                'label' => 'Tanggal',
                'orderable' => true,
                'searchable' => true,
                'required' => true
            ],
            'bukti_tf_file' => [
                'form' => true,
                'form_type' => 'file',
                'label' => 'File',
                'orderable' => false,
                'searchable' => true,
                'required' => true
            ],

            'bukti_tf_desc' => [
                'form' => true,
                'form_type' => 'area',
                'label' => 'Deskripsi',
                'orderable' => false,
                'searchable' => true,
                'required' => false
            ],
        ];
    }
}
