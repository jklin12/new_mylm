<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OltController extends Controller
{

    public function index(Request $request)
    {
        $dataOlt = Http::get('http://202.169.224.46:8080/index.php/onu/dataOlt');
        $respOlt = json_decode($dataOlt->body(), true);

        $resData['datas'] = [];
        if ($request->has('olt') || $request->has('cust_number')) {
            $olt = $request->input('olt');
            $cust_number = $request->input('cust_number');

            $response = Http::get('http://202.169.224.46:8080/index.php/onu/data?olt='.$olt.'&cust_number='.$cust_number);

            $resData = json_decode($response->body(), true);
            //print_r($resData);die;
        }

        $title = 'ONU Data';
        $subTitle = 'Pada Semua Olt';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $load['data_olt'] = $respOlt;
        $load['data'] = $resData['datas'];

        return view('pages/olt/index', $load);
    }

    public function uncfg()
    {
        $response = Http::get('202.169.224.46:5000/uncfg');

        $resData = json_decode($response->body(), true);

        $title = 'ONU Unconfig';
        $subTitle = 'Pada Semua Olt';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $load['data'] = $resData;


        return view('pages/olt/uncfg', $load);
    }

    public function register(Request $request)
    {

        $ipOlt = $request->input('ip_olt');
        $interface = $request->input('interface');
        $sn = $request->input('sn');
        $olt = $request->input('olt');
        $type = $request->input('type');

        $postVal['ip_olt'] = $ipOlt;
        $profile = Http::asForm()->post('http://202.169.224.46:5000/profile', $postVal);
        $resProfile = json_decode($profile->body(), true);
        //dd($resProfile);

        $postVal['interface'] = $interface;
        #print_r($postVal);die;
        $response = Http::asForm()->post('http://202.169.224.46:5000/showRun', $postVal);
        $resData = json_decode($response->body(), true);
        //print_r($resData);

        $title = 'Register ONU';
        $subTitle = $sn;

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;

        $i = 1;
        foreach ($resData as $key => $value) {
            if ($i != $value[1]) {
                $i = $i;
            } else {
                $i++;
            }
        }
        $onuInterface = str_replace('gpon-olt_', 'gpon-onu_', $interface);
        $load['onu_index'] = $onuInterface . ':' . $i;
        $load['onu_data'] = $resData;
        $load['ip_olt'] = $ipOlt;
        $load['interface'] = $interface;
        $load['olt'] = $olt;
        $load['type'] = $type;
        $load['sn'] = $sn;
        $load['profile'] = $resProfile;

        return view('pages/olt/register', $load);
    }

    public function api(Request $request)
    {

        $ipOlt = $request->input('olt');
        $interface = $request->input('interface');
        $url = $request->input('url');


        $url = 'http://202.169.224.46:5000/' . $url;
        $postVal['ip_olt'] = $ipOlt;
        $postVal['interface'] = $interface;

        $response = Http::asForm()->post($url, $postVal);

        $resData = json_decode($response->body(), true);

        $susunData = "";
        $susunData .= '<p>';
        $tableData = [];
        foreach ($resData as $key => $value) {

            if ($key < 27) {
                foreach ($value as $keys => $values) {
                    $susunData .= $values . ' ';
                }
                $susunData .= '<br>';
            } else {
                $tableData[] = $value;
            }
        }

        $table = "";
        if ($tableData) {
            $table = "<table>";
            foreach ($tableData as $key => $value) {
                if ($key == 0) {
                    $table .= "<thead>";
                    foreach ($value as $keys => $values) {
                        $table .= "<th>" . $values . "</th>";
                    }
                    $table .= "</thead>";
                } else {
                    $table .= "<tr>";
                    foreach ($value as $keys => $values) {
                        $table .= "<td>" . $values . "</td>";
                    }
                    $table .= "</tr>";
                }
            }
            $table .= "</table>";
        }

        $susunData .= '</p>';
        $susunData .= $table;

        return response()
            ->json($susunData);
    }
}
