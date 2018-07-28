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
namespace app\advertise\controller;

use app\advertise\model\Advertise;
use app\common\controller\AdminBase;
use app\common\model\Models;
use think\Db;

class AdminAdvertise extends AdminBase
{
    public $adgroup_model_id = "adgroup", $advertise_model_id = "advertise";

    /**
     * @param $group_id
     * @return mixed
     * @internal param string $model_id
     * @internal param string $node_type
     */
    public function add($group_id)
    {
        global $_W;
        $model = set_model($this->advertise_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        if ($this->isPost() && $model_info) {
            $base_info = input('post.data/a');//get the base info

            if (Models::field_exits('site_id', $model_info['id'])) {
                $base_info['site_id'] = $_W['site']['id'];
            }
            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {

            $this->view->assign('list', $model_info->get_admin_publish_fields(['group_id'=>$group_id]));
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit($id)
    {
        $id = (int)$id;
        $model = set_model($this->advertise_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;

        $where = ['id' => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();

        $this->check_admin_auth($detail);
        if ($this->isPost() && $model_info) {
            $data = input('param.data/a');
            // todo  process data input
            Db::name($model_info['table_name'])->where($where)->update($data);
            $this->zbn_msg("ok");
        } else {
            $this->view->list = $model_info->get_admin_publish_fields($detail);
            //assign('list', $new_field_list);
            $this->view->assign('model_info', $model_info);
            return $this->view->fetch();
        }
    }

    public function delete($id)
    {
        $id = (int)$id;
        set_model($this->advertise_model_id)->where(['id' => $id])->delete();
        return ['code' => 0, 'msg' => '删除成功'];
    }

    /**
     * @param $group_id
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($group_id)
    {
        $model = set_model($this->advertise_model_id);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        //data list 如果不是超级管理员 并且数据是区分站群的
        if (!$this->super_power && Models::field_exits('site_id', $this->advertise_model_id)) {
            $where['site_id'] = $this->site['id'];
        }
        //fields
        $this->view->field_list = $model_info->get_admin_column_fields();// assign('field_list', $new_field_list);
        //model_info
        $this->view->assign('model_info', $model_info);
        $where['group_id'] = $group_id;
        //data list
        $lists = Db::name($model_info['table_name'])->where($where)->paginate();
        $this->view->lists = $lists;
        return $this->view->fetch();
    }
}