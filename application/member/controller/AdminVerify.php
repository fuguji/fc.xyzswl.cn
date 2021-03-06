<?php

namespace app\member\controller;

use app\common\controller\AdminBase;
use app\common\model\Models;
use app\common\model\Users;
use think\Db;

class AdminVerify extends AdminBase
{
    public function index()
    {
        global $_W, $_GPC;
        $model = set_model("users_verify");
        /** @var Models $model_info */
        $model_info = $model->model_info;
        //fields
        $this->view->field_list = $model_info->get_admin_column_fields();
        //model_info
        $this->view->model_info = $model_info;
        $where = [];
        //data list
        $where['site_id'] = $this->site['id'];
        $this->view->mapping = $this->mapping;
        $lists = $model->where($where)->order("create_at desc")->paginate();
        $this->view->lists = $lists;
        return $this->view->fetch();
    }
    public function edit($user_id)
    {
        global $_GPC;
        $model = set_model("users_verify");
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $id = (int)$user_id;
        $where = [$model_info['id_key'] => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();
        if ($this->isPost() && $model_info) {
            if (isset($_GPC['_form_manual'])) {
                //手动处理数据
                $data = $_GPC;
            } else {
                //自动获取data分组数据
                $data = input('post.data/a');//get the base info
            }
            // todo  process data input
            $model_info->edit_content($data, $where);
            $this->zbn_msg("ok");
        } else {
            //todo auth
            $this->view->list = $model_info->get_user_publish_fields($detail);;
            $this->view->model_info = $model_info;
            return $this->view->fetch();
        }
    }
    public function pass($user_id, $type)
    {
        global $_GPC;
        $user = Users::get($user_id);
        $model = set_model("users_verify");
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $id = (int)$user_id;
        //$model_info = Models::get(['id' => $this->zwt_department]);
        $where = [$model_info['id_key'] => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();
        $update = [];
        if ($detail) {
            switch ($type) {
                case "personal":
                    $update['personal_verify'] = 99;
                    break;
                case "company":
                    $update['company_verify'] = 99;
                    break;
            }
            $res = $model_info->edit_content($update, $where);
            if ($res['code'] == 1) {
                $user->is_verify= 1;$user->save();
            }
            return $res;
            $this->zbn_msg("审核成功", 1);
        } else {
            $this->zbn_msg("对不起，用不户存在", 2);
        }
    }
    public function cancle($user_id)
    {
        global $_GPC;
        $user = Users::get($user_id);
        $model = set_model("users_verify");
        /** @var Models $model_info */
        $model_info = $model->model_info;
        $id = (int)$user_id;
        //$model_info = Models::get(['id' => $this->zwt_department]);
        $where = [$model_info['id_key'] => $id];
        $detail = Db::name($model_info['table_name'])->where($where)->find();
        $update = [];
        if ($detail) {
            $update['personal_verify'] = 0;
            $update['company_verify'] = 0;
            $res = $model_info->edit_content($update, $where);
            if ($res['code'] == 1) {
                $user->is_verify= 0;
                $user->save();
                $this->zbn_msg("操作成功", 1);
            } else {
                $this->zbn_msg("操作失败，" . $res['msg'], 1);
            }
        } else {
            $this->zbn_msg("对不起，用不户存在", 2);
        }
    }
}