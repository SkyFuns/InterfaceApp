<?php

namespace app\frontend\controller;

use think\Loader;
use request\Curl;
use think\Cookie;
use think\Session;
use think\Request;
use think\DB;

class Main extends Base
{

    public $item = [

        'TVDI',
        'TVDI_NDSI',
        'TTBLI',
        'TTBLI_NDSI',
        'TCPLI_DRIVER',
        'TCPLI_DRIVER_NDSI',
        'TCPLI_PASSENGER',
        'TCPLI_PASSENGER_NDSI',
        'TWCDMVI',
        'TWCDMVI_NDSI',
        'BSDI',
        'BSDI_NDSI',
        'BGAI',
        'BGAI_NDSI',
        'SLOI',
        'SLOI_NDSI',
        'VWTLI',
        'VWTLI_NDSI',
        'STSFS',

    ];

    public function index()
    {
        Session::delete('carInfo');
        Session::delete('premium_parems');
        Session::delete('premium');
        Session::delete('renewal');
        return $this->fetch("index");
    }

    public function renewal()
    {
        Session::delete('renewal');
        $app = new \Curl\Curl();
        $app->setOpt(CURLOPT_SSL_VERIFYPEER, FALSE);
        $app->post('https://zsidms.cdqidian.cn/zsidms/App.php?method=renewal', array(
            'account' => 'weixin',
            'pwd' => 'wx123456',
            'vin' => $_POST['auto']['vin'],
            'license_no' => $_POST['auto']['license_no']
        ));

        $result = json_decode($app->response,true);

        $renewal = [];
        if ($result['code'] == 0 && !empty($result['data']))
        {
            $renewal['bi_start_time'] = $result['data']['bi_start_time'];
            $renewal['ci_start_time'] = $result['data']['ci_start_time'];
            $renewal['carInfo'] =   $result['data']['carInfo'];
            $renewal['ownerInfo'] = $result['data']['ownerInfo'];
            $renewal['policyBI'] =  $result['data']['policyBI'];
            $renewal['policyCI'] =  $result['data']['policyCI'];
            Session::set('renewal',$renewal);

            return ret(0,'查询成功');
        }
        return ret(1,'查询失败',$result['message']);

    }

    public function add_driving()
    {
        return $this->fetch("add_driving");
    }

    public function recordSearch()
    {
        $username = Session::get('username');
        if(empty($username))
        {
            return ret(1,'请求失败','已超时,请重新登录');
        }
        $user['username'] = $username;
        $users = db('user')->where($user)->field('id')->find();
        $license_no = input('license_no');
        if(empty($license_no) && $license_no == "")
        {
            $parms['uid'] = $users['id'];
            $pages = input('pages');
            $result = db('Calculaterecord')->where($parms)->order('modify_time desc')->page($pages,7)->select();
        }
        else
        {
            $result = Db::query("select * from app_calculaterecord where uid = ".$users['id']." and renewal->'$.carInfo.license_no' like '%".$license_no."%'");
        }

        foreach ($result as $k => $v) {
                $result[$k]['create_time'] = date("Y-m-d",strtotime($v['create_time']));
                $result[$k]['carculat_parms'] = json_decode($v['carculat_parms'],true);
                $result[$k]['carcula_record'] = json_decode($v['carcula_record'],true);
                $result[$k]['renewal'] = json_decode($v['renewal'],true);
            }

        if(!empty($result))
        {
            return ret(0,'查询成功',$result);
        }
        return ret(1,'查询成功','没有查询到任何记录');
    }

    public function submit_driving()
    {
        Session::delete('renewal');
        $renewal['carInfo'] =   $_POST['carinfo'];
        Session::set('renewal',$renewal);
        return ret(0,'查询成功');
    }


    public function detail()
    {
        $renewal = Session::get('renewal');

        if(array_key_exists('insurance_company', $renewal['policyBI']) && !empty($renewal['policyBI']))
        {
            switch ($renewal['policyBI']['insurance_company']) {
                case 'PICC':
                    $renewal['policyBI']['insurance_company'] = '人保车险';
                    break;

                case 'CPIC':
                    $renewal['policyBI']['insurance_company'] = '太平洋车险';
                    break;

                case 'GPIC':
                    $renewal['policyBI']['insurance_company'] = '国寿财险';
                    break;

                case 'CICP':
                    $renewal['policyBI']['insurance_company'] = '中华联合';
                    break;

                case 'PAIC':
                    $renewal['policyBI']['insurance_company'] = '平安车险';
                    break;
            }
        }

        $this->assign('renewal',$renewal);
        return $this->fetch("detail");
    }

