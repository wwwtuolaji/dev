<?php

/* 商品数据模型 */

class TransactionModel extends BaseModel
{
    var $table = 'transaction';
    var $prikey = 'transaction_sn';
    var $_name = 'transaction';
    var $temp; // 临时变量

    function cancel_agent_mod($sn, $goods_count)
    {
        $db = db();
        $sql = "update ecm_transaction set transaction_status=-1 where transaction_sn='$sn' and transaction_count='$goods_count'";
        $check = "select transaction_sn from ecm_transaction where transaction_sn='$sn' and transaction_count='$goods_count'";
        $sn = $db->getone($check);
        if (empty($sn)) {
            return false;
        }
        $result = $db->query($sql);

        if ($result) {
            return true;
        }
        return false;
    }


    /**
     * [transaction_produce_mod 刷新交易状态]
     * @return [type] [description]
     */
    function transaction_produce_mod($transaction)
    {
        $db = db();
        $i = 0;
        $b = 0;
        foreach ($transaction as $key => $tran) {
            //该用户买的，需要与之对应的卖的用户
            if ($tran['transaction_status'] == 0 && $tran['transaction_count'] > 0) {
                if ($i === 0) {
                    $get_sell = "select * from ecm_transaction where user_id <> '{$tran['user_id']}' and transaction_status=1 and transaction_count>0 and goods_id=''{$tran['user_id']}' and transaction_price ='{$tran['transaction_price']}' order by transaction_time asc";
                    $sell_arr = $db->getall($get_sell);
                    $i++;
                }
                //生成交易
                $this->produce_sell_deal($sell_arr, $tran);
            } else if ($tran['transaction_status'] == 1 && $tran['transaction_count'] > 0) {
                //该用户卖应该找到与之对应买的用户
                if ($b === 0) {
                    $get_buy = "select * from ecm_transaction where user_id <> '{$tran['user_id']}' and transaction_status=0 and transaction_count>0 and goods_id=''{$tran['user_id']}' and transaction_price ='{$tran['transaction_price']}' order by transaction_time asc";
                    $b++;
                    $buy_arr = $db->getall($get_buy);
                }
                $this->produce_buy_deal($buy_arr, $tran);
            }

        }
    }

    /**
     * [get_cur_count 获取当前的交易数量]
     * @param  [type] $sn [description]
     * @return [type]     [description]
     */
    function get_cur_count($sn)
    {
        $db = db();
        $transaction_count = $db->getone('select transaction_count from ecm_transaction where transaction_sn=' . $sn);
        return $transaction_count;
    }

