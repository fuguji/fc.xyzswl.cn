<?php
// +----------------------------------------------------------------------
// | 鸣鹤CMS [ New Better  ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://www.mhcms.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( 您必须获取授权才能进行使用 )
// +----------------------------------------------------------------------
// | Author: new better <1620298436@qq.com>
// +----------------------------------------------------------------------
namespace app\house\controller;

use app\common\controller\HomeBase;
use app\common\model\Hits;
use app\common\model\Models;
use app\core\util\ContentTag;
use think\Db;

class Esf extends HouseBase
{
    private $house_esf = "house_esf";

    public function index()
    {
        global $_W;
        $this->view->areas = ContentTag::model_tree('area', '', 'area_name');


        $this->view->loupan_type_options = ContentTag::load_options("house_esf", 'loupan_type');
        //$this->view->price_options = ContentTag::load_options("house_esf", 'price_qujian');
        $this->view->tags_options = ContentTag::load_options("house_esf", 'tags');
        $this->view->zhuangxiu_options = ContentTag::load_options("house_esf", 'zhuangxiu');

        return $this->view->fetch();
    }

    public function detail($id)
    {
        global $_W;
        $content_model_id = $this->house_esf;
        $model = set_model($content_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $detail = Models::get_item($id, $content_model_id);		//		$detail['mobile'] = '*********';//		$is_phone = Db::table('buy_phone')->where('user_id' , )->find();		
        $this->view->detail = $detail;
        $this->view->page_title = $detail['title'];
        Hits::hit($id ,$this->house_esf);
        $this->mapping = array_merge($this->mapping, $detail);
        $this->view->seo = $this->seo($this->mapping);
        $this->view->user_verify = set_model("users_verify")->where(['user_id'=>$detail['user_id']])->find();
        return $this->view->fetch();
    }
}