    public function offer()
    {
        $nots = input('nots');
        if(isset($nots) && !empty($nots))
        {
            $parms['id'] = $nots;
            $Calculaterecord = db('Calculaterecord')->where($parms)->find();
            if(!empty($Calculaterecord))
            {
                $renewal = json_decode($Calculaterecord['renewal'],true);
                $this->assign('nots',$nots);
            }
        }
        else
        {
            $renewal = Session::get('renewal');
        }

        $id_card = substr_replace($renewal['ownerInfo']['identify_no'],'*',0,8);
        if(isset($renewal['policyBI']) && !empty($renewal['policyBI']))
        {
            foreach($renewal['policyBI']['items'] as $key => $val)
            {
                if($val['code_name'] == 'TTBLI')
                {
                    switch ($val['amount']) {
                        case 50000:
                            $renewal['policyBI']['items'][$key]['amount'] = 5;
                            break;
                        case 100000:
                            $renewal['policyBI']['items'][$key]['amount'] = 10;
                            break;

                        case 150000:
                            $renewal['policyBI']['items'][$key]['amount'] = 15;
                            break;

                        case 200000:
                            $renewal['policyBI']['items'][$key]['amount'] = 20;
                            break;

                        case 300000:
                            $renewal['policyBI']['items'][$key]['amount'] = 30;
                            break;
                        case 500000:
                            $renewal['policyBI']['items'][$key]['amount'] = 50;
                            break;
                        case 1000000:
                            $renewal['policyBI']['items'][$key]['amount'] = 100;
                            break;
                        case 2000000:
                            $renewal['policyBI']['items'][$key]['amount'] = 200;
                            break;
                    }
                }
            }
        }


        if(isset($renewal['policyBI']) && !empty($renewal['policyBI']))
        {
            $renewal['policyBI']['end_date'] = date('Y-m-d',strtotime($renewal['policyBI']['end_date']));
            if(isset($renewal['policyBI']['items']) && !empty($renewal['policyBI']['items']))
            {
                foreach($renewal['policyBI']['items'] as $key =>$val)
                {
                    if($val['code_name'] == 'TVDI')
                    {
                        $tvdi = 1;
                        $this->assign('tvdi_premium',$val['premium']);
                        $this->assign('tvdi',$tvdi);
                    }
                    else if($val['code_name'] == 'TVDI_NDSI')
                    {
                        $tvdi_ndsi = 1;
                        $this->assign('tvdi_ndsi',$tvdi_ndsi);
                    }
                    else if($val['code_name'] == 'TTBLI')
                    {
                        $ttbli = 1;
                        $this->assign('ttbli_amount',$val['amount']);
                        $this->assign('ttbli',$ttbli);
                    }
                    else if($val['code_name'] == 'TTBLI_NDSI')
                    {
                        $ttbli_ndsi = 1;
                        $this->assign('ttbli_ndsi',$ttbli_ndsi);
                    }
                    else if($val['code_name'] == 'TCPLI_DRIVER')
                    {
                        $tcpli_driver = 1;
                        $this->assign('tcpli_driver_amount',$val['amount']);
                        $this->assign('tcpli_driver',$tcpli_driver);
                    }
                    else if($val['code_name'] == 'TCPLI_DRIVER_NDSI')
                    {
                        $tcpli_driver_ndsi = 1;
                        $this->assign('tcpli_driver_ndsi',$tcpli_driver_ndsi);
                    }
                    else if($val['code_name'] == 'TCPLI_PASSENGER')
                    {
                        $tcpli_passenger = 1;
                        $this->assign('tcpli_passenger_amount',$val['amount']);
                        $this->assign('tcpli_passenger',$tcpli_passenger);
                    }
                    else if($val['code_name'] == 'TCPLI_PASSENGER_NDSI')
                    {
                        $tcpli_passenger_ndsi = 1;
                        $this->assign('tcpli_passenger_ndsi',$tcpli_passenger_ndsi);
                    }
                    else if($val['code_name'] == 'TWCDMVI')
                    {
                        $twcdmvi = 1;
                        $this->assign('twcdmvi',$twcdmvi);
                    }
                    else if($val['code_name'] == 'TWCDMVI_NDSI')
                    {
                        $twcdmvi_ndsi = 1;
                        $this->assign('twcdmvi_ndsi',$twcdmvi_ndsi);
                    }
                    else if($val['code_name'] == 'BSDI')
                    {
                        $bsdi = 1;
                        $this->assign('bsdi_amount',$val['amount']);
                        $this->assign('bsdi',$bsdi);
                    }
                    else if($val['code_name'] == 'BSDI_NDSI')
                    {
                        $bsdi_ndsi = 1;
                        $this->assign('bsdi_ndsi',$bsdi_ndsi);
                    }
                    else if($val['code_name'] == 'BGAI')
                    {
                        $bgai = 1;
                        $this->assign('bgai_amount',$val['amount']);
                        $this->assign('bgai',$bgai);
                    }
                    else if($val['code_name'] == 'BGAI_NDSI')
                    {
                        $bgai_ndsi = 1;
                        $this->assign('bgai_ndsi',$bgai_ndsi);
                    }
                    else if($val['code_name'] == 'SLOI')
                    {
                        $sloi = 1;
                        $this->assign('sloi',$sloi);
                    }
                    else if($val['code_name'] == 'SLOI_NDSI')
                    {
                        $sloi_ndsi = 1;
                        $this->assign('sloi_ndsi',$sloi_ndsi);
                    }
                    else if($val['code_name'] == 'VWTLI')
                    {
                        $vwtli = 1;
                        $this->assign('vwtli',$vwtli);
                    }
                    else if($val['code_name'] == 'VWTLI_NDSI')
                    {
                        $vwtli_ndsi = 1;
                        $this->assign('vwtli_ndsi',$vwtli_ndsi);
                    }
                    else if($val['code_name'] == 'STSFS')
                    {
                        $stsfs = 1;
                        $this->assign('stsfs_amount',$val['amount']);
                        $this->assign('stsfs',$stsfs);
                    }
                    else if($val['code_name'] == 'MVLINFTPSI')
                    {
                        $mvlinftpsi = 1;
                        $this->assign('mvlinftpsi',$mvlinftpsi);
                    }
                    else if($val['code_name'] == 'RDCCI')
                    {
                        $rdcci = 1;
                        $this->assign('rdcci',$rdcci);
                    }
                }
            }
        }

        if(isset($renewal['policyCI']) && !empty($renewal['policyCI']))
        {
            $renewal['policyCI']['end_date'] = date('Y-m-d',strtotime($renewal['policyCI']['end_date']));
        }
        $this->assign('id_card',$id_card);

        $premium_parems = Session::get('premium_parems');
        if(isset($premium_parems) && !empty($premium_parems))
        {
            $this->assign('premium_parems',$premium_parems);
        }

        $this->assign('renewal',$renewal);
        return $this->fetch("offer");
    }