    /**
     * [produce_buy_deal 刷新购买的状态]
     * @return [type] [description]
     */
    function produce_buy_deal($buy_arr, $tran)
    {
        $history_mod =& m('transaction_history');
        $transaction_mod =& m('transaction');
        $db = db();
        foreach ($buy_arr as $buy) {
            $count = $this->get_cur_count($tran['transaction_sn']);
            if ($count == 0) {
                return;
            }
            //买的数量大于 个人要卖的数量直接成交
            if ($buy['transaction_count'] >= $count) {
                //修改买方的状态
                $db->query("BEGIN");
                $buy_data['transaction_count'] = $buy['transaction_count'] - $count;
                if ($buy['transaction_count'] == $count) {
                    $buy_data['have_transaction'] = 2;
                } else {
                    $buy_data['have_transaction'] = 1;
                }
                $buy_edit = $transaction_mod->edit($buy['transaction_sn'], $buy_data);
                //增加买方的库存和金额
                $buy_insert = $this->insert_warehouse($buy['user_id'], $buy['goods_id'], $count,$tran['transaction_price']);

                //修改卖方的状态
                $sell_data['transaction_count'] = 0;
                $sell_data['have_transaction'] = 2;
                $sell_edit = $transaction_mod->edit($tran['transaction_sn'], $sell_data);
                //减少卖方的库存
  
                $sell_later = $this->alter_user_count($tran['user_id'], $tran['goods_id'], $count);
          
                $insert_data['transaction_history_count'] = $count;
                $insert_data['sell_sn_id'] = $tran['transaction_sn'];
                $insert_data['buy_sn_id'] = $buy['transaction_sn'];
                $insert_data['transaction_time'] = time();
                $insert_data['goods_id'] = $tran['goods_id'];
                $insert_data['transaction_price'] = $tran['transaction_price'];
                $result = $history_mod->add($insert_data);
                if ($result && $buy_edit && $buy_insert && $sell_edit && $sell_later) {
                    $db->query('COMMIT');
                } else {
                    $db->query('ROLLBACK');
                }
                $db->query("END");
                return;
            }
            //买的数量小 与 个人要卖的数量
            //修改买方的状态
            $db->query("BEGIN");
            $buy_data_re['transaction_count'] = 0;
            $buy_data_re['have_transaction'] = 2;
            $buy_edit_re = $transaction_mod->edit($buy['transaction_sn'], $buy_data_re);
            //增加买方的库存
            $buy_insert_re = $this->insert_warehouse($buy['user_id'], $buy['goods_id'], $buy['transaction_count'],$tran['transaction_price']);
            //修改个人卖方的状态
            $sell_data_re['transaction_count'] = $count - $buy['transaction_count'];
            $sell_data_re['have_transaction'] = 1;
            $sell_edit_re = $transaction_mod->edit($buy['transaction_sn'], $sell_data_re);
            //减少卖方的库存
       
             /*$sell_alter_re = $this->alter_user_count($tran['user_id'], $tran['goods_id'], $tran['transaction_count']);*/
          
            $insert_data_re['transaction_history_count'] = $buy['transaction_count'];
            $insert_data_re['sell_sn_id'] = $tran['transaction_sn'];
            $insert_data_re['buy_sn_id'] = $buy['transaction_sn'];
            $insert_data_re['transaction_time'] = time();
            $insert_data_re['goods_id'] = $tran['goods_id'];
           	$insert_data_re['transaction_price'] = $tran['transaction_price'];
            $result_re = $history_mod->add($insert_data_re);
            if ($result_re && $buy_edit_re && $buy_insert_re && $sell_edit_re && $sell_alter_re) {
                $db->query('COMMIT');
            } else {
                $db->query('ROLLBACK');
            }
            $db->query("END");
        }

    }

    function produce_sell_deal($sell_arr, $tran)
    {
        $history_mod =& m('transaction_history');
        $transaction_mod =& m('transaction');
        $db = db();
        //生成卖交易
        foreach ($sell_arr as $key => $sell) {
            $count = $this->get_cur_count($tran['transaction_sn']);
            if ($count == 0) {
                return;
            }
            //卖的数量大于买的数量一单成交
            if ($sell['transaction_count'] >= $count) {
                //开启事务
                /*$updade_dat='update '*/
                $db->query("BEGIN");
                //修改卖方的状态
                $updade_data['transaction_count'] = $sell['transaction_count'] - $count;
                if ($sell['transaction_count'] == $count) {
                    $updade_data['have_transaction'] = 2;
                } else {
                    $updade_data['have_transaction'] = 1;
                }
                $sell_edit = $transaction_mod->edit($sell['transaction_sn'], $updade_data);
                //修改卖方的持仓数量，-1是初始数据
          
                  $sell_alter = $this->alter_user_count($sell['user_id'], $sell['goods_id'], $count);
              
                //.......................

                //修改买方的状态
                $buy_data['transaction_count'] = 0;
                $buy_data['have_transaction'] = 2;
                $buy_edit = $transaction_mod->edit($tran['transaction_sn'], $buy_data);
                //修改买方的持仓数量
                $buy_insert = $this->insert_warehouse($tran['user_id'], $tran['goods_id'], $count,$tran['transaction_price']);
                //生成订单交易
                $insert_data['transaction_history_count'] = $count;
                $insert_data['sell_sn_id'] = $sell['transaction_sn'];
                $insert_data['buy_sn_id'] = $tran['transaction_sn'];
                $insert_data['transaction_time'] = time();
                $insert_data['goods_id'] = $tran['goods_id'];
                $insert_data['transaction_price'] = $tran['transaction_price'];
                $result = $history_mod->add($insert_data);

                if ($result && $buy_edit && $sell_edit && $sell_alter && $buy_insert) {
                    $db->query('COMMIT');
                } else {
                    $db->query('ROLLBACK');
                }
                $db->query('END');
                if (!$result) {
                    dump('业务异常');
                    return false;
                }
                return;
            }

            //买的数量大于卖的数量
            $db->query("BEGIN");
            //修改卖方的状态
            $sell_data_re['transaction_count'] = 0;
            $sell_data_re['have_transaction'] = 2;
            $sell_edit_re = $transaction_mod->edit($sell['transaction_sn'], $sell_data_re);
            //修改卖方的库存
             $sell_alter_re = $this->alter_user_count($sell['user_id'], $sell['goods_id'], $sell['transaction_count']);
      

            //修改买方的状态
            $buy_data_re['transaction_count'] = $sell['transaction_count'];
            $buy_data_re['have_transaction'] = 1;
            $buy_edit_re = $transaction_mod->edit($tran['transaction_sn'], $buy_data_re);
            //修改买方的持仓数量
            $buy_insert_re = $this->insert_warehouse($tran['user_id'], $tran['goods_id'], $sell['transaction_count'],$tran['transaction_price']);
            //生成订单交易
            $insert_data_re['transaction_history_count'] = $count;
            $insert_data_re['sell_sn_id'] = $sell['transaction_sn'];
            $insert_data_re['buy_sn_id'] = $tran['transaction_sn'];
            $insert_data_re['transaction_time'] = time();
            $insert_data_re['goods_id'] = $tran['goods_id'];
          	$insert_data_re['transaction_price'] = $tran['transaction_price'];
            $result_re = $history_mod->add($insert_data_re);
            if ($result_re && $sell_edit_re && $sell_alter_re && $buy_edit_re && $buy_insert_re) {
                $db->query('COMMIT');
            } else {
                $db->query('ROLLBACK');
            }
            $db->query('END');
        }
    }
    /*获取当前交易的金额*/
    function get_transaction_price($user_id, $goods_id){
    	$db = db();
    	$transaction_price = "select transaction_price from ecm_own_warehouse where user_id='$user_id' and goods_id=$goods_id";
    	$transaction_price=$this->getone($transaction_price);
    	if ($transaction_price===false) {
    		dump('金额异常检查！');
    	}
    	return $transaction_price;
    }

