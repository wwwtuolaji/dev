<?php

/**
 * 精品推荐挂件
 *
 * @param   int     $img_recom_id   图文推荐id
 * @return  array
 */
class Comment_shareWidget extends BaseWidget
{
    var $_name = 'comment_share';
    var $_ttl  = 1800;
    var $_num  = 200;

    function _get_data()
    {
        //优先获取顶级是茶的所有子分类id
        $db=&db();
        $sql="select * from ecm_gcategory";
        $categorys= $db->getall($sql);
        $tea_id_arr=$this->_tree($categorys,'1');
        $tea_id_str=implode($tea_id_arr, ",");
        $goods_ids="select goods_id from ecm_goods where cate_id in ($tea_id_str)";
        $ids=$db->getcol($goods_ids);
        $tea_id_str=implode($ids,",");
        $order_goods_mod =& m('ordergoods');
        $comments = $order_goods_mod->find(array(
            'conditions' => "evaluation_status = '1' and goods_id in ($tea_id_str) group by goods_id",
            'join'  => 'belongs_to_order',
            'fields'=> 'goods_id,buyer_id, buyer_name, anonymous, evaluation_time, comment, evaluation',
            'count' => true,
            'order' => 'evaluation_time desc',
            'limit' => $this->_num,
        ));
        //通过goods_id查询当前商品的信息
       /* foreach ($comments as $key => $comment) {
           $goods_ids[]=$comment['goods_id'];
        }
        $goods_mod=& m('goods');
        $goods_id_str=implode($goods_ids, ",");*/
        foreach ($comments as $key => $comment) {
           $sql="select default_image,goods_id,goods_name from ecm_goods where goods_id=". $comment['goods_id'];
           $goods_info=$db->getrow($sql);
           if(is_array($goods_info)&&is_array($comment)){
                $comment_info[]=array_merge($goods_info,$comment);
           }
           
        }
        //$data['comments'] = $comments;
        //dump($comments);
        return $comment_info;
    }

    function get_config_datasrc()
    {
        // 取得推荐类型
        $this->assign('recommends', $this->_get_recommends());

        // 取得一级商品分类
        $this->assign('gcategories', $this->_get_gcategory_options(1));
    }

    function parse_config($input)
    {
        if ($input['img_recom_id'] >= 0)
        {
            $input['img_cate_id'] = 0;
        }

        return $input;
    }
    //取出顶级为茶的所有商品类
    function _tree($goods_categorys,$parent_id){
        static $tea_id=array();
        foreach ($goods_categorys as $goods_category) {
            if ($goods_category['parent_id']==$parent_id) {
                $tea_id[]=$goods_category['cate_id'];
                $this->_tree($goods_categorys,$goods_category['cate_id']);
            }
        }
        return $tea_id;
    }
}

?>