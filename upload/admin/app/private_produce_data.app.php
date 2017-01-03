<?php
/**
 *    商品管理控制器
 */
class Private_produce_dataApp extends BackendApp
{
    var $_goods_mod;

    function __construct()
    {
        $this->GoodsApp();
    }
    function GoodsApp()
    {
        parent::BackendApp();

        $this->_goods_mod =& m('goods');
    }
    /**
     * [produce_history_data 产生以往的历史所有数据]
     * @return [type] [description]
     */
    function produce_history_data(){
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
       //当年茶2015   新茶包含2010-2014   中期茶2010年之前
        //var_dump($cate_arr);
            /*        array(90) {
              [0]=>
              array(3) {
                ["goods_id"]=>
                string(3) "480"
                ["goods_name"]=>
                string(19) "2007勐海早春饼"
                ["price"]=>
                string(6) "330.00"
              }
              [1]=>
              array(3) {
                ["goods_id"]=>
                string(1) "7"
                ["goods_name"]=>
                string(19) "糯香小金沱.生"
                ["price"]=>
                string(5) "35.00"
              }*/
               //1.获取需要插入的次数
            $days=31*6;
           
        $time=strtotime("2016-01-01");
        $column="insert into ecm_drogue (goods_id,real_price,type,show_date) values";
         
        foreach ($cate_arr as $key => $value) {
            //组装sql
            $sql_value="";
            $num=0;
            for ($i=$time; $i < time(); ) { 
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
                //按月划分价格
                if ($num<62) {      
                    $cha=$this->randomFloat($value['price']*0.03,$value['price']*0.05);
                    //echo "A$cha";
                }else if ($num<62&&$num<124) {
                    $cha=$this->randomFloat($value['price']*0.02,$value['price']*0.04);
                   //echo "B$cha";
                }else{
                    $cha=$this->randomFloat($value['price']*0,$value['price']*0.025); 
                   // echo "C$cha";
                }
                $cha=number_format($cha,2);
                $price=$value['price']-$cha;
                $sql_value .="('". $value['goods_id'] ."','" . $price ."','".$type."','". $i ."'),";               
               /* $ta=date("Y-m-d",$i);
                echo "$ta =>".$value['price'] ."<br/>"; */  
                //$i是时间戳
                $num++;
                $i+=24*3600;
            }
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
    /**
     * [produce_day_price 生成当天的日期价格]
     * @param  [type] $date [日期 y-m-d]
     * @return [type]       [description]
     */
    function produce_day_price($date="",$goods_id=""){

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
       //当年茶2015   新茶包含2010-2014   中期茶2010年之前
        //var_dump($cate_arr);
            /*        array(90) {
              [0]=>
              array(3) {
                ["goods_id"]=>
                string(3) "480"
                ["goods_name"]=>
                string(19) "2007勐海早春饼"
                ["price"]=>
                string(6) "330.00"
              }
              [1]=>
              array(3) {
                ["goods_id"]=>
                string(1) "7"
                ["goods_name"]=>
                string(19) "糯香小金沱.生"
                ["price"]=>
                string(5) "35.00"
              }*/
               //1.获取需要插入的次数
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
            for ($i=$time; $i < time(); ) { 
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
                //按月划分价格
               /* if ($num<62) {      
                    $cha=$this->randomFloat($value['price']*0.03,$value['price']*0.05);
                    //echo "A$cha";
                }else if ($num<62&&$num<124) {
                    $cha=$this->randomFloat($value['price']*0.02,$value['price']*0.04);
                   //echo "B$cha";
                }else{
                    $cha=$this->randomFloat($value['price']*0,$value['price']*0.025); 
                   // echo "C$cha";
                }*/
                $cha=$this->randomFloat($value['price']*(-0.015)+($value['price']*$n),$value['price']*0.025+($value['price']*$n)); 
                $cha=number_format($cha,2);
                $price=$value['price']+$cha;
                $sql_value .="('". $value['goods_id'] ."','" . $price ."','".$type."','". $i ."'),";               
                /* $ta=date("Y-m-d",$i);
                echo "$ta =>".$value['price'] ."<br/>"; */  
                //$i是时间戳
                $i+=24*3600;
            }
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

}