    public function calculate()
    {
        return $this->fetch("calculate");
    }

    public function offerRecord()
    {
        $username = Session::get('username');
        if(empty($username))
        {
            return ret(1,'请求失败','已超时,请重新登录');
        }
        $user['username'] = $username;
        $users = db('user')->where($user)->field('id')->find();
        $totle = 7;
        if(empty($_GET['pages']))
        {
            $pages = 1;
        }
        else
        {
            $pages = $_GET['pages'];

        }

        $parms['uid'] = $users['id'];
        $Calculaterecord = db('Calculaterecord')->where($parms)->order('modify_time desc')->page($pages,7)->select();

        if (Request::instance()->isAjax()){
            if(empty($Calculaterecord))
            {
                return ret(1,'请求失败','没有查询到任何记录');
            }

            foreach ($Calculaterecord as $k => $v) {
                $Calculaterecord[$k]['create_time'] = date("Y-m-d",strtotime($v['create_time']));
                $Calculaterecord[$k]['carculat_parms'] = json_decode($v['carculat_parms'],true);
                $Calculaterecord[$k]['carcula_record'] = json_decode($v['carcula_record'],true);
                $Calculaterecord[$k]['renewal'] = json_decode($v['renewal'],true);
            }
            return ret(0,'请求成功',$Calculaterecord);
        }
        if(empty($Calculaterecord))
        {
            return ret(0,'请求成功','没有查询到任何记录');
        }
        foreach ($Calculaterecord as $k => $v) {
            $Calculaterecord[$k]['create_time'] = date("Y-m-d",strtotime($v['create_time']));
            $Calculaterecord[$k]['carculat_parms'] = json_decode($v['carculat_parms'],true);
            $Calculaterecord[$k]['carcula_record'] = json_decode($v['carcula_record'],true);
            $Calculaterecord[$k]['renewal'] = json_decode($v['renewal'],true);
        }
        $this->assign('pages',$pages);
        $this->assign('result',$Calculaterecord);
        return $this->fetch("offer_record");
    }

