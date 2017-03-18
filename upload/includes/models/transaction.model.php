<?php

/* 商品数据模型 */

class TransactionModel extends BaseModel
{
    var $table = 'transaction';
    var $prikey = 'transaction_sn';
    var $_name = 'transaction';
    var $temp; // 临时变量
    var $db;

    function cancel_agent_mod($sn, $goods_count, $user_id)
    {
        $this->db = db();
        /* $get_is="select transaction_sn from ecm_transaction where transaction_sn='$sn' and transaction_count='$goods_count' and transaction_status>-1";*/

        $check = "select goods_id,transaction_sn,transaction_status,waiting_pay_price,transaction_count,transaction_price  as univalent from ecm_transaction where transaction_sn='$sn' and transaction_count='$goods_count' and transaction_status>-1";
        $sn = $this->db->getrow($check);

        if (empty($sn['transaction_sn'])) {
            return false;
        }
        if (empty($user_id)) {
            return false;
        }
        $this->db->query("SET AUTOCOMMIT=0");
        //判断订单是买入还是卖出0:买，1卖，2其他,-1取消
        if ($sn['transaction_status'] == 1) {
            $buy = "update ecm_transaction set transaction_status=-1,waiting_pay_price=0 where transaction_sn='{$sn['transaction_sn']}' and transaction_count='$goods_count'";
            $result = $this->db->query($buy);
            //增加持仓数量和持仓成本
            $goods_id = $sn['goods_id'];
            $get_stock = "select transaction_price from ecm_own_warehouse where goods_id='$goods_id' and user_id=$user_id";
            $transaction_price_total = $this->db->getone($get_stock);
            $transaction_price = intval($transaction_price_total * 100) + intval($sn['univalent'] * 100) * $sn['transaction_count'];
            $transaction_price = $transaction_price / 100;
            //hh
            $get_chi = "update ecm_own_warehouse set goods_count=goods_count+'$goods_count',transaction_price='$transaction_price' where user_id='$user_id'and goods_id='$goods_id'";

            /*dump($get_chi);*/
            $result_re = $this->db->query($get_chi);
        } elseif ($sn['transaction_status'] == 0) {
            /*$get_stock="select transaction_price from ecm_own_warehouse where goods_id='$goods_id' and user_id=$user_id";
            $transaction_price_total=$this->db->getone($get_stock);
            $transaction_price=intval($transaction_price_total*100)/100-intval()*/
            $sell = "update ecm_transaction set transaction_status=-1,waiting_pay_price=0 where transaction_sn='{$sn['transaction_sn']}' and transaction_count='$goods_count'";
            $result = $this->db->query($sell);
            //退还金额到原来账号；
            //1.获取到当前的金额
            $get_price = "select use_money from ecm_member where user_id='$user_id'";
            $_price = $this->getone($get_price);
            $temp = intval($_price * 100) + intval($sn['waiting_pay_price'] * 100);
            $cur_price = $temp / 100;
            $up_price = "update ecm_member set use_money='$cur_price' where user_id='$user_id'";
            $result_re = $this->db->query($up_price);
            if ($result_re) {
                $money_hitory_mod =& m('money_history');
                //增加金额日志记录
                $add_money_arr = array(
                    'transaction_sn' => $sn['transaction_sn'],//历史id
                    'money_from' => 0,
                    'transaction_type' => 0,//0,收入 1支出
                    'receive_money' => $sn['waiting_pay_price'],
                    'pay_time' => time(),
                    'platform_from' => 2,//0,茶通历史表，1，商城 2，transaction
                    'use_money_history' => $cur_price,
                    'user_id' => $user_id,
                    'comments' => '买茶金额取消回退金额到原来账户',
                );
                $money_hitory_mod->add($add_money_arr);
            }
        }

        if ($result && $result_re) {
            $this->db->query("COMMIT");
            $this->db->query("SET AUTOCOMMIT=1");
            return true;
        } else {
            $this->db->query("ROLLBACK");
            $this->db->query("SET AUTOCOMMIT=1");
            return false;
        }
    }


