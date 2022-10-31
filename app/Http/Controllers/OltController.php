<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use \RouterOS\Client;
use \RouterOS\Query;

class OltController extends Controller
{

    var $pppoeProfile = [
        '192.168.99.31' => 'new-pppoe-str',
        '192.168.99.68' => 'new-pppoe-cdt',
        '192.168.99.71' => 'new-pppoe-kds',
        '192.168.99.72' => 'new-pppoe-tmh',
        '192.168.99.73' => 'new-pppoe-gjn',
        '192.168.99.74' => 'new-pppoe-ngp',
        '192.168.99.75' => 'new-pppoe-jec',
        '192.168.99.76' => 'new-pppoe-gdn',
        '192.168.99.77' => 'new-pppoe-pgo',
        '192.168.99.78' => 'new-pppoe-tmh',
    ];

    var $valn = [
        '192.168.99.31' => '112',
        '192.168.99.68' => '114',
        '192.168.99.71' => '115',
        '192.168.99.72' => '117',
        '192.168.99.73' => '118',
        '192.168.99.74' => '119',
        '192.168.99.75' => '120',
        '192.168.99.76' => '121',
        '192.168.99.77' => '116',
        '192.168.99.78' => '117',
    ];

    public function index(Request $request)
    {
        $dataOlt = Http::get('http://202.169.224.46:8080/index.php/onu/dataOlt');
        $respOlt = json_decode($dataOlt->body(), true);

        $resData['datas'] = [];
        if ($request->has('olt') || $request->has('cust_number')) {
            $olt = $request->input('olt');
            $cust_number = $request->input('cust_number');

            $response = Http::get('http://202.169.224.46:8080/index.php/onu/data?olt=' . $olt . '&cust_number=' . $cust_number);

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

    public function register(Request $request, $step = 0)
    {

        $ipOlt = $request->input('ip_olt');
        $interface = $request->input('interface');
        $sn = $request->input('sn');
        $olt = $request->input('olt');
        $type = $request->input('type');



        if ($step == 0) {

            $profilePppoe = $this->pppoeProfile[$ipOlt];
            $load['profile_pppoe'] = $profilePppoe;

            $newIp = $this->getNewIp($profilePppoe);
            $load['new_ip'] = $newIp['new_ip'];
            $load['ip_terdekat'] = $newIp['ip_terdekat'];
        } elseif ($step == 1) {
            $name = $request->input('name');
            $client = new Client([
                'host' => '202.169.224.19',
                'user' => 'faris123',
                'pass' => 'faris123',
                'port' => 9778,
            ]);
            $query =
                (new Query('/ppp/secret/print'))
                ->where('name', $name);

            $response = $client->query($query)->read();

            $load['name'] = $name;
            $load['ppp_result'] = $response;
        } elseif ($step == 2) {
            $name = $request->input('name');
            $load['name'] = $name;

            $postVal['ip_olt'] = $ipOlt;
            $profile = Http::asForm()->post('http://202.169.224.46:5000/profile', $postVal);
            $resProfile = json_decode($profile->body(), true);
            //dd($resProfile);

            $profileTcon = Http::asForm()->post('http://202.169.224.46:5000/profileTcon', $postVal);
            $resProfileTcon = json_decode($profileTcon->body(), true);

            //dd($resProfileTcon);

            $postVal['interface'] = $interface;
            #print_r($postVal);die;
            $response = Http::asForm()->post('http://202.169.224.46:5000/showRun', $postVal);
            $resData = json_decode($response->body(), true);
            //dd($resData);

            $dataProfileTrafic = [];
            $dataProfileTcon = [];
            if ($ipOlt == '192.168.99.78') {
                $dataProfileTrafic = $resProfile;
                $dataProfileTcon = $resProfileTcon;
            } else {
                foreach ($resProfile as $key => $value) {
                    $dataProfileTrafic[$key][0] = $value[1];
                    $dataProfileTrafic[$key][1] = $value[2];
                }
                foreach ($resProfileTcon as $key => $value) {
                    $dataProfileTcon[$key][0] = $value[1];
                    $dataProfileTcon[$key][1] = $value[2];
                }
            }

            $i = 1;
            foreach ($resData as $key => $value) {
                if ($i != $value[1]) {
                    $i = $i;
                } else {
                    $i++;
                }
            }
            $onuInterface = str_replace('gpon-olt_', 'gpon-onu_', $interface);


            $load['onu_index'] = $i;
            $load['onu_data'] = $resData;

            $load['profile'] = $dataProfileTrafic;
            $load['profile_tcon'] = $dataProfileTcon;
            $load['vlan'] = $this->valn[$ipOlt];
        } elseif ($step == 3) {

            $onu_index = $request->input('onu_index');

            $gponOnu = 'gpon-onu_' . $interface . ':' . $onu_index;

            $url = 'http://202.169.224.46:5000/cekRegister';
            $postVal['ip_olt'] = $ipOlt;
            $postVal['onu'] = $gponOnu;

            $response = Http::asForm()->post($url, $postVal);
            $resData = json_decode($response->body(), true);

            //dd($resData);

            $load['onu_result'] = $resData;
        }

        $title = 'Register Pelanggan Baru';
        $subTitle = $sn;

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;

        $load['ip_olt'] = $ipOlt;
        $load['interface'] = $interface;
        $load['olt'] = $olt;
        $load['type'] = $type;
        $load['sn'] = $sn;
        $load['step'] = $step;


        return view('pages/olt/register', $load);
    }

    public function pppRegister(Request $request)
    {

        $ipOlt = $request->input('ip_olt');
        $interface = $request->input('interface');
        $sn = $request->input('sn');
        $olt = $request->input('olt');
        $type = $request->input('type');
        $name = $request->input('name');
        $service = $request->input('service');
        $profile = $request->input('profile');
        $remote_address = $request->input('remote_address');

        //print_r($request->all());

        $client = new Client([
            'host' => '202.169.224.19',
            'user' => 'faris123',
            'pass' => 'faris123',
            'port' => 9778,
        ]);

        $query =
            (new Query('/ppp/secret/add'))
            ->equal('name', $name)
            ->equal('password', $sn)
            ->equal('service', $service)
            ->equal('profile', $profile)
            ->equal('remote-address', $remote_address);

        $response = $client->query($query)->read();
        //print_r($response);die;

        return redirect(route('olt-register', ('1?olt=' . $olt . '&ip_olt=' . $ipOlt . '&interface=' . $interface . '&sn=' . $sn . '&type=' . $type . '&name=' . $name)));
    }

    public function onuRegister(Request $request)
    {
        //dd($request->all());
        $jenis = $request->input('jenis');
        $ipOlt = $request->input('ip_olt');
        $interface = $request->input('interface');
        $sn = $request->input('sn');
        $olt = $request->input('olt');
        $type = $request->input('type');
        $onuIndex = $request->input('onu_index');
        $name = $request->input('name');
        $profileTcon1 = $request->input('tcon_profile_1');
        $profileTcon2 = $request->input('tcon_profile_2');
        $profileTrafic1 = $request->input('trafic_profile_1');
        $profileTrafic2 = $request->input('trafic_profile_2');
        $vlan = $request->input('vlan');

        //echo $jenis;
        $explodeInt = explode('_', $interface);
        $comand = [];
        if ($ipOlt == '192.168.99.78') {
            $comand[] =  'interface ' . $interface;
            $comand[] =  'onu ' . $onuIndex . ' type ZXHN-' . $type . ' sn ' . $sn;
            $comand[] =  '!';
            $comand[] =  'interface gpon-onu_' . $explodeInt[1] . ':' . $onuIndex;
            $comand[] =  'description ' . $name . ' sn ' . $sn . ' gpon-onu_' . $explodeInt[1] . ':' . $onuIndex;
            $comand[] =  'tcont 1 profile ' . $profileTcon1;
            if ($profileTcon2) {
                $comand[] =  'tcont 2 profile ' . $profileTcon2;
            }
            if ($jenis == '1') {
                $comand[] =  'gemport 1 name INTERNET unicast tcont 1 dir both';
                $comand[] =  'gemport 1 traffic-limit upstream ' . $profileTrafic1 . ' downstream ' . $profileTrafic1;
                $comand[] =  'switchport mode hybrid vport 1';
                $comand[] =  'service-port 1 vport 1 user-vlan ' . $vlan . ' vlan ' . $vlan;
            } elseif ($jenis == '2') {
                $comand[] =  'gemport 1 name PPPOE unicast tcont 1 dir both';
                $comand[] =  'gemport 1 traffic-limit upstream ' . $profileTrafic1 . ' downstream ' . $profileTrafic1;
                $comand[] =  'switchport mode hybrid vport 1';
                $comand[] =  'service-port 1 vport 1 user-vlan ' . $vlan . ' vlan ' . $vlan;
                if ($profileTrafic2) {
                    $comand[] =  'gemport 2 name INTERNNET unicast tcont 2 dir both';
                    $comand[] =  'gemport 2 traffic-limit upstream ' . $profileTrafic2 . ' downstream ' . $profileTrafic2;
                    $comand[] =  'switchport mode hybrid vport 2';
                    $comand[] =  'service-port 2 vport 2 user-vlan 211 vlan 211';
                }
            }
            $comand[] =  '!';
            $comand[] =  'pon-onu-mng gpon-onu_' . $explodeInt[1] . ':' . $onuIndex;
            if ($jenis == '1') {
                $comand[] =  'service INTERNET gemport 1 vlan ' . $vlan;
                $comand[] =  'wan-ip 1 mode pppoe username ' . $name . ' password ' . $sn . ' vlan-profile vlan-' . $vlan . ' host 1';
                $comand[] =  'security-mng 1 state enable mode permit protocol web';
                $comand[] =  'wan 1 service internet host 1';
            } elseif ($jenis == '2') {
                $comand[] =  'service PPPOE gemport 1 vlan ' . $vlan;
                $comand[] =  'service INTERNET gemport 2 vlan 211';
                $comand[] = 'interface eth eth_0/1 state lock';
                $comand[] = 'interface eth eth_0/2 state lock';
                $comand[] = 'interface eth eth_0/3 state lock';
                $comand[] = 'interface eth eth_0/4 state lock';
                $comand[] = 'interface wifi wifi_0/2 state lock';
                $comand[] = 'interface wifi wifi_0/3 state lock';
                $comand[] = 'interface wifi wifi_0/4 state lock';
                $comand[] = 'vlan port eth_0/1 mode tag vlan 211';
                $comand[] = 'vlan port eth_0/2 mode tag vlan 211';
                $comand[] = 'vlan port eth_0/3 mode tag vlan 211';
                $comand[] = 'vlan port eth_0/4 mode tag vlan 211';
                $comand[] = 'vlan port wifi_0/1 mode tag vlan 211';
                $comand[] = 'vlan port wifi_0/2 mode tag vlan 211';
                $comand[] = 'vlan port wifi_0/3 mode tag vlan 211';
                $comand[] = 'vlan port wifi_0/4 mode tag vlan 211';
                $comand[] = 'dhcp-ip ethuni eth_0/1 from-internet';
                $comand[] = 'dhcp-ip ethuni eth_0/2 from-internet';
                $comand[] = 'dhcp-ip ethuni eth_0/3 from-internet';
                $comand[] = 'dhcp-ip ethuni eth_0/4 from-internet';
                $comand[] =  'wan-ip 1 mode pppoe username ' . $name . ' password ' . $sn . ' vlan-profile vlan-' . $vlan . ' host 1';
                $comand[] =  'security-mng 1 state enable mode permit protocol web';
            }
            $comand[] =  '!';
        } else {

            #$comand[] =  'conf t';
            $comand[] =  'interface ' . $interface;
            $comand[] =  'onu ' . $onuIndex . ' type ZXHN-' . $type . ' sn ' . $sn;
            $comand[] = '!';
            $comand[] =  'interface gpon-onu_' . $explodeInt[1] . ':' . $onuIndex;
            $comand[] =  'description ' . $name . ' sn ' . $sn . ' gpon-onu_' . $explodeInt[1] . ':' . $onuIndex;
            $comand[] =  'tcont 1 profile ' . $profileTcon1;
            if ($profileTcon2) {
                $comand[] =  'tcont 2 profile ' . $profileTcon2;
            }
            if ($jenis == '1') {
                $comand[] =  'gemport 1 name INTERNET tcont 1';
                $comand[] =  'gemport 1 traffic-limit downstream ' . $profileTrafic1;
                $comand[] =  'service-port 1 vport 1 user-vlan ' . $vlan . ' vlan ' . $vlan;
            } elseif ($jenis = '2') {
                $comand[] =  'gemport 1 name PPPOE tcont 1';
                $comand[] =  'gemport 1 traffic-limit downstream ' . $profileTrafic1;
                $comand[] =  'service-port 1 vport 1 user-vlan ' . $vlan . ' vlan ' . $vlan;
                if ($profileTrafic2) {
                    $comand[] =  'gemport 2 name INTERNET tcont 2';
                    $comand[] =  'gemport 2 traffic-limit downstream ' . $profileTrafic2;
                    $comand[] =  'service-port 2 vport 2 user-vlan 211 vlan 211';
                }
            }
            $comand[] = '!';
            $comand[] =  'pon-onu-mng gpon-onu_' . $explodeInt[1] . ':' . $onuIndex;
            if ($jenis == '1') {
                $comand[] =  'service INTERNET gemport 1 vlan ' . $vlan;
                $comand[] =  'wan-ip 1 mode pppoe username ' . $name . ' password ' . $sn . ' vlan-profile vlan-' . $vlan . ' host 1';
                $comand[] =  'security-mgmt 1 state enable mode forward protocol web';
                $comand[] =  'wan 1 service internet host 1';
            } elseif ($jenis == '2') {
                $comand[] =  'service PPPOE gemport 1 vlan ' . $vlan;
                $comand[] =  'service INTERNET gemport 2 vlan 211';
                $comand[] = 'interface eth eth_0/1 state lock';
                $comand[] = 'interface eth eth_0/2 state lock';
                $comand[] = 'interface eth eth_0/3 state lock';
                $comand[] = 'interface eth eth_0/4 state lock';
                $comand[] = 'interface wifi wifi_0/2 state lock';
                $comand[] = 'interface wifi wifi_0/3 state lock';
                $comand[] = 'interface wifi wifi_0/4 state lock';
                $comand[] = 'vlan port eth_0/1 mode tag vlan 211';
                $comand[] = 'vlan port eth_0/2 mode tag vlan 211';
                $comand[] = 'vlan port eth_0/3 mode tag vlan 211';
                $comand[] = 'vlan port eth_0/4 mode tag vlan 211';
                $comand[] = 'vlan port wifi_0/1 mode tag vlan 211';
                $comand[] = 'vlan port wifi_0/2 mode tag vlan 211';
                $comand[] = 'vlan port wifi_0/3 mode tag vlan 211';
                $comand[] = 'vlan port wifi_0/4 mode tag vlan 211';
                $comand[] = 'dhcp-ip ethuni eth_0/1 from-internet';
                $comand[] = 'dhcp-ip ethuni eth_0/2 from-internet';
                $comand[] = 'dhcp-ip ethuni eth_0/3 from-internet';
                $comand[] = 'dhcp-ip ethuni eth_0/4 from-internet';
                $comand[] =  'wan-ip 1 mode pppoe username ' . $name . ' password ' . $sn . ' vlan-profile vlan-' . $vlan . ' host 1';
                $comand[] =  'security-mgmt 1 state enable mode forward protocol web';
                if ($jenis == '1') {
                    $comand[] =  'wan 1 service internet host 1';
                }
            }
            $comand[] = '!';
            $comand[] = 'end';
            $comand[] = 'wr';
        }

        //print_r($comand);die;

        $postVal['ip_olt'] = $ipOlt;
        $postVal['onu'] = 'gpon-onu_' . $explodeInt[1] . ':' . $onuIndex;;
        $postVal['comm'] = json_encode($comand);
        //dd($postVal);

        $url = 'http://202.169.224.46:5000/config';
        $response = Http::asForm()->post($url, $postVal);
        $resData = json_decode($response->body(), true);
        //dd($resData);
        $postVal['name'] = $name;
        Http::asForm()->post('http://202.169.224.46:8080/index.php/onu/addLog', $postVal);

        //dd($resData);

        return redirect(route('olt-register', ('3?olt=' . $olt . '&ip_olt=' . $ipOlt . '&interface=' . $explodeInt[1]  . '&sn=' . $sn . '&type=' . $type . '&onu_index=' . $onuIndex)));
    }

    public function getLog()
    {
        $url = 'http://202.169.224.46:8080/index.php/onu/getLog';
        $response = Http::get($url);
        $resData = json_decode($response->body(), true);


        $susunData = [];
        foreach ($resData as $key => $value) {
            $susunData[$key]['olt'] = $value['name'];
            $susunData[$key]['ip_olt'] = $value['ip'];
            $susunData[$key]['register_log_name'] = $value['register_log_name'];
            $susunData[$key]['register_onu'] = $value['register_onu'];
            $susunData[$key]['register_log_command'] = ($value['register_log_command']);
            $susunData[$key]['created_at'] = Carbon::parse($value['created_at'])->isoFormat('dddd, D MMMM Y H:mm');
        }
        //dd($susunData);
        $title = 'Log Register';
        $subTitle = ' ONT Baru';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $load['data'] = $susunData;

        return view('pages/olt/log', $load);
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

    public function getNewIp($profile)
    {
        $client = new Client([
            'host' => '202.169.224.19',
            'user' => 'faris123',
            'pass' => 'faris123',
            'port' => 9778,
        ]);


        $status = false;
        $data = [];
        $query =
            (new Query('/ppp/secret/print'))
            ->where('profile', $profile);

        $response = $client->query($query)->read();
        $ipList = [];
        foreach ($response as $key => $value) {
            $ipList[] = $value['remote-address'];
        }
        //print_r($ipList);
        foreach ($ipList as $key => $value) $susunIp[$key] = ip2long($value);
        sort($susunIp);
        foreach ($susunIp as $key => $value) $susunIp[$key] = long2ip($value);

        $lastip = "";

        foreach ($susunIp as $key => $value) {
            if (!array_key_last($susunIp)) {
                $nowIp = explode('.', $value);
                $nexIp = explode('.', $susunIp[$key + 1]);

                if ($nowIp[2] != $nexIp[2]) {

                    if ($nowIp[3] < 255) {
                        $lastip = $value;
                        break;
                    }
                }
            } else {
                $lastipIndex = $key;
                $lastip = $value;
            }
        }



        $lastip = explode('.', $lastip);
        $indexIp3 = 1;
        $indexIp2 = $lastip[2];
        $lastipIndex = $lastip[3];
        if ($lastip[3] < 255) {
            $indexIp3 = $lastip[3] + 1;
        } else {
            $indexIp2 = $lastip[2] + 1;
        }

        $newIp = $lastip[0] . '.' . $lastip[1] . '.' . $indexIp2 . '.' . $indexIp3;



        $load['new_ip'] = $newIp;

        $ip5after = [];
        for ($i = $lastipIndex + 2; $i < $lastipIndex + 7; $i++) {
            $ips = $lastip[0] . '.' . $lastip[1] . '.' . $indexIp2 . '.' . $i;
            if (!in_array($ips, $susunIp)) {
                $ip5after[] =  $ips;
            }
        }
        $load['ip_terdekat'] = $ip5after;

        return $load;
    }
}