    /**
     * vehicle   查询车型
     * @dateTime 2018-04-16
     * @license  license
     * @return   json     返回车辆信息
     */
    public function vehicle($parms = array())
    {

        if(!empty($parms))
        {
            $data = [
                'insurance' => 'SICHUAN_KHYXSC_CICP',
                'model' => trim(!isset($parms['model'])?"111":$parms['model']),
                'vin' => trim(!isset($parms['vin'])?"":$parms['vin']),
                'modelCode' => trim(!isset($parms['modelcode'])?"":$parms['modelcode']),
            ];
        }
        else
        {
            $data = [
                'insurance' => 'SICHUAN_KHYXSC_CICP',
                'model' => trim($_POST['model']),
                'vin' => trim($_POST['vin']),
            ];
        }


        $validate = Loader::validate('Purchase');

        if (!$validate->check($data)) {

            return ret(1, $validate->getError());
        }

        $app = new Curl();
        $result = $app->post('VehicleInfo',$data);

        if(!$result)
        {
            return ret(1,'请求失败',$app->errorMsgs());
        }
        return ret(0,'请求成功',$result['data']);

    }

    /**
     * preuim    计算保费
     * @dateTime 2018-04-18
     * @license  license
     * @return   [type]     [description]
     */
    public function preuim()
    {
        $arr = [];
        foreach($_POST as $key =>$val)
        {
            foreach($val as $k => $v)
            {
                $arr[$key][strtoupper($k)] = $v;
            }
        }

        foreach($arr['business']['BUSINESS_ITEMS'] as $z => $s)
        {
            if(in_array(strtoupper($z), $this->item) && $s != 0)
            {
                $arr['business']['BUSINESS_ITEMS'][] = strtoupper($z);

            }
            unset($arr['business']['BUSINESS_ITEMS'][$z]);
        }

        if(!array_key_exists('CAR_DATA', $_POST['auto']) || empty($_POST['auto']['CAR_DATA']))
        {
            return ret(1,'请求失败','车型信息不能为空,请重新查询');
        }

        $car_info = explode('/', $_POST['auto']['CAR_DATA']);

        $arr['auto']['MODEL'] = $car_info[1];
        $arr['auto']['MODEL_CODE'] = $car_info[0];
        $arr['auto']['BUYING_PRICE'] = $car_info[4];
        $discout_price = $this->discoutPirce($_POST['auto'],$arr['business']['BUSINESS_START_TIME']);
        $arr['auto']['DISCOUNT_PRICE'] = $discout_price['content'];
        $arr['auto']['SEATS'] = $car_info[5];;
        $arr['auto']['MODEL_ALIAS'] = $car_info[2];
        $arr['auto']['LICENSE_NO'] = trim($_POST['auto']['LICENSE_NO']);
        $arr['auto']['VIN_NO'] = trim($_POST['auto']['VIN_NO']);

        $arr['ownerInfo']['owner'] = $_POST['auto']['OWNER'];
        $arr['ownerInfo']['identify_no'] = $_POST['auto']['IDENTIFY_NO'];
        $data['vehicle'] = $arr['auto'];
        if(trim($arr['mvtalci']['MVTALCI_START_TIME']) == "")
        {
            unset($arr['mvtalci']);
        }
        else
        {
            $time = $this->date_s(trim($arr['mvtalci']['MVTALCI_START_TIME']));
            $arr['mvtalci']['MVTALCI_START_TIME'] = $time['start_time'];
            $arr['mvtalci']['MVTALCI_END_TIME'] = $time['end_time'];
            $data['compulsory'] = $arr['mvtalci'];
        }

        if(trim($arr['business']['BUSINESS_START_TIME']) == "")
        {
            unset($arr['business']);
        }
        else
        {
            if(empty($arr['business']['BUSINESS_ITEMS']))
            {
                return ret(1,'请求失败','若投保商业险，投保险种不能为空');
            }

            $time = $this->date_s(trim($arr['business']['BUSINESS_START_TIME']));
            $arr['business']['BUSINESS_START_TIME'] = $time['start_time'];
            $arr['business']['BUSINESS_END_TIME'] = $time['end_time'];
            $data['business'] = $arr['business'];
        }

        Session::delete('carInfo');
        Session::delete('premium_parems');
        #Session::set('carInfo',trim($_POST['auto']['CAR_DATA_CLIENT']));
        Session::set('premium_parems',$data);

        return ret(0,'请求成功');

    }

