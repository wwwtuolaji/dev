<?php

class DefaultApp extends MallbaseApp
{
    function index()
    {
        $this->assign('index', 1); // 标识当前页面是首页，用于设置导航状态
        $this->assign('icp_number', Conf::get('icp_number'));

        /* 热门搜素 */
        $this->assign('hot_keywords', $this->_get_hot_keywords());

        $this->_config_seo(array(
            'title' => Lang::get('mall_index') . ' - ' . Conf::get('site_title'),
        ));
        $this->assign('page_description', Conf::get('site_description'));
        $this->assign('page_keywords', Conf::get('site_keywords'));
        $this->display('index.html');
    }
    function license()
    {
    	$LI_SESSION=$_POST['session_id'];
    	if(empty($LI_SESSION))
        {
        	exit;
        }
    	$db =& db();
    	$sql="SELECT count(`sesskey`) FROM `".DB_PREFIX."sessions`  WHERE `sesskey`='$LI_SESSION'";
    	$result=$db->getOne($sql);
    	if($result)
        {
           exit('{"res":"succ","msg":"","info":""}');	
        }
        
    }

    function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }
}

?>
