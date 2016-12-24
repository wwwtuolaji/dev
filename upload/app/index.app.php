<?php
class IndexApp extends IndexbaseApp
{	
	function __construct()
	{
		parent::__construct();
		//parent::_config_view();
	}
	function index()
	{
		$visitor=$this->visitor->get();
		if (!$this->visitor->has_login)
        	{
        		//未登录
        		$member_info=false;
        	}else{
        		//已经登陆
        		$user_info=$this->visitor->get();
        		//过滤前台分配数据
        		$member_info['level']	= $user_info['member_level'];
        		$member_info['card']	= $user_info['member_card_num'];
        		$member_info['name']	= $user_info['user_name'];
        	}
        	//获取企业信息
        	$store_arr=$this->_get_data_priv_store();
		$notice=$this->_get_article_notice();
		$drogue_arr=$this->_get_data();
		$goods_new=$this->_get_data_new();

		$out_data['notice']=$notice;
		$out_data['member_info']=$member_info;
		$out_data['goods_new']=$goods_new;
		$out_data['drogue_arr']=$drogue_arr;
		$out_data['store_arr']=$store_arr;
		$this->assign("out_data",$out_data);
		$this->display("index.html");
	}
	/**
	 * [loan 贷款页面]
	 * @return [type] [description]
	 */
	function loan()
	{
		if (empty($_POST)) {
			$this->display("loan.html");	
		}else{

		}
	}
	/**
	 * [leisure 茶休闲会所]
	 * @return [type] [description]
	 */
	function leisure(){
		
		$this->display("leisure_clubs.html");
	}
	function enterprise(){
		$store_arr=$this->_get_data_priv_store(8);
		$out_data['store_arr']=$store_arr;
		$this->assign("out_data",$out_data);
		$this->display("enterprise.html");
	}
	/**
	 * [_get_article_notice 获取当前动态文章信息]
	 * @return [type] [description]
	 */
	function _get_article_notice()
	{
	      $acategory_mod =& m('acategory');
	      $article_mod =& m('article');
	      $data = $article_mod->find(array(
	            'conditions'    => 'cate_id=' . $acategory_mod->get_ACC(ACC_NOTICE) . ' AND if_show = 1',
	            'order'         => 'sort_order ASC, add_time DESC',
	            'fields'        => 'article_id, title, add_time',
	             'limit'         => $this->_num,
            	));
        	return $data;
	}
	/**
	 * [_get_data 获取茶叶风向标数据]
	 * @return [type] [description]
	 */
	function _get_data()
	{
		$db = &db();
		$drogue=$db->getall("select id, type, real_price,goods_id,show_date from (select * from ecm_drogue as b order by show_date desc) as d group by d.goods_id");
		if (!empty($drogue)) {
			foreach ($drogue as $key => $value) {
				//升跌=实际价格-参考价格
				$drogue_row=$db->getrow("select goods_id,goods_name from ecm_goods where goods_id=".$value['goods_id']);
				//获取前一天的价格当做参考价
				$date=$value['show_date']-(3600*24);
				$pre_sql="select real_price from ecm_drogue where show_date ='" .$date ."' and goods_id='".$value['goods_id'] ."'order by show_date desc";
				$value['ref_price']=$db->getone($pre_sql);
				 $i=2;
				//如果为空进入循环直到有数据的那一天
				while (empty($value['ref_price'])) {
					$date=$value['show_date']-(3600*24*$i);
					$pre_sql="select real_price from ecm_drogue where show_date ='" .$date ."' and goods_id='".$value['goods_id'] ."'order by show_date desc";
					$value['ref_price']=$db->getone($pre_sql);
					$i++;
					if ($i>50) {
						break;
					}
				}	
				$value['tea_name']=$drogue_row['goods_name'];
				$value['goods_id']=$drogue_row['goods_id'];
				$value['fluctuate_price']=$value['real_price']-$value['ref_price'];
				if ($value['type']==0) {					
					if (empty($value['ref_price'])||$value['ref_price']==0.00) {
						continue;
					}
					$value['percentage']=$value['fluctuate_price']/$value['ref_price'];	
					$value['percentage']=$value['percentage']*100;
					$value['percentage']=number_format($value['percentage'],1);
					$value['status']=$value['fluctuate_price']>=0?1:0;
					$value['fluctuate_price']=abs($value['fluctuate_price']);
					$value['percentage']=abs($value['percentage']);
					$drogue_a[]=$value;	
				}elseif ($value['type']==1) {
					//升跌=实际价格-参考价格
					if (empty($value['ref_price'])||$value['ref_price']==0.00) {
						continue;
					}
					$value['percentage']=$value['fluctuate_price']/$value['ref_price'];	
					$value['percentage']=$value['percentage']*100;
					$value['percentage']=number_format($value['percentage'],1);
					$value['status']=$value['fluctuate_price']>=0?1:0;
					$value['fluctuate_price']=abs($value['fluctuate_price']);
					$value['percentage']=abs($value['percentage']);
					$drogue_b[]=$value;
					
				}elseif ($value['type']==2) {
					//升跌=实际价格-参考价格
					if (empty($value['ref_price'])||$value['ref_price']==0.00) {
						continue;
					}
					$value['percentage']=$value['fluctuate_price']/$value['ref_price'];	
					$value['percentage']=$value['percentage']*100;
					$value['percentage']=number_format($value['percentage'],1);
					$value['status']=$value['fluctuate_price']>=0?1:0;
					$value['fluctuate_price']=abs($value['fluctuate_price']);
					$value['percentage']=abs($value['percentage']);
					$drogue_c[]=$value;
				}

				if($value['status']===1){
					$drogue_upmount[]=$value;	
				}
					
			}
		}else{
			$options['drogue_a']=array();
			$options['drogue_b']=array();	
			$options['drogue_c']=array();	
		}

		$options['drogue_a']=$drogue_a;
		$options['drogue_b']=$drogue_b;	
		$options['drogue_c']=$drogue_c;
		$options['drogue_upmount']=$this->_get_upmount($drogue_upmount);
		/*dump($options['drogue_upmount']);*/
		return $options;
	}
	/*涨幅排序*/
	function _get_upmount($data_upmount)
	{
		$sort = array(  
		        'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
		        'field'     => 'percentage',       //排序字段  
		);  
		$arrSort=array();
		foreach ($data_upmount as $uniqid => $row) {  
		    foreach($row as $key=>$value){  
		        $arrSort[$key][$uniqid] = $value;  
		    }  
		
		}
		if($sort['direction']){  
    			array_multisort($arrSort[$sort['field']], constant($sort['direction']), $data_upmount);  
		}  

		return array_slice($data_upmount,0,10);

	}
	/*获取推荐的图片信息*/
	function _get_data_new()
    	{
       
            $recom_mod =& m('recommend');
           
            $img_goods_list = $recom_mod->get_recommended_goods(7, 3, true, 1);
       	
       	return $img_goods_list;
    	}
    	/*获取非自营店铺信息*/
    	function _get_data_priv_store($limit=6){
    		$db=&db();
    		$inner_join="SELECT
			store.store_id,
			store.store_name
		FROM
			ecm_store AS store
		INNER JOIN ecm_category_store AS cs ON store.store_id = cs.store_id
		INNER JOIN ecm_scategory AS es ON cs.cate_id = es.cate_id
		WHERE
			es.cate_name NOT LIKE 'VIP%'
		AND es.parent_id = 0 order by store.sort_order ASC limit $limit";
		$store_arr=$db->getall($inner_join);
		return $store_arr;
    	}

}