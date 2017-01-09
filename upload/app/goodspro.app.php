<?php

/* 商品 */
class GoodsproApp extends StorebaseApp
{
    var $_goods_mod;
  

      /**
     * [produce_day_price 生成当天的日期价格]
     * @param  [type] $date [日期 y-m-d]
     * @return [type]       [description]
     */
    function index($date="",$goods_id=""){

        //1.获取到所有的商品的茶叶id
        $sql="select cate_id from ecm_gcategory where parent_id in (42,43,44)";
        $db=&db();
        $cate_arr=$db->getall($sql);

        foreach ($cate_arr as $key => $value) {
            $cate[]=$value['cate_id'];
        }
       $cate= implode(",",$cate);
       strchr($cate,-1);
       /* $sql="select a.goods_id,a.goods_name,a.price,a.cate_id from ecm_goods as a where a.cate_id in (42,43,44,92)";*/
       $sql="select a.goods_id,a.goods_name,a.price,a.cate_id from ecm_goods as a where a.cate_id in ($cate)";
        $cate_arr=$db->getall($sql);
        $days=31*6;
        $seria=date("Y-m-d");
        $time=strtotime("$seria");
        $time=$time-3600*24;
        $n=date("m");
        $n=$n-1;
        //判断有没有跨月
        $column="insert into ecm_drogue (goods_id,real_price,type,show_date) values";
         
        foreach ($cate_arr as $key => $value) {
            //组装sql
            $sql_value="";
       
                //$num++
               $ref_price_mt= mt_rand(-20,40);

               if (in_array($value['cate_id'], array(84,86,87,88,89,90,91,92,93,94,102,103,104,105,106,107,108,109,110,111))) {
                   //中期茶中 期茶2010年之前
                   $type=2;       
               }else if(in_array($value['cate_id'],array(95,96,97,98,99,112,113,114,115,116))) {
                   //新茶  新茶包含2010-2014
                   $type=1;                 
               }else{
                   $type=0;
                   # code...
               }
                echo "$type";
                $cha=$this->randomFloat($value['price']*(-0.015)+($value['price']*$n),$value['price']*0.025+($value['price']*$n)); 
                $cha=number_format($cha,2);
                $price=$value['price']+$cha;
                $sql_value .="('". $value['goods_id'] ."','" . $price ."','".$type."','". $time ."'),";               
            $sql_value2=$column . $sql_value;
            $sql_value2=substr($sql_value2, 0, -1);
            $sql_r[]=$sql_value2 ;
        }
        header("Content-Type:text/html;charset=utf-8");
        foreach ($sql_r as $key => $value) {
            $result=$db->query($value);
            if ($result) {
                echo "第$key 执行成功<br/>";
            }
        }
    }
    
    function randomFloat($min = 0, $max = 1) {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
}

?>
