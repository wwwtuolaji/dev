<?php
/* 会员控制器 */
class AgentApp extends BackendApp
{
	 var $_agent;

    function __construct()
    {
        $this->AgentApp();
    }

    function AgentApp()
    {
        
        $this->_agent =& m('agent');
        parent::__construct();
    }
    function index()
    {
    	$db=&db();
    	$sql="select * from ecm_agent";
    	$agent_arr=$db->getall($sql);
    	$this->assign('ecm_agent',$agent_arr);
    	$this->display("agent.index.html");
    }
    	function add()
    	{
	    if (IS_POST) {
	    		//判断是编辑还是添加
	    		if($_POST['agent_id']){
	    			//编
	    			$agent_id=$_POST['agent_id'];
	    			unset($_POST['agent_id']);
	    			$result=$this->_agent->edit($agent_id, $_POST);
	    		}else{
	    			$result=$this->_agent->add($_POST);	
	    		}
	    		
	    		if ($result!==false) {
	    			$this->show_message('添加成功',
	                    '用户列表',    'index.php?app=agent',
	                    '重新编辑',   'index.php?app=agent&amp;act=edit&amp;id=' . $result
	                    );
	    			return;
	    		}else{
	    			 $this->show_warning('添加失败，请检查相关数据');
	           		 return;
	    		}
	    		
	    }
	    $this->display("agent.add.html");
    	}

     	function edit()
    	{
    		if (empty($_GET['agent_id'])) {
    			$this->show_warning('该经纪人不存在请重新编辑！');
    			return;
    		}
    		$agent_id=$_GET['agent_id'];
    		$agent_info = $this->_agent->get(array(
                    'conditions' => '1=1 AND agent_id ='.$agent_id,
                    'fields' => '*',
                ));
    		$this->assign('agent',$agent_info);
    		$this->display("agent.add.html");

    	}
    	function drop()
    	{
    		if(empty($_GET['agent_id'])){
    			$this->show_warning('删除失败');
    			return;
    		}
    		$agent_id=$_GET['agent_id'];
    		    $db = &db(); 
               //先删除当前id的内容
             $del="delete from ecm_agent where agent_id='$agent_id'";
              $result=$db->query($del);
             $this->show_message('删除成功');
	    	return;;
    	}
}