    private function date_s($start_time)
    {

        $date_y = date("Y");

        if($start_time == "")
        {
            return false;
        }

        $starttime = date("Y",strtotime($start_time));

        $endtime = $starttime;


        if($starttime < $date_y)
        {
            $year = $date_y - $starttime;
            $starttime = $starttime + $year;
            $endtime = $endtime + $year + 1;

            $starttime_m = date("m",strtotime($start_time));
            $starttime_d = date("d",strtotime($start_time)) -1;
            $return['start_time'] = ($endtime -1) ."-".$starttime_m."-".($starttime_d + 1);
            $return['end_time'] = $endtime."-".$starttime_m."-".$starttime_d;

        }else if($starttime == $date_y)
        {
            $starttime_m = date("m",strtotime($start_time));
            $starttime_d = date("d",strtotime($start_time)) -1;
            $return['start_time'] = ($endtime) ."-".$starttime_m."-".($starttime_d + 1);
            $return['end_time'] = ($endtime + 1)."-".$starttime_m."-".$starttime_d;
        }



        return $return;
    }

    public function accurate()
    {

        if(!empty($_POST['id']))
        {
            $arr['id'] = $_POST['id'];
            $Calculaterecord = db('Calculaterecord')->where($arr)->find();

            if(!empty($Calculaterecord))
            {
                Session::set('premium_parems',json_decode($Calculaterecord['carculat_parms'],true));
                Session::set('premium',json_decode($Calculaterecord['carcula_record'],true));
                return ret(0,'请求成功');
            }
            return ret(1,'请求失败','登录超时,请重新登录');
        }

        $nots = input('nots');
        if(isset($nots) && !empty($nots))
        {
            $parms['id'] = $nots;
            $Calculaterecord = db('Calculaterecord')->where($parms)->find();
            if(!empty($Calculaterecord))
            {
                $trackparms['cid'] = $Calculaterecord['id'];
                $trackrecord = db('Trackrecord')->where($trackparms)->select();

                $this->assign('trackrecord',$trackrecord);
                $this->assign('nots',$nots);
                $this->assign('premium',json_decode($Calculaterecord['carcula_record'],true));
                $this->assign('insurance',json_decode($Calculaterecord['carculat_parms'],true));
            }
        }
        else
        {
            $this->assign('premium',Session::get('premium'));
            $this->assign('insurance',Session::get('premium_parems'));
        }
        return $this->fetch("accurate");
    }
    /**
     * computationalCost     请求算价
     * @dateTime 2018-05-05
     * @license  license
     * @return   [type]     [description]
     */
    public function computationalCost()
    {

        $insurance = Session::get('premium_parems');

        if(empty($insurance))
        {
            return ret(1,'请求失败','请重新提交险种信息');
        }

        $data = [
            'insurance' => 'SICHUAN_KHYXSC_CICP',
            'vehicle' => $insurance['vehicle'],
        ];

        if(isset($insurance['compulsory']) && !empty($insurance['compulsory']))
        {
            $data['compulsory'] = $insurance['compulsory'];
        }
        if(isset($insurance['business']) && !empty($insurance['business']))
        {
            $data['business'] = $insurance['business'];
        }

        $curl = new Curl();
        $result = $curl->post('quickPremium',$data);

        if(isset($result['data']['MESSAGE']) && !empty($result['data']['MESSAGE']) && strstr($result['data']['MESSAGE'],'行业车型编码'))
        {

            $jsonmsg = explode("\n", $result['data']['MESSAGE']);
            $ss = explode('行业车型编码：', $jsonmsg[7]);
            if(isset($ss[1]) && !empty($ss[1]))
            {
                $pas['modelcode'] = $ss[1];
                $vehicle = $this->vehicle($pas);
                if(!empty($vehicle['content']) && $vehicle['content']['totalPage'] > 0)
                {

                    foreach($vehicle['content']['rows'] as $key => $val)
                    {
                        $insurance['vehicle']['CAR_DATA'] = $val['vehicleId']."/".$val['vehicleName']."/".$val['vehicleAlias']."/".$val['vehicleDisplacement']."/".$val['vehiclePrice']."/".$val['vehicleSeat']."/".$val['vehicleModelcode'];
                        $insurance['vehicle']['CAR_DATA_CLIENT'] = $val['vehicleName']."/".$val['vehicleAlias']."/".$val['vehicleDisplacement']."/".$val['vehiclePrice'];
                        $insurance['vehicle']['MODEL_CODE'] = $val['vehicleId'];
                        $insurance['vehicle']['BUYING_PRICE'] = $val['vehiclePrice'];

                    }
                        $discout_price['CAR_DATA_CLIENT'] = $insurance['vehicle']['CAR_DATA_CLIENT'];
                        $discout_price['ENROLL_DATE'] = $insurance['vehicle']['ENROLL_DATE'];
                        $discout_prices = $this->discoutPirce($discout_price,$insurance['business']['BUSINESS_START_TIME']);
                        $insurance['vehicle']['DISCOUNT_PRICE'] = $discout_prices;
                        Session::set('premium_parems',$insurance);
                        return ret(1,'请求失败','已自动过滤成正确车型,请返回险种页面重新提交报价');
                }
            }
        }
        else if(isset($result['data']['MESSAGE']) && strstr($result['data']['MESSAGE'],'终保'))
        {
           return ret(1,'请求失败',$result['data']['MESSAGE']);
        }
        else if(isset($result['data']['MVTALCI']) && empty($result['data']['MVTALCI']) && isset($result['data']['BUSINESS']) && empty($result['data']['BUSINESS']))
        {
            return ret(1,'请求失败',$result['data']['MESSAGE']);
        }
        else if(isset($result['data']['MESSAGE']) && !empty($result['data']['MESSAGE']) && $result['data']['MESSAGE'] != "交易成功")
        {
            return ret(1,'请求失败',$result['data']['MESSAGE']);
        }



        Session::delete('premium');
        Session::set('premium',$result);

        $username = Session::get('username');
        if(empty($username))
        {
            return ret(1,'请求失败','已超时,请重新登录');
        }

        $user['username'] = $username;
        $users = db('user')->where($user)->field('id')->find();

        if(empty($users))
        {
            return ret(1,'请求失败','已超时,请重新登录');
        }

        $parms['uid'] = $users['id'];
        $parms['renewal'] = json_encode(Session::get('renewal'));
        $parms['carculat_parms'] = json_encode($insurance);
        $parms['carcula_record']  =  json_encode($result);

        $Calculaterecord = model('Calculaterecord');
        $Calculaterecord->data($parms);
        $Calculaterecord->save();
        if(empty($Calculaterecord->id))
        {
            return ret(1,'请求失败','请核实数据是否规范');
        }

        $result['data']['CALCULA_ID'] = $Calculaterecord->id;
        return ret(0,'请求成功',$result);
    }

