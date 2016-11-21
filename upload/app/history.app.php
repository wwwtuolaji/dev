<?php

class HistoryApp extends StoreadminbaseApp
{
    var $_store_id;
    var $_store_mod;
    function __construct()
    {
        $this->HistoryApp();
    }
    function HistoryApp()
    {
        parent::__construct();
        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_store_mod =& m('record');
      
        
    }
    function index()
    {
        $page = $this->_get_page(10);
        $page_limit=$page['limit'];
        $store_info=$this->_store_mod->get_record_by_store_id($this->_store_id, $page_limit);
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                         "商品浏览记录", 'index.php?app=history',
                         "记录列表");
        $page['item_count'] = $this->_store_mod->get_record_count_by_store_id($this->_store_id);
        $this->_format_page($page);
        $this->assign('page_info', $page);
        //数据处理
        if(!empty($store_info)){
            foreach ($store_info as $key => $value) {
                $store_info[$key]['visit_date']=date("Y-m-d H:i:s",$value['visit_date']);
                switch ($value['member_level']) {
                    case '1':
                        $store_info[$key]['member_level']="店主至尊";
                        break;
                    case '2':
                        $store_info[$key]['member_level']="至尊会员";
                        break;
                    case '3':
                        $store_info[$key]['member_level']="金卡会员";
                        break;
                    case '4':
                        $store_info[$key]['member_level']="福禄卡会员";
                        break;
                    
                    default:
                        $store_info[$key]['member_level']="普通会员";
                        break;
                }
                

            }
        }
        
        $this->_curitem('history');
        $this->_curmenu('history_list');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . "商品浏览记录");
        $this->assign('store_info', $store_info);
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->assign('time', gmtime());
        $this->display('history.index.html');
    }

   /* function add()
    {
        if (!IS_POST)
        {
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->assign('today', gmtime());
            $this->display('coupon.form.html');
        }
        else
        {

            $coupon_value = floatval(trim($_POST['coupon_value']));
            $use_times = intval(trim($_POST['use_times']));
            $min_amount = floatval(trim($_POST['min_amount']));
            if (empty($coupon_value) || $coupon_value < 0 )
            {
                $this->pop_warning('coupon_value_not');
                exit;
            }
            if (empty($use_times))
            {
                $this->pop_warning('use_times_not_zero');
                exit;
            }
            if ($min_amount < 0)
            {
                $this->pop_warning("min_amount_gt_zero");
                exit;
            }
            $start_time = gmstr2time(trim($_POST['start_time']));
            $end_time = gmstr2time_end(trim($_POST['end_time'])) - 1 ;
            if ($end_time < $start_time)
            {
                $this->pop_warning('end_gt_start');
                exit;
            }
            $coupon = array(
                'coupon_name' => trim($_POST['coupon_name']),
                'coupon_value' => $coupon_value,
                'store_id' => $this->_store_id,
                'use_times' => $use_times,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'min_amount' => $min_amount,
                'if_issue'  => trim($_POST['if_issue']) == 1 ? 1 : 0,
            );
            $this->_coupon_mod->add($coupon);
            if ($this->_coupon_mod->has_error())
            {
                $this->pop_warning($this->_coupon_mod->get_error());
                exit;
            }
            $this->pop_warning('ok', 'coupon_add');
        }
    }

    function edit()
    {
        $coupon_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (empty($coupon_id))
        {
            echo Lang::get("no_coupon");
        }
        if (!IS_POST)
        {
            header("Content-Type:text/html;charset=" . CHARSET);
            $coupon = $this->_coupon_mod->get_info($coupon_id);
            $this->assign('coupon', $coupon);
            $this->display('coupon.form.html');
        }
        else
        {
            $coupon_value = floatval(trim($_POST['coupon_value']));
            $use_times = intval(trim($_POST['use_times']));
            $min_amount = floatval(trim($_POST['min_amount']));
            if (empty($coupon_value) || $coupon_value < 0 )
            {
                $this->pop_warning('coupon_value_not');
                exit;
            }
            if (empty($use_times))
            {
                $this->pop_warning('use_times_not_zero');
                exit;
            }
            if ($min_amount < 0)
            {
                $this->pop_warning("min_amount_gt_zero");
                exit;
            }
            $start_time = gmstr2time(trim($_POST['start_time']));
            $end_time = gmstr2time_end(trim($_POST['end_time']))-1;
            //echo gmstr2time_end(trim($_POST['end_time'])) . '-------' .$end_time;exit; 
            if ($end_time < $start_time)
            {
                $this->pop_warning('end_gt_start');
                exit;
            }
            $coupon = array(
                'coupon_name' => trim($_POST['coupon_name']),
                'coupon_value' => $coupon_value,
                'store_id' => $this->_store_id,
                'use_times' => $use_times,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'min_amount' => $min_amount,
                'if_issue'  => trim($_POST['if_issue']) == 1 ? 1 : 0,
            );
            $this->_coupon_mod->edit($coupon_id, $coupon);
            if ($this->_coupon_mod->has_error())
            {
                $this->pop_warning($this->_coupon_mod->get_error());
                exit;
            }
            $this->pop_warning('ok','coupon_edit');
        }
    }*/
} 