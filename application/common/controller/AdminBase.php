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
namespace app\common\controller;

use app\common\controller\Base;
use app\common\model\UserRoles;
use app\common\model\Users;
use think\Db;
use think\Session;

/**
 * Class AdminBase
 * @package app\common\controller
 *
 * @property Users $user
 */

class AdminBase extends Base
{
    public $admin_id, $admin_role_id, $admin_user_name, $current_admin_role, $current_admin, $current_menu, $user;
    /** @var MhcmsView $view */
    public $view ;
    public $model;
    /** @var array $admin_info */
    public $admin_info;

    public function _initialize()
    {
        global $_W;
        parent::_initialize();
        if(!defined("IN_MHCMS_ADMIN")){
            define("IN_ADMIN", 1);define("IN_MHCMS_ADMIN", 1);
        }

        $_W['admin'] = $this->current_admin = $this->user = check_admin();
        if (!$this->user) $this->error("未授权的访问 ，因为会话已过期 ", "admin/passport/login");
        /**
         * 超级管理员
         * 两种非正常授权方式
         */
        if ($this->user->id == 1) {
            $this->view->super_power = $_W['super_power'] = $this->super_power = 1;
            $this->current_admin_role = UserRoles::get(['id' => 1]);
        }
        /**
         * 分站超级管理员
         */

        if ($this->site['user_id'] == $this->user->id) {
            $this->view->sub_super = $_W['sub_super'] = $this->sub_super = 1;
            $this->current_admin_role = UserRoles::get(['id' => 3]);
            $this->admin_info = [];
            $this->admin_info['role_id'] = 3;
            $this->admin_info['role_id'] = $this->current_admin_role['id'];
            $this->admin_info['site_id'] = $this->site_id;
        }

        /**
         * make sure the user is ok
         * normal way to auth
         */
        if (!$this->sub_super && !$this->super_power) {
            $this->admin_info = set_model('admin')->where(['user_id' => $this->user->id, 'site_id' => $this->site['id']])->find();
            if(!$this->admin_info){
                die("Auth Error");
            }else{
                $this->current_admin_role = UserRoles::get(['id' => $this->admin_info['role_id']]);
            }
        }

        /**
         * 保存admin info
         */
        if(isset($this->admin_info)){
            $_W['admin_info'] = $this->admin_info;
        }


        $this->admin_id = Session::get('admin_id');
        $this->admin_role_id = $this->current_admin_role['id'];
        $this->admin_user_name = Session::get('admin_user_name');


        /**
         * Admin Menu Process
         */
        $user_menu = $this->current_menu;
        $this->view->assign("admin_menu", $this->current_menu);

        if (!$this->current_menu && ROUTE_M!=='admin') {

        }
        if (!$this->super_power) {
            if ($this->site['id'] != $this->admin_info['site_id']) {
                $this->error("越权的访问！！ ", "passport/login");
            }

            if ($this->current_admin['status']!=99 || !$this->current_admin_role['status']) {
                $this->error("this door is not open for you , please wait or call the administrator！", 'admin/passport/logout');
            }
            //非超级管理员 一定要验证菜单
            if (ROUTE_C != "admin_service" && ROUTE_C != "service" && ROUTE_C != "index" && strpos(ROUTE_A, "public_") !== 0) {
                $map = [];
                $map['user_menu_id'] = $this->current_menu['id'];
                $map['user_role_id'] = $_W['admin_info']['role_id'];
                $user_menu_access = Db::name('user_menu_access')->where($map)->find();
                if (!$user_menu_access) {
                    $this->error("未授权的访问 3");
                }
            }
        }


        $this->view->assign("admin", $this->current_admin);
        $this->view->assign("admin_role", $this->current_admin_role);
        $this->view->assign("admin_base_layout", config('admin_base_layout'));
    }

    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function update_field()
    {
        $data[input('param.field')] = input('param.field_value');
        if ($this->admin_id != 1) {
            $allow_keys = ['listorder'];
            $allow = 0;
            foreach ($allow_keys as $key) {
                if (strpos(input('param.field'), $key) !== false) {
                    $allow = 1;
                    break;
                }
            }
        } else {
            $allow = $this->admin_id;
        }
        if ($allow == 1) {
            $where[input('param.pk')] = input('param.pk_value');
            $model = set_model(input('param.model'));
            if ($model->where($where)->update($data)) {
                $rep['msg'] = "success";
                $rep["code"] = "1";
            } else {
                $rep['msg'] = "failed";
                $rep["code"] = "2";
            }

        } else {
            $rep['msg'] = "failed , author failed";
            $rep["code"] = "2";
        }
        return $rep;
    }

    public function check_admin_auth($data)
    {
        global $_W;
        if (!$this->super_power) {
            if ($_W['admin_info']['site_id'] == $data['site_id'] || $_W['site']['user_id'] == $this->current_admin['id']) {

            }else{
                $this->zbn_msg("授权错误");
            }
        }
    }

    public function map_fenzhan($map_old = [])
    {
        global $_W;
        $map = [];
        if (!$this->super_power) {
            $map['site_id'] = $_W['site']['id'];
        }
        return array_merge($map, $map_old);
    }

}