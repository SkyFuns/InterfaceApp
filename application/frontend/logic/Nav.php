<?php
/**
 * ============================================================================
 * 版权所有 2017-2077 tpframe工作室，并保留所有权利。
 * @link http://www.tpframe.com/
 * @copyright Copyright (c) 2017 TPFrame Software LLC
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！未经本公司授权您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 */
namespace app\frontend\logic;
use \tpfcore\util\Tree;
use \tpfcore\util\Data;
use \tpfcore\Core;
/**
 *  导航逻辑
 */
class Nav extends FrontendBase
{
	public function getNav(){
		$listNav=self::getObject(['cid'=>1,"display"=>1],"id,href,label,target");
		$nav_arr=[];
		foreach ($listNav as $key => $value) {
			$nav_arr[$key]['id']=$value['id'];
			$nav_arr[$key]['label']=$value['label'];
			$nav_arr[$key]['href']=$value['href'];
			$nav_arr[$key]['target']=$value['target'];
		}

		$categor_arr=[];
		$listCategory=Core::loadModel("Category",'','logic')->getCategory(['isnav'=>1]);
		foreach ($listCategory as $key => $value) {
			$categor_arr[$key]['id']=$value['id'];
			$categor_arr[$key]['label']=$value['title'];
			$categor_arr[$key]['href']=$value['url'];
			$categor_arr[$key]['target']="_self";
		}
		$navs=array_merge($nav_arr,$categor_arr);
		return $navs;
	}
}