    /**
     * [transaction_produce_mod 刷新交易状态]
     * @return [type] [description]
     */
    function transaction_produce_mod($transaction)
    {
        $this->db = db();
        $i = 0;
        $b = 0;
        foreach ($transaction as $key => $tran) {
            $get_tran = "select * from ecm_transaction where transaction_sn='{$tran['transaction_sn']}' and transaction_count>0 and  transaction_status>-1";
            $get_tran = $this->db->getrow($get_tran);
            //该用户买的，需要与之对应的卖的用户包括自己的
            if ($get_tran['transaction_status'] == 0 && $get_tran['transaction_count'] > 0) {

                /*$get_sell = "select * from ecm_transaction where user_id <> '{$get_tran['user_id']}' and transaction_status=1 and transaction_count>0 and goods_id='{$get_tran['goods_id']}' and transaction_price ='{$get_tran['transaction_price']}' order by transaction_time asc";*/
                $get_sell = "select * from ecm_transaction where transaction_status=1 and transaction_count>0 and goods_id='{$get_tran['goods_id']}' and transaction_price ='{$get_tran['transaction_price']}' order by transaction_time asc";
                $sell_arr = $this->db->getall($get_sell);

                $i++;

                //生成交易
                $this->produce_sell_deal($sell_arr, $get_tran);
                echo "用户买的结束";
            } else if ($get_tran['transaction_status'] == 1 && $get_tran['transaction_count'] > 0) {
                //该用户卖应该找到与之对应买的用户包括自己的
                /*$get_buy = "select * from ecm_transaction where user_id <> '{$get_tran['user_id']}' and transaction_status=0 and transaction_count>0 and goods_id='{$get_tran['goods_id']}' and transaction_price ='{$get_tran['transaction_price']}' order by transaction_time asc";*/
                $get_buy = "select * from ecm_transaction where transaction_status=0 and transaction_count>0 and goods_id='{$get_tran['goods_id']}' and transaction_price ='{$get_tran['transaction_price']}' order by transaction_time asc";

                $buy_arr = $this->db->getall($get_buy);

                $this->produce_buy_deal($buy_arr, $get_tran);
                echo "用户卖的结束";
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

        $transaction_count = $this->db->getone('select transaction_count from ecm_transaction where transaction_sn=' . $sn);
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

        foreach ($buy_arr as $buy) {
            $count = $this->get_cur_count($tran['transaction_sn']);
            if ($count == 0) {
                return;
            }
            //买的数量大于 个人要卖的数量直接成交
            if ($buy['transaction_count'] >= $count) {
                //修改买方的状态
                $this->db->query("SET AUTOCOMMIT=0");
                $buy_data['transaction_count'] = $buy['transaction_count'] - $count;
                if ($buy['transaction_count'] == $count) {
                    $buy_data['have_transaction'] = 2;
                } else {
                    $buy_data['have_transaction'] = 1;
                }
                //重新获取waiting_price可能有问题？？？？？

                $waiting_pay_price = intval($buy['waiting_pay_price'] * 100) - intval($buy['transaction_price'] * 100) * $count;
                $buy_data['waiting_pay_price'] = $waiting_pay_price / 100;
                $buy_edit = $transaction_mod->edit($buy['transaction_sn'], $buy_data);
                //增加买方的库存和金额
                $buy_insert = $this->insert_warehouse($buy['user_id'], $buy['goods_id'], $count, $tran['transaction_price']);
                //修改卖方的状态
                $sell_data['transaction_count'] = 0;
                $sell_data['have_transaction'] = 2;
                /*$sell_data['waiting_pay_price'] = 0;*/
                //增加mai方的use_money
                /*$temp_price = $count*(intval($buy['transaction_price']*100));*/
                $cur_price = "select use_money from ecm_member where user_id='{$tran['user_id']}'";
                $_price = $this->getone($cur_price);
                //获取当前的waiting_price               
                $temp = intval($_price * 100) + intval($buy['waiting_pay_price'] * 100);
                $cur_price = $temp / 100;
                $sell_edit = $transaction_mod->edit($tran['transaction_sn'], $sell_data);
                $up_price = "update ecm_member set use_money='$cur_price' where user_id='{$tran['user_id']}'";
                $pay_result = $this->db->query($up_price);

                //减少卖方的库存,库存不用再减因为库存已经在提交订单的时候减去了
                $sell_later = $this->alter_user_count($tran['user_id'], $tran['goods_id'], $count);
                $insert_data['transaction_history_count'] = $count;
                $insert_data['sell_sn_id'] = $tran['transaction_sn'];
                $insert_data['buy_sn_id'] = $buy['transaction_sn'];
                $insert_data['transaction_time'] = time();
                $insert_data['goods_id'] = $tran['goods_id'];
                $insert_data['transaction_price'] = $tran['transaction_price'];
                $result = $history_mod->add($insert_data);
                if ($pay_result) {
                    $money_hitory_mod =& m('money_history');
                    //增加金额日志记录
                    $add_money_arr = array(
                        'transaction_sn' => $result,//历史id
                        'money_from' => 0,
                        'transaction_type' => 0,//0,收入 1支出
                        'receive_money' => $buy['waiting_pay_price'],
                        'pay_time' => time(),
                        'platform_from' => 0,//0,茶通历史表，1，商城 2，transaction
                        'use_money_history' => $cur_price,
                        'user_id' => $tran['user_id'],
                        'comments' => '茶通卖方的金额增加商品id是：' . $tran['goods_id'],
                    );
                    $money_hitory_mod->add($add_money_arr);
                }

                if ($result && $buy_edit && $buy_insert && $sell_edit && $sell_later && $pay_result) {
                    $this->db->query('COMMIT');
                    $this->db->query("SET AUTOCOMMIT=1");
                    return true;
                } else {
                    $this->db->query('ROLLBACK');
                    echo "交易失败";
                    $this->db->query("SET AUTOCOMMIT=1");
                    return false;
                }
            }

        }
        //买的数量小 于 个人要卖的数量
        //修改买方的状态
        $this->db->query("SET AUTOCOMMIT=0");
        $buy_data_re['transaction_count'] = 0;
        $buy_data_re['have_transaction'] = 2;
        //减少买方的待支付金额
        $buy_data_re['waiting_pay_price'] = 0;
        $buy_edit_re = $transaction_mod->edit($buy['transaction_sn'], $buy_data_re);
        //增加买方的库存
        $buy_insert_re = $this->insert_warehouse($buy['user_id'], $buy['goods_id'], $buy['transaction_count'], $tran['transaction_price']);

        //修改个人卖方的状态
        $sell_data_re['transaction_count'] = $count - $buy['transaction_count'];
        $sell_data_re['have_transaction'] = 1;
        $sell_edit_re = $transaction_mod->edit($buy['transaction_sn'], $sell_data_re);
        //增加卖方的金额
        $cur_price = "select use_money from ecm_member where user_id='{$tran['user_id']}'";
        $_price = $this->getone($cur_price);
        $price_temp = intval($_price * 100);

        $temp_add_price = $buy['transaction_count'] * intval($buy['transaction_price'] * 100);
        $alter_price = ($temp_add_price + $price_temp) / 100;
        $add_price = $temp_add_price / 100;
        $up = "update ecm_member set use_money='$alter_price' where user_id='{$tran['user_id']}'";
        $up_usemoney = $this->db->query($up);
        //减少卖方的库存
        /*$sell_alter_re = $this->alter_user_count($tran['user_id'], $tran['goods_id'], $tran['transaction_count']);*/
        $insert_data_re['transaction_history_count'] = $buy['transaction_count'];
        $insert_data_re['sell_sn_id'] = $tran['transaction_sn'];
        $insert_data_re['buy_sn_id'] = $buy['transaction_sn'];
        $insert_data_re['transaction_time'] = time();
        $insert_data_re['goods_id'] = $tran['goods_id'];
        $insert_data_re['transaction_price'] = $tran['transaction_price'];
        $result_re = $history_mod->add($insert_data_re);
        if ($result_re) {
            $money_hitory_mod =& m('money_history');
            //增加金额日志记录
            $add_money_arr = array(
                'transaction_sn' => $result_re,//历史id
                'money_from' => 0,
                'transaction_type' => 0,//0,收入 1支出
                'receive_money' => $add_price,
                'pay_time' => time(),
                'platform_from' => 0,//0,茶通历史表，1，商城 2，transaction
                'use_money_history' => $alter_price,
                'user_id' => $tran['user_id'],
                'comments' => '茶通卖方的金额增加商品id是：' . $tran['goods_id'],
            );
            $money_hitory_mod->add($add_money_arr);
        }
        if ($result_re && $buy_edit_re && $buy_insert_re && $sell_edit_re  && $up_usemoney) {
            $this->db->query('COMMIT');
        } else {
            $this->db->query('ROLLBACK');
            echo "交易失败";
        }
        $this->db->query("SET AUTOCOMMIT=1");
    }


    /**
     * [produce_sell_deal description]
     * @param  [type] $sell_arr [需要交易的源数据]
     * @param  [type] $tran     [个人的交易数据]
     * @return [type]           [description]
     */
    function produce_sell_deal($sell_arr, $tran)
    {
        $history_mod =& m('transaction_history');
        $transaction_mod =& m('transaction');

        /*dump($sell_arr);
        */        //生成卖交易
        foreach ($sell_arr as $key => $sell) {
            $count = $this->get_cur_count($tran['transaction_sn']);
            if ($count == 0) {
                return;
            }
            //卖的数量大于买的数量一单成交
            if ($sell['transaction_count'] >= $count) {
                //开启事务
                /*$updade_dat='update '*/
                $this->db->query("SET AUTOCOMMIT=0");
                //修改卖方的状态
                $updade_data['transaction_count'] = $sell['transaction_count'] - $count;
                if ($sell['transaction_count'] == $count) {
                    $updade_data['have_transaction'] = 2;
                } else {
                    $updade_data['have_transaction'] = 1;
                }
                $sell_edit = $transaction_mod->edit($sell['transaction_sn'], $updade_data);

                //卖方用户金额增加
                //1.获取到当前的金额
                $cur_price = "select use_money from ecm_member where user_id='{$sell['user_id']}'";
                $_price = $this->getone($cur_price);
                $temp = intval($_price * 100) + intval($tran['waiting_pay_price'] * 100);
                $cur_price = $temp / 100;
                $up_price = "update ecm_member set use_money='$cur_price' where user_id='{$sell['user_id']}'";
                $pay_result = $this->db->query($up_price);
                //修改卖方的持仓数量，-1是初始数据
                $sell_alter = $this->alter_user_count($sell['user_id'], $sell['goods_id'], $count);
                //.......................
                //修改买方的状态
                $buy_data['transaction_count'] = 0;
                $buy_data['have_transaction'] = 2;
                $buy_data['waiting_pay_price'] = 0;
                $buy_edit = $transaction_mod->edit($tran['transaction_sn'], $buy_data);
                //修改买方的持仓数量
                $buy_insert = $this->insert_warehouse($tran['user_id'], $tran['goods_id'], $count, $tran['transaction_price']);
                //生成订单交易
                $insert_data['transaction_history_count'] = $count;
                $insert_data['sell_sn_id'] = $sell['transaction_sn'];
                $insert_data['buy_sn_id'] = $tran['transaction_sn'];
                $insert_data['transaction_time'] = time();
                $insert_data['goods_id'] = $tran['goods_id'];
                $insert_data['transaction_price'] = $tran['transaction_price'];
                $result = $history_mod->add($insert_data);
                if ($result) {
                    $money_hitory_mod =& m('money_history');
                    //增加金额日志记录
                    $add_money_arr = array(
                        'transaction_sn' => $result,//历史id
                        'money_from' => 0,
                        'transaction_type' => 0,//0,收入 1支出
                        'receive_money' => $tran['waiting_pay_price'],
                        'pay_time' => time(),
                        'platform_from' => 0,//0,茶通历史表，1，商城 2，transaction
                        'use_money_history' => $cur_price,
                        'user_id' => $sell['user_id'],
                        'comments' => '茶通卖方的金额增加商品id是：' . $tran['goods_id'],
                    );
                    $money_hitory_mod->add($add_money_arr);
                }
                /* if (!$result) {
                     echo "1";
                 }
                  if (!$buy_edit) {
                     echo "2";
                 }
                  if (!$sell_edit) {
                     echo "3";
                 }
                  if (!$sell_alter) {
                     echo "4";
                 }
                  if (!$buy_insert) {
                     echo "5";
                 }
             */

                if ($result && $buy_edit && $sell_edit && $sell_alter && $buy_insert && $pay_result) {
                    $this->db->query('COMMIT');
                    $this->db->query("SET AUTOCOMMIT=1");
                    echo "请求成功";
                    return true;

                } else {
                    $this->db->query('ROLLBACK');
                    echo "交易失败";
                    $this->db->query("SET AUTOCOMMIT=1");
                    echo "请求失败";
                }

                if (!$result) {
                    dump('业务异常');
                    return false;
                }
                return;
            }

            //买的数量大于卖的数量
            $this->db->query("SET AUTOCOMMIT=0");
            //修改卖方的状态
            $sell_data_re['transaction_count'] = 0;
            $sell_data_re['have_transaction'] = 2;
            $sell_edit_re = $transaction_mod->edit($sell['transaction_sn'], $sell_data_re);
            //增加卖方的金额
            $cur_price_re = "select use_money from ecm_member where user_id='{$sell['user_id']}'";
            $_price_re = $this->getone($cur_price_re);
            $temp_re = intval($_price_re * 100);
            $add_price_temp = intval($sell['transaction_price'] * 100);
            $add_price = $add_price_temp * $sell['transaction_count'];
            $use_money = ($add_price + $temp_re) / 100;
            $up_price_re = "update ecm_member set use_money='$use_money' where user_id='{$sell['user_id']}'";
            $up_price_re = $this->db->query($up_price_re );
            //修改卖方的库存
            $sell_alter_re = $this->alter_user_count($sell['user_id'], $sell['goods_id'], $sell['transaction_count']);
            //修改买方的状态
            $buy_data_re['transaction_count'] = $sell['transaction_count'];
            $buy_data_re['have_transaction'] = 1;
            $buy_temp_re = intval($tran['waiting_pay_price'] * 100);
            $buy_data_re['waiting_pay_price'] = ($buy_temp_re - $add_price) / 100;
            $buy_edit_re = $transaction_mod->edit($tran['transaction_sn'], $buy_data_re);
            //修改买方的持仓数量
            $buy_insert_re = $this->insert_warehouse($tran['user_id'], $tran['goods_id'], $sell['transaction_count'], $tran['transaction_price']);
            //生成订单交易
            $insert_data_re['transaction_history_count'] = $count;
            $insert_data_re['sell_sn_id'] = $sell['transaction_sn'];
            $insert_data_re['buy_sn_id'] = $tran['transaction_sn'];
            $insert_data_re['transaction_time'] = time();
            $insert_data_re['goods_id'] = $tran['goods_id'];
            $insert_data_re['transaction_price'] = $tran['transaction_price'];
            $result_re = $history_mod->add($insert_data_re);
            if ($result_re) {
                $money_hitory_mod =& m('money_history');
                //增加金额日志记录
                $add_money_arr = array(
                    'transaction_sn' => $result_re,//历史id
                    'money_from' => 0,
                    'transaction_type' => 0,//0,收入 1支出
                    'receive_money' => $add_price/100,
                    'pay_time' => time(),
                    'platform_from' => 0,//0,茶通历史表，1，商城 2，transaction
                    'use_money_history' => $use_money ,
                    'user_id' => $sell['user_id'],
                    'comments' => '茶通卖方的金额增加商品id是：' . $tran['goods_id'],
                );
                $money_hitory_mod->add($add_money_arr);
            }
            if($result_re){

            }
            if ($result_re && $sell_edit_re && $sell_alter_re && $buy_edit_re && $buy_insert_re && $up_price_re) {
                $this->db->query('COMMIT');
                $this->db->query("SET AUTOCOMMIT=1");
            } else {
                $this->db->query('ROLLBACK');
                echo "交易失败";
                $this->db->query("SET AUTOCOMMIT=1");
            }
        }
    }

    /*获取当前交易的金额*/
    function get_transaction_price($user_id, $goods_id)
    {

        $transaction_price = "select transaction_price from ecm_own_warehouse where user_id='$user_id' and goods_id=$goods_id";
        $transaction_price = $this->getone($transaction_price);
        if ($transaction_price === false) {
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
        return true;
        $get_user_count = "select * from ecm_own_warehouse where user_id='$user_id' and goods_id=$goods_id";
        $warehouse = $this->db->getrow($get_user_count);
        $goods_count = $warehouse['goods_count'] - $count;
        if ($warehouse['warehouse_id']) {
            $sql = "update ecm_own_warehouse set  goods_count='$goods_count' where warehouse_id='{$warehouse['warehouse_id']}'";
            $result = $this->db->query($sql);
            return $result;
        }
        die('业务异常，请检查！');
        return false;
    }

    /**
     * [insert_warehouse description]
     * @param  [type] $user_id  [description]
     * @param  [type] $goods_id [description]
     * @param  [type] $count    [description]
     * @param  [type] $price    [当前交易的产品单价]
     * @return [type]           [description]
     */
    function insert_warehouse($user_id, $goods_id, $count, $price)
    {


        $get_user_count = "select * from ecm_own_warehouse where user_id='$user_id' and goods_id=$goods_id";
        $warehouse = $this->db->getrow($get_user_count);
        if ($warehouse['warehouse_id']) {
            $goods_count = $warehouse['goods_count'] + $count;
            $transaction_price = intval($warehouse['transaction_price']) * 100 + intval($price * 100) * $count;
            $transaction_price = $transaction_price / 100;
            $sql = "update ecm_own_warehouse set  goods_count='$goods_count',transaction_price='$transaction_price' where warehouse_id='{$warehouse['warehouse_id']}'";
            $result = $this->db->query($sql);
            echo " $sql <br/>";
            $warehouse = $this->db->getrow($get_user_count);
            /* dump($warehouse);*/
            return $result;
        }
        $own_warehouse_mod =& m('own_warehouse');
        $own_data['user_id'] = $user_id;
        $own_data['goods_id'] = $goods_id;
        $own_data['goods_count'] = $count;
        $own_data['transaction_price'] = intval($price * 100) * $count / 100;
        $result = $own_warehouse_mod->add($own_data);
        return $result;
    }
    /**
     * [get_warehouse 获取库存明细]
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    function get_warehouse($user_id)
    {
        if (empty($this->db)) {
            $this->db = db();
        }
        $sql = "select a.*,b.goods_name,b.cate_id from ecm_own_warehouse as a inner join ecm_share_tea as b on a.goods_id=b.goods_id where a.user_id='$user_id' and a.goods_count<>0";
        $result = $this->db->getall($sql);
        if (empty($result)) {
            return array();
        }
        $str = date('Y-m-d', time());
        $begin_time = strtotime($str);
        $str = $str . ' 9:30';
        $end_time = strtotime($str);
        foreach ($result as $key => $goods) {
            //持仓均价
            $goods['transaction_price'] = $goods['transaction_price'] * 100;
            $goods['pre_price'] = $goods['transaction_price'] / $goods['goods_count'];
            $goods['pre_price'] = $goods['pre_price'] / 100;
            $result[$key]['pre_price'] = sprintf("%.2f", substr(sprintf("%.3f", $goods['pre_price']), 0, -1));

            //当前价格:今天9点半交易的第一单
            $get_cur_price = "select sum(transaction_price) as total_price,transaction_price from ecm_transaction_history where goods_id={$goods['goods_id']} and transaction_time>'$begin_time' and transaction_time<'$begin_time' ";
            $price_arr = $this->db->getcol($get_cur_price);
            $price_count = count($price_arr['transaction_price']);
            /*dump($price_count);*/
            if ($price_count) {
                $total_price = $price_arr['total_price'] * 100;
                $current_price = $total_price / $price_count;
                $current_price = $current_price / 100;
                $result[$key]['current_price'] = sprintf("%.2f", substr(sprintf("%.3f", $current_price), 0, -1));
            } else {
                /*$this->show_warrring('获取库存明细')*/
                //当前价格:是昨日的收盘价
                $arr = $this->_get_zhang_ting($goods['goods_id'], time());
                $result[$key]['current_price'] = $arr['shoupan'];

            }
            //实时浮动盈亏价格：  （当前价格*数量 -持仓金额）/持仓金额
            $current_price = $result[$key]['current_price'] * 100;
            /*dump($goods);*/
            $fdyk = ($current_price * $goods['goods_count'] - $goods['transaction_price']) / $goods['transaction_price'] * 100;
            /*dump($fdyk);*/
            $result[$key]['fdyk'] = sprintf("%.2f", substr(sprintf("%.3f", $fdyk), 0, -1));
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
    function get_cur_user_count($user_id, $goods_id)
    {
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
        if (empty($this->db)) {
            $this->db = db();
        }

        $get_count = "select count(*) from ecm_transaction_history  where goods_id='$goods_id'";
        $count = $this->db->getone($get_count);
        //只有一天或者一天都没有就取原始茶的价格
        if ($get_count < 2) {
            $sql = "select price from ecm_share_spec where goods_id='$goods_id'";
            $price = $this->db->getone($sql);
            $price = $price * 100;
            $out_data['zhangting'] = $price * 11 / 1000;
            $out_data['dieting'] = $price * 9 / 1000;
            $out_data['shoupan'] = $price / 100;
            return $out_data;
        }
        //格式化时间获取当前天的凌晨0点
        $sim_date = date("Y-m-d", $transaction_time);
        $end_time = strtotime($sim_date);
        //前一天的时间
        $get_pre_final = "select transaction_price from ecm_transaction_history where transaction_time<'$end_time' and transaction_have>0 order by history_id desc limit 1";
        $price = $this->db->getone($get_pre_final);
        $price = $price * 100;
        $out_data['zhangting'] = $price * 1.1 / 1000;
        $out_data['dieting'] = $price * 0.9 / 1000;
        $out_data['shoupan'] = $price / 100;
        return $out_data;
    }
}
