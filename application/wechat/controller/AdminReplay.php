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

namespace app\wechat\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\model\UserMenu;
use app\common\util\Tree2;
use app\wechat\util\MhcmsWechatAccountBase;
use think\Db;

class AdminReplay extends AdminBase
{
    private $sites_wechat_keyword = "sites_wechat_keyword";

    public function _initialize()
    {
        global $_W;
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->check_admin_auth($_W['account']);
    }

    public function index()
    {
        global $_W;
        $model = set_model($this->sites_wechat_keyword);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where = [];
        $where['sites_wechat_id'] = $_W['account']['id'];

        $this->view->lists = $model->where($where)->order("id desc")->paginate();
        $this->view->field_list = $model_info->get_admin_column_fields();
        $this->view->content_model_id = $this->sites_wechat_keyword;
        return $this->view->fetch();
    }

    public function change_config()
    {
        return $this->view->fetch();
    }

    /**
     * @return string|void
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function add()
    {
        global $_W, $_GPC;
        $model = set_model($this->sites_wechat_keyword);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
            $base_info['sites_wechat_id'] = $_W['account']['id'];

            $res = $model_info->add_content($base_info);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            $this->view->field_list = $model_info->get_admin_publish_fields([], ['default', 'reply_content']);
            return $this->view->fetch();
        }
    }


    /**
     * @param $id
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function set_default($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->sites_wechat_keyword);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['sites_wechat_id'] = $_W['account']['id'];
        $detail = $model->where($where)->find(); if($detail['module']  && $detail['module'] != "wechat"){
        return $this->error("此为模块托管规则 请勿修改");
    }
        if ($detail) {
            $update = [];
            $update['default'] = 0;

            $new_where = [];
            $new_where['id'] = ["NEQ", $id];
            $new_where['sites_wechat_id'] = $_W['account']['id'];
            $model->where($new_where)->update($update);

            //
            $update['default'] = 1;
            $new_where['id'] = ["EQ", $id];
            $model->where($new_where)->update($update);
        }


        return [
            'code' => 1,
            'msg' => '操作完成'
        ];
    }

    public function edit($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->sites_wechat_keyword);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['sites_wechat_id'] = $_W['account']['id'];
        $detail = $model->where($where)->find();

        if($detail['module']  && $detail['module'] != "wechat"){
            return $this->error("此为模块托管规则 请勿修改");
        }

        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
            $base_info['sites_wechat_id'] = $_W['account']['id'];

            $res = $model_info->edit_content($base_info, $where);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }

        } else {
            $this->view->field_list = $model_info->get_admin_publish_fields($detail, ['reply_content', 'reply_type']);
            return $this->view->fetch();
        }

    }


    public function delete($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->sites_wechat_keyword);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['sites_wechat_id'] = $_W['account']['id'];
        $detail = $model->where($where)->find();

        if ($detail) {
            $model->where($where)->delete();
        }

        return [
            'code' => 1,
            'msg' => '操作完成'
        ];
    }

    /**
     * @param $id
     * @return string|void
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function set_reply($id)
    {
        global $_W, $_GPC;
        $model = set_model($this->sites_wechat_keyword);
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $where['id'] = $id;
        $where['sites_wechat_id'] = $_W['account']['id'];
        $detail = $model->where($where)->find(); if($detail['module']  && $detail['module'] != "wechat"){
        return $this->error("此为模块托管规则 请勿修改");
    }
        if ($this->isPost()) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $base_info = $_GPC;
            } else {
                //自动获取data分组数据
                $base_info = input('post.data/a');//get the base info
            }
            $base_info['sites_wechat_id'] = $_W['account']['id'];
            $res = $model_info->edit_content($base_info, $where);
            if ($res['code'] == 1) {
                return $this->zbn_msg($res['msg'], 1, 'true', 1000, "''", "'reload_page()'");
            } else {
                return $this->zbn_msg($res['msg'], 2);
            }
        } else {
            $this->view->field_list = $model_info->get_admin_publish_fields($detail, [], [ 'reply_type']);
            $this->view->detail = $detail;
            return $this->view->fetch();
        }
    }
}