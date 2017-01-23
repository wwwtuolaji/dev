<?php

/**
 * 精品推荐挂件
 *
 * @param   int     $img_recom_id   图文推荐id
 * @return  array
 */
class Agent_listWidget extends BaseWidget
{
    var $_name = 'agent_list';
    var $_ttl  = 1800;
    var $_num  = 3;

    function _get_data()
    {
        //优先获取顶级是茶的所有子分类id
        $db=&db();
        $sql="select * from ecm_agent limit ".$this->_num;
        $agent= $db->getall($sql);
        return $agent;
    }

    function get_config_datasrc()
    {
       
    }

    function parse_config($input)
    {
        
    }

}

?>