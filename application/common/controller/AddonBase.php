<?php
// +----------------------------------------------------------------------
// | Author: yaoyihong <510974211@qq.com>
// +----------------------------------------------------------------------

namespace app\common\controller;

/**
 * 插件控制器基类
 */
class AddonBase extends ControllerBase
{
    /**
     * 插件基类构造方法
     */
    public function _initialize()
    {
        parent::_initialize();
    }
    
    /**
     * 插件模板渲染
     */
    public function addonTemplate($template_name = '',$params=[])
    {
        
        $class = get_class($this);

        $arr=explode("\\", $class);


        $cate_name = strtolower($arr[1]);
        
        $view_path = ADDON_DIR_NAME."/{$cate_name}/view/";
        
        $this->assign('static_path', '/' .ADDON_DIR_NAME . "/{$cate_name}/view/static");

        $this->assign('catename',$cate_name);
        
        $this->view->engine(['view_path' => $view_path]);
        
        echo $this->fetch($template_name,$params);
    }
    /*
        插件使用说明
    */
    public function doc(){
        echo "该插件开发者未完善使用文档";
    }
}