    /**
     * [alter_user_count 修改持仓人的持仓数量和金额]
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    function alter_user_count($user_id, $goods_id, $count)
    {
        $db = db();
        $get_user_count = "select * from ecm_own_warehouse where user_id='$user_id' and goods_id=$goods_id";
        $warehouse = $db->getrow($get_user_count);
        $goods_count = $warehouse['goods_count'] - $count;
        if ($warehouse['warehouse_id']) {
            $sql = "update ecm_own_warehouse set  goods_count='$goods_count' where warehouse_id='{$warehouse['warehouse_id']}'";
            $result = $db->query($sql);
            return $result;
        }
        die('业务异常，请检查！');
        return false;
    }

    function insert_warehouse($user_id, $goods_id, $count,$price)
    {
        $db = db();
        $get_user_count = "select * from ecm_own_warehouse where user_id='$user_id' and goods_id=$goods_id";
        $warehouse = $db->getrow($get_user_count);
        if ($warehouse['warehouse_id']) {
            $goods_count = $warehouse['goods_count'] + $count;
            $transaction_price = $warehouse['transaction_price']*100 + $price*100;
            $transaction_price = $transaction_price /100;
            $sql = "update ecm_own_warehouse set  goods_count='$goods_count',transaction_price='$transaction_price' where warehouse_id='{$warehouse['warehouse_id']}'";
            $result = $db->query($sql);
            return $result;
        }
        $own_warehouse_mod =& m('own_warehouse');
        $own_data['user_id'] = $user_id;
        $own_data['goods_id'] = $goods_id;
        $own_data['count'] = $count;
        $own_data['transaction_price']  = $price;
        $result = $own_warehouse_mod->add($own_data);
        return $result;
    }
    /**
     * [get_warehouse 获取库存明细]
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    function get_warehouse($user_id){
    	$db=db();
    	$sql="select a.*,b.goods_name,b.cate_id from ecm_own_warehouse as a inner join ecm_share_tea as b on a.goods_id=b.goods_id where a.user_id='$user_id' and a.goods_count<>0";
    	$result=$db->getall($sql);
    	if (empty($result)) {
    		return array();
    	}
    	$str=date('Y-m-d',time());
    	$begin_time=strtotime($str);
    	$str=$str. ' 9:30';
    	$end_time=strtotime($str);
    	foreach ($result as $key => $goods) {
    		//持仓均价
    		$goods['transaction_price']=$goods['transaction_price']*100;
    		$goods['pre_price']=$goods['transaction_price']/$goods['goods_count'];
    		$goods['pre_price']=$goods['pre_price']/100;
    		$result[$key]['pre_price'] = sprintf("%.2f",substr(sprintf("%.3f", $goods['pre_price']), 0, -1)); 

    		//当前价格:今天9点半交易的第一单
    		$get_cur_price="select sum(transaction_price) as total_price,transaction_price from ecm_transaction_history where goods_id={$goods['goods_id']} and transaction_time>'$begin_time' and transaction_time<'$begin_time' ";
    		$price_arr=$db->getcol($get_cur_price);
    		$price_count=count($price_arr['transaction_price']);
    		/*dump($price_count);*/
    		if ($price_count) {
    			$total_price=$price_arr['total_price']*100;
    			$current_price=$total_price/$price_count;
    			$current_price=$current_price/100;
    			$result[$key]['current_price']=sprintf("%.2f",substr(sprintf("%.3f", $current_price), 0, -1)); 
    		}else{
    			/*$this->show_warrring('获取库存明细')*/
    			//当前价格:是昨日的收盘价
    			$arr=$this->_get_zhang_ting($goods['goods_id'], time());
    			$result[$key]['current_price']=$arr['shoupan'];
    			
    		}
    		//实时浮动盈亏价格：  （当前价格*数量 -持仓金额）/持仓金额 
    		$current_price=$result[$key]['current_price']*100;
    		/*dump($goods);*/
    		$fdyk=($current_price* $goods['goods_count']-$goods['transaction_price'])/$goods['transaction_price']*100;
    		/*dump($fdyk);*/
    		$result[$key]['fdyk']=sprintf("%.2f",substr(sprintf("%.3f", $fdyk), 0, -1));
    		if (in_array($goods['cate_id'], array(84, 86, 87, 88, 89, 90, 91, 92, 93, 94, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111))) {
                    //中期茶中 期茶2010年之前        
                    $result[$key]['type'] = 2;
                    $result[$key]['type_des'] = '中期茶';
                } else if (in_array($goods['cate_id'], array(95, 96, 97, 98, 99, 112, 113, 114, 115, 116))) {
                    //新茶  新茶包含2010-2014
                    $result[$key]['type'] = 1;
                    $result[$key]['type_des'] = '新茶';
                } else {
                    # code...
                    $result[$key]['type'] = 0;
                    $result[$key]['type_des'] = '当年茶';
                }
    	}
    	return $result;
    }
    /**获取当前用户的持仓数量*/
    function get_cur_user_count($user_id,$goods_id){

    }
     /**
     * [_get_zhang_ting 获取涨,跌停的价格]
     * @param  [type] $goods_id [description]
     * @return [type]           [description]
     */
    function _get_zhang_ting($goods_id, $transaction_time)
    {
        //涨停价，跌停价以昨日收盘价上下浮动10%
        //获取交易次数
        $db = db();
        $get_count = "select count(*) from ecm_transaction_history  where goods_id='$goods_id'";
        $count = $db->getone($get_count);
        //只有一天或者一天都没有就取原始茶的价格
        if ($get_count < 2) {
            $sql = "select price from ecm_share_spec where goods_id='$goods_id'";
            $price = $db->getone($sql);
            $price=$price*100;
            $out_data['zhangting'] = $price * 11/1000;
            $out_data['dieting'] = $price * 9/1000;
            $out_data['shoupan'] = $price/100;
            return $out_data;
        }
        //格式化时间获取当前天的凌晨0点
        $sim_date = date("Y-m-d", $transaction_time);
        $end_time = strtotime($sim_date);
        //前一天的时间
        $get_pre_final = "select transaction_price from ecm_transaction_history where transaction_time<'$end_time' and transaction_have>0 order by history_id desc limit 1";
        $price = $db->getone($get_pre_final);
        $price =$price *100;
        $out_data['zhangting'] = $price * 1.1/1000;
        $out_data['dieting'] = $price * 0.9/1000;
        $out_data['shoupan'] = $price/100;
        return $out_data;
    }
}