    /**
     * accurate_details    报价详情
     * @dateTime 2018-04-24
     * @license  license
     * @return   [type]     [description]
     */
    public function accurate_details()
    {

        $premium = Session::get('premium');
        $carInfo = Session::get('premium_parems');
        if(isset($premium['data']['BUSINESS']) && !empty($premium['data']['BUSINESS']) && isset($premium['data']['MVTALCI']) && !empty($premium['data']['MVTALCI']))
        {
            $premiums = $premium['data']['BUSINESS']['BUSINESS_PREMIUM'] + $premium['data']['MVTALCI']['MVTALCI_PREMIUM'];
        }

        if(!isset($premium['data']['BUSINESS']) && empty($premium['data']['BUSINESS']) && isset($premium['data']['MVTALCI']) && !empty($premium['data']['MVTALCI']))
        {
            $premiums =  $premium['data']['MVTALCI']['MVTALCI_PREMIUM'];
        }

        if(isset($premium['data']['BUSINESS']) && !empty($premium['data']['BUSINESS']) && !isset($premium['data']['MVTALCI']) && empty($premium['data']['MVTALCI']))
        {
            $premiums = $premium['data']['BUSINESS']['BUSINESS_PREMIUM'];
        }
        $count_ndsi = 0;
        foreach($premium['data']['BUSINESS']['BUSINESS_ITEMS'] as $k => $v)
        {
            switch ($k) {
                case 'TVDI':
                    $premium['data']['BUSINESS']['BUSINESS_ITEMS'][$k]['TITLE'] = "机动车损失保险";
                    break;
                case 'TTBLI':
                    $premium['data']['BUSINESS']['BUSINESS_ITEMS'][$k]['TITLE'] = "第三方责任险";
                    break;
                case 'TCPLI_DRIVER':
                    $premium['data']['BUSINESS']['BUSINESS_ITEMS'][$k]['TITLE'] = "车上人员责任险(司机)";
                    break;
                case 'TCPLI_PASSENGER':
                    $premium['data']['BUSINESS']['BUSINESS_ITEMS'][$k]['TITLE'] = "车上人员责任险(乘客)";
                    break;
                case 'TWCDMVI':
                    $premium['data']['BUSINESS']['BUSINESS_ITEMS'][$k]['TITLE'] = "盗抢险";
                    break;
                case 'BSDI':
                    $premium['data']['BUSINESS']['BUSINESS_ITEMS'][$k]['TITLE'] = "车身划痕险";
                    break;
                case 'SLOI':
                    $premium['data']['BUSINESS']['BUSINESS_ITEMS'][$k]['TITLE'] = "自燃损失险";
                    break;
                case 'BGAI':
                    $premium['data']['BUSINESS']['BUSINESS_ITEMS'][$k]['TITLE'] = "玻璃单独破碎险";
                    break;
                case 'VWTLI':
                    $premium['data']['BUSINESS']['BUSINESS_ITEMS'][$k]['TITLE'] = "发动机涉水损失险";
                    break;
                case 'STSFS':
                    $premium['data']['BUSINESS']['BUSINESS_ITEMS'][$k]['TITLE'] = "指定专修厂";
                    break;
                case 'MVLINFTPSI':
                    $premium['data']['BUSINESS']['BUSINESS_ITEMS'][$k]['TITLE'] = "第三方特约险";
                    break;
            }

            if(strstr($k,'_NDSI'))
            {
                $count_ndsi += $v['PREMIUM'];
                unset($premium['data']['BUSINESS']['BUSINESS_ITEMS'][$k]);
            }
        }
        $nots = input('nots');
        if(isset($nots) && !empty($nots))
        {
            $this->assign('nots',$nots);
        }
        else
        {
            $this->assign('nots',"");
        }
        $this->assign('count_ndsi',$count_ndsi);
        $this->assign('premiums',$premiums);
        $this->assign('carInfo',$carInfo);
        $this->assign('preuim',$premium);
        $this->assign('items',$premium['data']['BUSINESS']['BUSINESS_ITEMS']);
        return $this->fetch("accurate_details");
    }

