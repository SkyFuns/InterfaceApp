<?php

namespace app\frontend\controller;

use think\Loader;
use request\Curl;
use think\Cookie;
use think\Session;

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
            'vin' => $_POST['vin'],
            'license_no' => $_POST['license_no']
        ));

        $result = json_decode($app->response,true);

        $renewal = [];
        if ($result['code'] == 0 && !empty($result['data']))
        {
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
        $renewal = Session::get('renewal');

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


        $this->assign('renewal',$renewal);
        return $this->fetch("offer");
    }

    public function calculate()
    {
        return $this->fetch("calculate");
    }

    public function offerRecord()
    {
        return $this->fetch("offer_record");
    }

    /**
     * vehicle   查询车型
     * @dateTime 2018-04-16
     * @license  license
     * @return   json     返回车辆信息
     */
    public function vehicle()
    {
        $data = [
            'insurance' => 'SICHUAN_KHYXSC_CICP',
            'model' => $_POST['model'],
            'vin' => $_POST['vin'],
        ];

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

        $arr['auto']['MODEL_CODE'] = $car_info[0];
        $arr['auto']['BUYING_PRICE'] = $car_info[4];
        $discout_price = $this->discoutPirce($_POST['auto'],$arr['business']['BUSINESS_START_TIME']);
        $arr['auto']['DISCOUNT_PRICE'] = $discout_price['content'];//$car_info[0];
        $arr['auto']['SEATS'] = $car_info[5];//$car_info[0];
        $arr['auto']['MODEL_ALIAS'] = $car_info[2];
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
        Session::set('carInfo',trim($_POST['auto']['CAR_DATA_CLIENT']));
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
        $insurance = Session::get('premium_parems');
        $this->assign('insurance',$insurance);
        return $this->fetch("accurate");
    }

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

        $app = new Curl();
        $result = $app->post('quickPremium',$data);

        if(!$result)
        {
           return ret(1,'请求失败',$app->errorMsgs());
        }
        if(empty($result['data']['MVTALCI']) && empty($result['data']['BUSINESS']))
        {
            return ret(1,'请求失败',$result['data']['MESSAGE']);
        }

        Session::delete('premium');
        Session::set('premium',$result);
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

        if(!empty($_POST))
        {
            $car_data = explode('/', $_POST['auto']['CAR_DATA_CLIENT']);
            $data['purchase_price'] = intval($car_data[3]);
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
