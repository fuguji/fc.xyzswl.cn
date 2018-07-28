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

use app\common\controller\HomeBase;
use app\common\util\Snoopy;
use app\core\model\ModulesSetting;
use app\core\util\MhcmsDistribution;
use app\core\util\MhcmsModules;
use think\Cookie;

class ModuleBase extends FrontBase
{

    public $module_config, $module_global_config;

    /**
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        global $_W;
        parent::_initialize(); // TODO: Change the autogenerated stub
        //加载模块全局配置
        $this->module_global_config = MhcmsModules::get_module_global_setting($this->module['module']);

        /**
         * 若为配置全局配置
         */
        if (isset($this->module_global_config['close_site']) && $this->module_global_config['close_site']) {
            echo $this->module_global_config['close_site_tips'];
            die();
        }

        //加载模块站点配置
        $this->module_config = $module_config = MhcmsModules::get_module_setting($this->module['module']);

        /**
         * 新开站点未配置 直接用全局
         */
        if (!$module_config) {
            $this->module_config = $this->module_global_config;
        } else {
            //自定义模块配置
            if (is_array($this->module_global_config)) {
                $this->module_config = array_merge($this->module_global_config, $module_config);
            }
        }


        $_W['module_config'] = $this->module_config;

        //全局默认缩略图
        if( $_W['module_config']['default_thumb']){
            $_W['module_config']['default_thumb'] = render_file_id($_W['module_config']['default_thumb']);
        }
        //load config
        if (isset($this->module_config['close_site']) && $this->module_config['close_site']) {
            echo $this->module_config['close_site_tips'];
            die();
        }

        $this->theme = $_W['theme'] = MhcmsModules::get_module_theme($this->module['module']);

        //判断模块是否制定了模板
        MhcmsModules::get_device_tpl($this->module['module']);



        $device = DIRECTORY_SEPARATOR . $_W['DEVICE_TYPE_TPL'];

        $this->view->config([
            'view_path' => strtolower(SYS_PATH . "tpl" . DS . 'themes' . DS . $this->theme . $device . DS . ROUTE_M . DS),
        ]);

        $this->view->view_path = strtolower(SYS_PATH . "tpl" . DS . 'themes' . DS . $this->theme . $device . DS . ROUTE_M . DS);
        $this->view->view =$this->view;

        /*
         * Template Config  View 模板变量在这里处理
         * */
        $this->view->front_base_layout = $this->view_config['front_' . $_W['DEVICE_TYPE_TPL'] . '_base_layout'];
        $this->view->header_tpl = $this->view_config[$_W['DEVICE_TYPE_TPL'] . '_header_tpl'];
        $this->view->footer_tpl = $this->view_config[$_W['DEVICE_TYPE_TPL'] . '_footer_tpl'];
        /**
         *****base seo 如果包含管理字段的 则不执行保存
         *
         */
        if ($this->module['is_seo']) {
            $this->view->seo = $this->seo($this->mapping);
        }
    }

}