    /**
     * discoutPirce 计算车损折扣保保额
     * @dateTime 2018-04-24
     * @license  license
     * @return   [type]     [description]
     */
    public function discoutPirce($info = array(),$starttime = "")
    {
        $app = new Curl();
        if(isset($_POST['auto']) && !empty($_POST['auto']))
        {
            $car_data = explode('/', $_POST['auto']['CAR_DATA_CLIENT']);
            $data['purchase_price'] = intval(trim(end($car_data)));
            $data['enroll_date'] = $_POST['auto']['ENROLL_DATE'];
            $data['business_start_date'] = trim($_POST['business']['business_start_time']);
            $data['insurance'] = "SICHUAN_KHYXSC_CICP";

            $result = $app->post('Depreciation',$data);
            if(!$result)
            {
                return ret(1,'请求失败','请重新查询购置价');
                die();
            }
            return ret(0,'请求成功',$result['data']);
        }
        $car_data = explode('/', $info['CAR_DATA_CLIENT']);
        $data['purchase_price'] = intval($car_data[3]);
        $data['enroll_date'] = $info['ENROLL_DATE'];
        $data['business_start_date'] = $starttime;
        $data['insurance'] = "SICHUAN_KHYXSC_CICP";

        $result = $app->post('Depreciation',$data);
        if(!$result)
        {
            echo $app->errorMsgs();
            die();
        }
        return $result['data'];

    }


}
