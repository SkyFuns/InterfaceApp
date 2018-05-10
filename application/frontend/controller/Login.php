<?php
namespace app\frontend\controller;

use app\frontend\model\User;
use think\Cookie;
use think\Loader;
use think\Session;
use request\Curl;

class Login extends FrontendBase
{
    public function login()
    {
        return $this->fetch("login");
    }

    public function callback()
    {
        return $this->fetch("callback");
    }


    public function qqLogin()
    {

        if(isset($_POST['parms']['openid']) && !empty($_POST['parms']['openid']))
        {

            $data['openid'] = $_POST['parms']['openid'];
            //$data['accessToken'] = $_POST['parms']['accessToken'];
            $user = db('user')->where($data)->find();

            if(empty($user))
            {
                Session::set('qq_openid',$data['openid']);
                return ret(1,'请求失败','');
            }
            else
            {
                Session::set('qq_openid',$user['openid']);
                return ret(0,'请求成功',$user);
            }
            
        }
    }

    public function information()
    {
        $qq_openid = Session::get('qq_openid');
        if(empty($qq_openid))
        {
            return ret(1,'请求失败','请重新登录');
        }
        return $this->fetch("information");
    }



    protected function setPasswordAttr($value)
    {
        return '###' . md5($value . DATA_ENCRYPT_KEY);
    }

    public function index()
    {
        $data = [
            'username' => $_POST['username'],
            'password' => $_POST['password'],
            'authorizationcode' => $_POST['authorizationcode'],
        ];

        $validate = Loader::validate('User');

        if (!$validate->check($data)) {

            return ret(1, $validate->getError());
        }

        $data['authorizationcode'] = $this->setPasswordAttr($data['authorizationcode']);
        $data['password'] = $this->setPasswordAttr($data['password']);

        $user = db('user')->where($data)->find();
        if (!empty($user)) {

            Session::set('username',$data['username']);
            return ret(0, '登录成功');
        } else {
            return ret(1, '用户名，密码或授权码输入错误');
        }
    }


    public function register()
    {
        $validate = Loader::validate('User');
        $password = $this->setPasswordAttr($_POST['password']);
        if($_POST['authorizationcode'] != 9999)
        {
            return ret(1, '授权码不符合规则');
        }
        $authorizationcode = $this->setPasswordAttr($_POST['authorizationcode']);
        $data = [
            'username' => $_POST['username'],
            'password' => $password,
            'authorizationcode' => $authorizationcode,
        ];

        if (!$validate->check($data)) {

            return ret(1, $validate->getError());
        }

        $user = db('user')->where('username',$data['username'])->find();
        if(!empty($user))
        {
            return ret(1, '注册失败，用户名已经存在');
        }
        $ap = db('user')->insert($data);

        if (!empty($ap)) {
            return ret(0, '注册成功,请登录');
        } else {
            return ret(1, '注册失败，请排查用户名或密码是否符合规则');
        }
    }

}
