<?php echo $this->fetch('header.html'); ?>

<div class="keyword">
    <div class="keyword1"></div>
    <div class="keyword2"></div>
    热门搜索:
    <?php $_from = $this->_var['hot_keywords']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'keyword');if (count($_from)):
    foreach ($_from AS $this->_var['keyword']):
?>
    <a href="<?php echo url('app=search&keyword=' . $this->_var['keyword']. ''); ?>"><?php echo $this->_var['keyword']; ?></a>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</div>

<div class="content">
    <div class="left" area="top_left" widget_type="area" >
        <?php $this->display_widgets(array('page'=>'index','area'=>'top_left')); ?>
    </div>

    <div class="right">
        <div class="main">
            <div id="module_middle" area="cycle_image" widget_type="area" >
                <?php $this->display_widgets(array('page'=>'index','area'=>'cycle_image')); ?>
            </div>

            <div class="sidebar" area="sales" widget_type="area">
                <?php $this->display_widgets(array('page'=>'index','area'=>'sales')); ?>
            </div>
        </div>

        <div area="top_right" widget_type="area">
            <?php $this->display_widgets(array('page'=>'index','area'=>'top_right')); ?>
        </div>

    </div>
  
</div>

<div class="clear"></div>
<div class="ad_banner" area="banner" widget_type="area">
    <?php $this->display_widgets(array('page'=>'index','area'=>'banner')); ?>
</div>
 
<style type="text/css">
    .sec_title {
        background: #f8f8f8 none repeat scroll 0 0;
        font-size: 13px;
        font-weight: bold;
        height: 33px;
        line-height: 33px;
    }
    .mb_line {
        border-bottom: 1px solid #e5e5e5;
    }
    div {
        margin: 0;
    }
    div {
        margin: 0 auto;
        padding: 0;
    }
    .sec_title a {
        color: #d20001;
        padding-left: 14px;
    }
    a:hover {
        color: #c00;
    }
    a {
        color: #333;
        text-decoration: none;
    }
    .sec_title {
        font-size: 13px;
        font-weight: bold;
        line-height: 33px;
    }
    #NewsDhPriceBox .dhbox {
        width: 1280px;
    }
    .DhPriceBox .dhbox {
        width: 985px;
    }
    div {
        margin: 0;
    }
    div {
        margin: 0 auto;
        padding: 0;
    }
    body {
        -moz-text-size-adjust: none;
        color: #333;
        font: 12px/150% Verdana,Arial,Tahoma;
    }


    .clearfix::after {
        clear: both;
        content: ".";
        display: block;
        height: 0;
        visibility: hidden;
    }
    #NewsDhPriceBox .dhbox .dh_info {
        margin-right: 11px;
        width: 394px;
    }
    .DhPriceBox .dhbox .dh_info {
        display: inline;
        height: 353px;
        margin-right: 10px;
        overflow: hidden;
        width: 310px;
    }
    .box_23 {
        border: 1px solid #e5e5e5;
        border-radius: 2px;
    }
    .clearfix {
    }
    .f_l {
        float: left;
    }
    div {
        margin: 0;
    }
    div {
        margin: 0 auto;
        padding: 0;
    }
    body {
        -moz-text-size-adjust: none;
        color: #333;
        font: 12px/150% Verdana,Arial,Tahoma;
    }
    #NewsDhPriceBox .dh_pbox li .tit {
        overflow: hidden;
        width: 150px;
    }
    .dh_pbox li .tit {
        overflow: hidden;
        width: 150px;
    }
    .dh_pbox li p {
        white-space: nowrap;
    }
    .f_l {
        float: left;
    }
    h1, h2, h3, h4, h5, h6, ul, li, dl, dt, dd, form, img, p {
        border: medium none;
        list-style-type: none;
        margin: 0;
        padding: 0;
    }
    .dh_pbox li {
        line-height: 24px;
    }
    h1, h2, h3, h4, h5, h6, ul, li, dl, dt, dd, form, img, p {
        list-style-type: none;
    }
    h1, h2, h3, h4, h5, h6, ul, li, dl, dt, dd, form, img, p {
        list-style-type: none;
    }
    body {
        -moz-text-size-adjust: none;
        color: #333;
        font: 12px/150% Verdana,Arial,Tahoma;
    }
    #NewsDhPriceBox .dh_pbox li .price {
        overflow: hidden;
        width: 85px;
    }
    .dh_pbox li .price {
        overflow: hidden;
        width: 75px;
    }
    .dh_pbox li p {
        white-space: nowrap;
    }
    .f_l {
        float: left;
    }
    h1, h2, h3, h4, h5, h6, ul, li, dl, dt, dd, form, img, p {
        border: medium none;
        list-style-type: none;
        margin: 0;
        padding: 0;
    }
    .dh_pbox li {
        line-height: 24px;
    }
    h1, h2, h3, h4, h5, h6, ul, li, dl, dt, dd, form, img, p {
        list-style-type: none;
    }
    h1, h2, h3, h4, h5, h6, ul, li, dl, dt, dd, form, img, p {
        list-style-type: none;
    }
    #NewsDhPriceBox .dh_pbox li .u_price {
        overflow: hidden;
        width: 80px;
    }
    .dh_pbox li .u_price {
        overflow: hidden;
        width: 72px;
    }
    .dh_pbox li p {
        white-space: nowrap;
    }
    .f_l {
        float: left;
    }
    h1, h2, h3, h4, h5, h6, ul, li, dl, dt, dd, form, img, p {
        border: medium none;
        list-style-type: none;
        margin: 0;
        padding: 0;
    }
    .dh_pbox li {
        line-height: 24px;
    }
    h1, h2, h3, h4, h5, h6, ul, li, dl, dt, dd, form, img, p {
        list-style-type: none;
    }

    .dh_pbox li a:hover {
        color: #ff0000;
        text-decoration: underline;
    }
    .dh_pbox li a {
        color: #333;
        text-decoration: none;
    }
    a:hover {
        color: #c00;
    }
    a {
        color: #333;
        text-decoration: none;
    }
    .dh_pbox li p {
        white-space: nowrap;
    }
    h1, h2, h3, h4, h5, h6, ul, li, dl, dt, dd, form, img, p {
        list-style-type: none;
    }


    #indexbox .DhPriceBox {
        height: 400px;
        overflow: hidden;
    }
    #NewsDhPriceBox {
        overflow: hidden;
        width: 1210px;
    }
    .dhnews, .DhPriceBox {
        background: rgba(0, 0, 0, 0) url("../images/lines.jpg") repeat-x scroll left 32px;
    }
    .DhPriceBox {
        overflow: hidden;
        width: 948px;
    }
    div {
        margin: 0;
    }
    div {
        margin: 0 auto;
        padding: 0;
    }

    #NewsDhPriceBox .dh_pbox li .price {
        overflow: hidden;
        width: 88px;
    }
    .dh_pbox li .price {
        overflow: hidden;
        width: 75px;
    }
    .dh_pbox li p {
        white-space: nowrap;
    }
    .f_l {
        float: left;
    }
    h1, h2, h3, h4, h5, h6, ul, li, dl, dt, dd, form, img, p {
        border: medium none;
        }


    .dh_pbox li a {
        color: #333;
        text-decoration: none;
    }
    a {
        color: #333;
        text-decoration: none;
    }
    .dh_pbox li p {
        white-space: nowrap;
    }
    h1, h2, h3, h4, h5, h6, ul, li, dl, dt, dd, form, img, p {
        list-style-type: none;
    }
    .dh_pbox li {
        line-height: 24px;
    }
    h1, h2, h3, h4, h5, h6, ul, li, dl, dt, dd, form, img, p {
        list-style-type: none;
    }
    h1, h2, h3, h4, h5, h6, ul, li, dl, dt, dd, form, img, p {
        list-style-type: none;
    }
    body {
        -moz-text-size-adjust: none;
        color: #333;
        font: 12px/150% Verdana,Arial,Tahoma;
    }
    .dh_pbox li .tit {
        overflow: hidden;
        width: 84px;
        overflow: hidden; /*自动隐藏文字*/
        text-overflow: ellipsis;/*文字隐藏后添加省略号*/
        white-space: nowrap;/*强制不换行*/ 
    }
    .dh_pbox li p {
        white-space: nowrap;
    }
    .f_l {  
        float: left;
    }

    .dh_info {
      margin-right: 20px;

    }
    #NewsDhPriceBox .dhbox .dh_pbox {
        width: 370px;
    }
    #scrollDiv, #scrollDiv1, #scrollDiv2 {
        clear: both;
        height: 320px;
        overflow: hidden;
        width: 284px;
    }
    .DhPriceBox .dhbox .dh_pbox {
        width: 284px;
    }
    div {
        margin: 0;
    }
    div {
        margin: 0 auto;
        padding: 0;
    }


    span.red {
        color: #e60012;
        font-family: Verdana,Geneva,sans-serif;
    }

    span.green {
        color: #009944;
        font-family: Verdana,Geneva,sans-serif;
    }

    .tit{
      width: 200px;

    }  
.DhPriceBox span.titlel {
   
    font-family: "微软雅黑",arail;
    font-size: 18px;
    font-weight: bold;
    height: 35px;
    left: 0;
    line-height: 29px;
    padding-left: 2px;
  
    top: 0;
}

.DhPriceBox span.en {
    color: #d20001;

    font-family: Verdana,Geneva,sans-serif;
    font-size: 11px;
    font-weight: bold;
    left: 85px;
   
    top: 1px;
}

.blank {
    clear: both;
    font-size: 0;
    height: 10px;
    line-height: 10px;
}

</style>
<div class="content">
    <div id="my_div" style="margin:10px auto;border:4px solid #ECDB69">
        
    <div class="DhPriceBox">
<div class="title"><span class="titlel">茶叶风向标</span><span class="en">&nbsp;&nbsp;TEA OF DROGUE</span><span class="more"><a href="category.php?id=36">&nbsp;&nbsp;更多&gt;&gt;</a></span></div>
  <div class="blank"></div>
      <div class="dhbox">
   <div class="dh_info f_l box_23 clearfix">
      <div class="sec_title mb_line"><a href="category.php?id=5">当年茶</a></div>
      <div class="dh_pbox dh_title">
        <ul>
         <li>
            <p class="tit f_l">品名</p>
            <p class="price f_l">参考价</p>
            <p class="u_price f_l">升跌</p>
         </li> 
       </ul>
      </div>
      <div id="scrollDiv" class="dh_pbox">
        <ul style="margin-top: 0px;" id="scrollDiv_ul">          
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1531" target="_blank" title="2016年 老班章">2016年 老班章</a></p>
            <p class="price f_l">￥53000</p>
            <p class="u_price f_l"><span class="green">降￥500</span></p>
            <p class="u_rate f_l"><span class="green">↓0.9%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1355" target="_blank" title="1501 古韵传芳">1501 古韵传芳</a></p>
            <p class="price f_l">￥3600</p>
            <p class="u_price f_l"><span class="green">降￥50</span></p>
            <p class="u_rate f_l"><span class="green">↓1.4%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1357" target="_blank" title="1501 陈韵青饼">1501 陈韵青饼</a></p>
            <p class="price f_l">￥3100</p>
            <p class="u_price f_l"><span class="green">降￥50</span></p>
            <p class="u_rate f_l"><span class="green">↓1.6%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1530" target="_blank" title="1601 高山韵象（生）">1601 高山韵象（生）</a></p>
            <p class="price f_l">￥3100</p>
            <p class="u_price f_l"><span class="red">升￥50</span></p>
            <p class="u_rate f_l"><span class="red">↑1.6%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1421" target="_blank" title="1501 大益传奇">1501 大益传奇</a></p>
            <p class="price f_l">￥5150</p>
            <p class="u_price f_l"><span class="green">降￥50</span></p>
            <p class="u_rate f_l"><span class="green">↓1%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1500" target="_blank" title="1601 美猴乾坤【“悟空”饼】">1601 美猴乾坤【“悟...</a></p>
            <p class="price f_l">￥2400</p>
            <p class="u_price f_l"><span class="green">降￥50</span></p>
            <p class="u_rate f_l"><span class="green">↓2%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1510" target="_blank" title="1601 龙生甘露">1601 龙生甘露</a></p>
            <p class="price f_l">￥3200</p>
            <p class="u_price f_l"><span class="green">降￥50</span></p>
            <p class="u_rate f_l"><span class="green">↓1.5%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1462" target="_blank" title="1501 7742">1501 7742</a></p>
            <p class="price f_l">￥7000</p>
            <p class="u_price f_l"><span class="green">降￥100</span></p>
            <p class="u_rate f_l"><span class="green">↓1.4%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1515" target="_blank" title="1501 7572">1501 7572</a></p>
            <p class="price f_l">￥1650</p>
            <p class="u_price f_l"><span class="red">升￥50</span></p>
            <p class="u_rate f_l"><span class="red">↑3.1%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1532" target="_blank" title="1601 7752">1601 7752</a></p>
            <p class="price f_l">￥1400</p>
            <p class="u_price f_l"><span class="green">降￥100</span></p>
            <p class="u_rate f_l"><span class="green">↓6.7%</span></p>
          </li>
                 <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1498" target="_blank" title="1601 美猴乾坤【经典珍藏版】">1601 美猴乾坤【经典...</a></p>
            <p class="price f_l">￥4650</p>
            <p class="u_price f_l"><span class="green">降￥50</span></p>
            <p class="u_rate f_l"><span class="green">↓1.1%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1538" target="_blank" title="1601 老树圆茶生饼">1601 老树圆茶生饼</a></p>
            <p class="price f_l">￥14800</p>
            <p class="u_price f_l"><span class="green">降￥200</span></p>
            <p class="u_rate f_l"><span class="green">↓1.3%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1546" target="_blank" title="1601 群峰之上">1601 群峰之上</a></p>
            <p class="price f_l">￥11200</p>
            <p class="u_price f_l"><span class="green">降￥200</span></p>
            <p class="u_rate f_l"><span class="green">↓1.8%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1551" target="_blank" title="1601 玫瑰大益">1601 玫瑰大益</a></p>
            <p class="price f_l">￥5050</p>
            <p class="u_price f_l"><span class="red">升￥50</span></p>
            <p class="u_rate f_l"><span class="red">↑1%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1566" target="_blank" title="1601 珍藏孔雀">1601 珍藏孔雀</a></p>
            <p class="price f_l">￥19300</p>
            <p class="u_price f_l"><span class="green">降￥200</span></p>
            <p class="u_rate f_l"><span class="green">↓1%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1571" target="_blank" title="1601 春秋（生熟一套）">1601 春秋（生熟一套...</a></p>
            <p class="price f_l">￥22500</p>
            <p class="u_price f_l"><span class="green">降￥1700</span></p>
            <p class="u_rate f_l"><span class="green">↓7%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1250" target="_blank" title="1501 善美祥羊">1501 善美祥羊</a></p>
            <p class="price f_l">￥4000</p>
            <p class="u_price f_l"><span class="green">降￥50</span></p>
            <p class="u_rate f_l"><span class="green">↓1.2%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1459" target="_blank" title="1501 南糯生态青饼">1501 南糯生态青饼</a></p>
            <p class="price f_l">￥3800</p>
            <p class="u_price f_l"><span class="green">降￥100</span></p>
            <p class="u_rate f_l"><span class="green">↓2.6%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1523" target="_blank" title="1601 7542">1601 7542</a></p>
            <p class="price f_l">￥5200</p>
            <p class="u_price f_l"><span class="red">升￥50</span></p>
            <p class="u_rate f_l"><span class="red">↑1%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1287" target="_blank" title="1501 紫大益">1501 紫大益</a></p>
            <p class="price f_l">￥11000</p>
            <p class="u_price f_l"><span class="green">降￥200</span></p>
            <p class="u_rate f_l"><span class="green">↓1.8%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1262" target="_blank" title="1501 大吉象山">1501 大吉象山</a></p>
            <p class="price f_l">￥2100</p>
            <p class="u_price f_l"><span class="green">降￥100</span></p>
            <p class="u_rate f_l"><span class="green">↓4.5%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1511" target="_blank" title="1601 云水臻">1601 云水臻</a></p>
            <p class="price f_l">￥2300</p>
            <p class="u_price f_l"><span class="red">升￥50</span></p>
            <p class="u_rate f_l"><span class="red">↑2.2%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1499" target="_blank" title="1601 美猴乾坤【“猴王”特藏版】">1601 美猴乾坤【“猴...</a></p>
            <p class="price f_l">￥3050</p>
            <p class="u_price f_l"><span class="black">￥0</span></p>
            <p class="u_rate f_l"><span class="black">0%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1517" target="_blank" title="1601 7452">1601 7452</a></p>
            <p class="price f_l">￥2000</p>
            <p class="u_price f_l"><span class="red">升￥50</span></p>
            <p class="u_rate f_l"><span class="red">↑2.6%</span></p>
          </li></ul>
      </div>
   </div>
   <div class="dh_info box_23  f_l clearfix">
     <div class="sec_title mb_line"><a href="category.php?id=6">新茶</a></div>
     <div class="dh_pbox dh_title">
        <ul>
         <li>
            <p class="tit f_l">品名</p>
            <p class="price f_l">参考价</p>
            <p class="u_price f_l">升跌</p>
         </li> 
       </ul>
      </div>
      <div id="scrollDiv1" class="dh_pbox">
        <ul style="margin-top: 0px;" id="scrollDiv1_ul">                    
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=121" target="_blank" title="906 7572">906 7572</a></p>
            <p class="price f_l">￥4450</p>
            <p class="u_price f_l"><span class="green">降￥50</span></p>
            <p class="u_rate f_l"><span class="green">↓1.1%</span></p>
          </li>
          <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=584" target="_blank" title="901 勐海之星">901 勐海之星</a></p>
            <p class="price f_l">￥9900</p>
            <p class="u_price f_l"><span class="green">降￥100</span></p>
            <p class="u_rate f_l"><span class="green">↓1%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=167" target="_blank" title="001 亚运珍藏饼">001 亚运珍藏饼</a></p>
            <p class="price f_l">￥7500</p>
            <p class="u_price f_l"><span class="green">降￥100</span></p>
            <p class="u_rate f_l"><span class="green">↓1.3%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=195" target="_blank" title="001 黄金岁月(熟)">001 黄金岁月(熟)</a></p>
            <p class="price f_l">￥8600</p>
            <p class="u_price f_l"><span class="green">降￥100</span></p>
            <p class="u_rate f_l"><span class="green">↓1.1%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=294" target="_blank" title="001 春早·超级早春女儿茶">001 春早·超级早春女...</a></p>
            <p class="price f_l">￥3100</p>
            <p class="u_price f_l"><span class="green">降￥400</span></p>
            <p class="u_rate f_l"><span class="green">↓11.4%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=722" target="_blank" title="001 瑞虎呈祥(2、3号)">001 瑞虎呈祥(2、3...</a></p>
            <p class="price f_l">￥18000</p>
            <p class="u_price f_l"><span class="green">降￥500</span></p>
            <p class="u_rate f_l"><span class="green">↓2.7%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1113" target="_blank" title="901 大益红">901 大益红</a></p>
            <p class="price f_l">￥9500</p>
            <p class="u_price f_l"><span class="green">降￥300</span></p>
            <p class="u_rate f_l"><span class="green">↓3.1%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=120" target="_blank" title="001 瑞虎呈祥(1号)">001 瑞虎呈祥(1号)</a></p>
            <p class="price f_l">￥23000</p>
            <p class="u_price f_l"><span class="green">降￥500</span></p>
            <p class="u_rate f_l"><span class="green">↓2.1%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=734" target="_blank" title="101 辛亥革命大套">101 辛亥革命大套</a></p>
            <p class="price f_l">￥9000</p>
            <p class="u_price f_l"><span class="green">降￥100</span></p>
            <p class="u_rate f_l"><span class="green">↓1.1%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=216" target="_blank" title="201 金针白莲">201 金针白莲</a></p>
            <p class="price f_l">￥6200</p>
            <p class="u_price f_l"><span class="green">降￥100</span></p>
            <p class="u_rate f_l"><span class="green">↓1.6%</span></p>
          </li>
                 <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=207" target="_blank" title="101 大益醇">101 大益醇</a></p>
            <p class="price f_l">￥4800</p>
            <p class="u_price f_l"><span class="green">降￥200</span></p>
            <p class="u_rate f_l"><span class="green">↓4%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1092" target="_blank" title="1301 金针白莲(散茶)">1301 金针白莲(散茶...</a></p>
            <p class="price f_l">￥2500</p>
            <p class="u_price f_l"><span class="red">升￥50</span></p>
            <p class="u_rate f_l"><span class="red">↑2%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1152" target="_blank" title="1301 小龙柱">1301 小龙柱</a></p>
            <p class="price f_l">￥3450</p>
            <p class="u_price f_l"><span class="red">升￥50</span></p>
            <p class="u_rate f_l"><span class="red">↑1.5%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=168" target="_blank" title="701 7542">701 7542</a></p>
            <p class="price f_l">￥19900</p>
            <p class="u_price f_l"><span class="green">降￥100</span></p>
            <p class="u_rate f_l"><span class="green">↓0.5%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=209" target="_blank" title="701 7572">701 7572</a></p>
            <p class="price f_l">￥10500</p>
            <p class="u_price f_l"><span class="green">降￥300</span></p>
            <p class="u_rate f_l"><span class="green">↓2.8%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=492" target="_blank" title="701 宫廷普饼">701 宫廷普饼</a></p>
            <p class="price f_l">￥4400</p>
            <p class="u_price f_l"><span class="red">升￥200</span></p>
            <p class="u_rate f_l"><span class="red">↑4.8%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=679" target="_blank" title="08年 五只孔雀">08年 五只孔雀</a></p>
            <p class="price f_l">￥120000</p>
            <p class="u_price f_l"><span class="green">降￥2000</span></p>
            <p class="u_rate f_l"><span class="green">↓1.6%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=691" target="_blank" title="801 陈年谷花(秋香)">801 陈年谷花(秋香)</a></p>
            <p class="price f_l">￥6800</p>
            <p class="u_price f_l"><span class="green">降￥200</span></p>
            <p class="u_rate f_l"><span class="green">↓2.9%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1114" target="_blank" title="801 大益红生茶">801 大益红生茶</a></p>
            <p class="price f_l">￥9000</p>
            <p class="u_price f_l"><span class="green">降￥400</span></p>
            <p class="u_rate f_l"><span class="green">↓4.3%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1141" target="_blank" title="1401 易武正山">1401 易武正山</a></p>
            <p class="price f_l">￥7500</p>
            <p class="u_price f_l"><span class="green">降￥100</span></p>
            <p class="u_rate f_l"><span class="green">↓1.3%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1174" target="_blank" title="1401 经典红丝带7432">1401 经典红丝带74...</a></p>
            <p class="price f_l">￥3200</p>
            <p class="u_price f_l"><span class="green">降￥50</span></p>
            <p class="u_rate f_l"><span class="green">↓1.5%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1211" target="_blank" title="1401 勐海孔雀">1401 勐海孔雀</a></p>
            <p class="price f_l">￥8000</p>
            <p class="u_price f_l"><span class="green">降￥50</span></p>
            <p class="u_rate f_l"><span class="green">↓0.6%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=95" target="_blank" title="905 7572">905 7572</a></p>
            <p class="price f_l">￥4450</p>
            <p class="u_price f_l"><span class="green">降￥50</span></p>
            <p class="u_rate f_l"><span class="green">↓1.1%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=108" target="_blank" title="901 高枕无忧厚砖">901 高枕无忧厚砖</a></p>
            <p class="price f_l">￥8000</p>
            <p class="u_price f_l"><span class="green">降￥100</span></p>
            <p class="u_rate f_l"><span class="green">↓1.2%</span></p>
          </li></ul>
      </div>
   </div>  
   <div class="dh_info box_23 f_l clearfix">
     <div class="sec_title mb_line"><a href="category.php?id=7">中期茶</a></div> 
     <div class="dh_pbox dh_title">
        <ul>
         <li>
            <p class="tit f_l">品名</p>
            <p class="price f_l">参考价</p>
            <p class="u_price f_l">升跌</p>
         </li> 
       </ul>
      </div>
      <div id="scrollDiv2" class="dh_pbox">
        <ul style="margin-top: 0px;" id="scrollDiv2_ul">                 
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=418" target="_blank" title="2004年 易武山明前春尖饼">2004年 易武山明前春...</a></p>
            <p class="price f_l">￥75000</p>
            <p class="u_price f_l"><span class="green">降￥2000</span></p>
            <p class="u_rate f_l"><span class="green">↓2.6%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=499" target="_blank" title="401 红印高级沱200克">401 红印高级沱200...</a></p>
            <p class="price f_l">￥55000</p>
            <p class="u_price f_l"><span class="green">降￥2000</span></p>
            <p class="u_rate f_l"><span class="green">↓3.5%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1334" target="_blank" title="2004年 班章贡青饼">2004年 班章贡青饼</a></p>
            <p class="price f_l">￥365000</p>
            <p class="u_price f_l"><span class="red">升￥5000</span></p>
            <p class="u_rate f_l"><span class="red">↑1.4%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=663" target="_blank" title="301 绿色生态青饼357克">301 绿色生态青饼35...</a></p>
            <p class="price f_l">￥133000</p>
            <p class="u_price f_l"><span class="green">降￥5000</span></p>
            <p class="u_rate f_l"><span class="green">↓3.6%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=612" target="_blank" title="309 紫大益7572">309 紫大益7572</a></p>
            <p class="price f_l">￥58800</p>
            <p class="u_price f_l"><span class="red">升￥800</span></p>
            <p class="u_rate f_l"><span class="red">↑1.4%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=267" target="_blank" title="2003年 建州50周年纪念饼青饼">2003年 建州50周年...</a></p>
            <p class="price f_l">￥41000</p>
            <p class="u_price f_l"><span class="red">升￥1000</span></p>
            <p class="u_rate f_l"><span class="red">↑2.5%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=263" target="_blank" title="301 7222">301 7222</a></p>
            <p class="price f_l">￥210000</p>
            <p class="u_price f_l"><span class="red">升￥2000</span></p>
            <p class="u_rate f_l"><span class="red">↑1%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1281" target="_blank" title="小2易武正山野生茶特级品">小2易武正山野生茶特级品</a></p>
            <p class="price f_l">￥380000</p>
            <p class="u_price f_l"><span class="red">升￥42000</span></p>
            <p class="u_rate f_l"><span class="red">↑12.4%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=130" target="_blank" title="501 8542 ">501 8542</a></p>
            <p class="price f_l">￥32000</p>
            <p class="u_price f_l"><span class="green">降￥1000</span></p>
            <p class="u_rate f_l"><span class="green">↓3%</span></p>
          </li>
                    <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=234" target="_blank" title="503 8542 ">503 8542</a></p>
            <p class="price f_l">￥24000</p>
            <p class="u_price f_l"><span class="green">降￥500</span></p>
            <p class="u_rate f_l"><span class="green">↓2%</span></p>
          </li>
                 <li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=324" target="_blank" title="2001年 紫大益4号">2001年 紫大益4号</a></p>
            <p class="price f_l">￥260000</p>
            <p class="u_price f_l"><span class="red">升￥2000</span></p>
            <p class="u_rate f_l"><span class="red">↑0.8%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=290" target="_blank" title="2002年 易武正山一棵树">2002年 易武正山一棵...</a></p>
            <p class="price f_l">￥280000</p>
            <p class="u_price f_l"><span class="red">升￥4000</span></p>
            <p class="u_rate f_l"><span class="red">↑1.4%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=264" target="_blank" title="2003年 301 易武正山野生茶特级品(绿大树)">2003年 301 易武...</a></p>
            <p class="price f_l">￥285000</p>
            <p class="u_price f_l"><span class="red">升￥5000</span></p>
            <p class="u_rate f_l"><span class="red">↑1.8%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=439" target="_blank" title="2003年 301 银大益">2003年 301 银大...</a></p>
            <p class="price f_l">￥260000</p>
            <p class="u_price f_l"><span class="green">降￥5000</span></p>
            <p class="u_rate f_l"><span class="green">↓1.9%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=445" target="_blank" title="2003年 301 黄大益青饼">2003年 301 黄大...</a></p>
            <p class="price f_l">￥370000</p>
            <p class="u_price f_l"><span class="green">降￥5000</span></p>
            <p class="u_rate f_l"><span class="green">↓1.3%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=638" target="_blank" title="2003年 大2易武正山野生茶特级品">2003年 大2易武正山...</a></p>
            <p class="price f_l">￥500000</p>
            <p class="u_price f_l"><span class="red">升￥10000</span></p>
            <p class="u_rate f_l"><span class="red">↑2%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1294" target="_blank" title="301 乔木老树易武500克">301 乔木老树易武50...</a></p>
            <p class="price f_l">￥290000</p>
            <p class="u_price f_l"><span class="red">升￥5000</span></p>
            <p class="u_rate f_l"><span class="red">↑1.8%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=401" target="_blank" title="401 7522">401 7522</a></p>
            <p class="price f_l">￥180000</p>
            <p class="u_price f_l"><span class="green">降￥3000</span></p>
            <p class="u_rate f_l"><span class="green">↓1.6%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1258" target="_blank" title="2004年 博字7572">2004年 博字7572</a></p>
            <p class="price f_l">￥30500</p>
            <p class="u_price f_l"><span class="green">降￥500</span></p>
            <p class="u_rate f_l"><span class="green">↓1.6%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=1327" target="_blank" title="2004年 甲级熟沱100克">2004年 甲级熟沱10...</a></p>
            <p class="price f_l">￥18300</p>
            <p class="u_price f_l"><span class="red">升￥300</span></p>
            <p class="u_rate f_l"><span class="red">↑1.7%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=186" target="_blank" title="601 班章有机普洱茶">601 班章有机普洱茶</a></p>
            <p class="price f_l">￥170000</p>
            <p class="u_price f_l"><span class="red">升￥2000</span></p>
            <p class="u_rate f_l"><span class="red">↑1.2%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=281" target="_blank" title="302 银大益">302 银大益</a></p>
            <p class="price f_l">￥178000</p>
            <p class="u_price f_l"><span class="green">降￥2000</span></p>
            <p class="u_rate f_l"><span class="green">↓1.1%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=406" target="_blank" title="2004年 孔雀班章贡青饼">2004年 孔雀班章贡青...</a></p>
            <p class="price f_l">￥363000</p>
            <p class="u_price f_l"><span class="red">升￥3000</span></p>
            <p class="u_rate f_l"><span class="red">↑0.8%</span></p>
          </li><li> 
            <p class="tit f_l"><a href="http://fulucang.cc/goods.php?id=408" target="_blank" title="401 8582">401 8582</a></p>
            <p class="price f_l">￥98000</p>
            <p class="u_price f_l"><span class="green">降￥2000</span></p>
            <p class="u_rate f_l"><span class="green">↓2%</span></p>
          </li></ul>
      </div>
   </div> 
   <div class="blank"></div>
  </div>
<script type="text/javascript">
    scrollDivObj=document.getElementById("scrollDiv_ul");  
    countNum=0;
    var stoping;
    var test="";
    stoping=setInterval("moveDivObj(scrollDivObj)",2000);
    function moveDivObj(scrollDivObj){
        countNum--;
        countNum-=24;
        //获取li的个数
        var li_count=scrollDivObj.getElementsByTagName('li').length;
        countNumS=countNum*-1;
        if (countNumS>=(li_count-2)*12) {
            scrollDivObj.style.marginTop=0+"px";
            countNum=0;
            clearInterval(stoping);
            setTimeout(function() { 
            stoping=setInterval("moveDivObj(scrollDivObj)",2000);
            }, 5000); 
        }
        scrollDivObj.style.marginTop=countNum+"px";
        //alert(cc);
    }
    /* //鼠标放上事件监听
    scrollDivObj.onmouseover=function(){
    clearInterval(stoping);
    }*/
    scrollDiv1_ul=document.getElementById("scrollDiv1_ul");
    setTimeout(function(){stoping1=setInterval("moveDivObj1(scrollDiv1_ul)",2000)}, 3000);
    function moveDivObj1(scrollDiv1_ul){
    countNum--;
    countNum-=24;
    //获取li的个数
    var li_count=scrollDiv1_ul.getElementsByTagName('li').length;
    countNumS=countNum*-1;
    if (countNumS>=(li_count-2)*12) {
        scrollDiv1_ul.style.marginTop=0+"px";
        countNum=0;
        clearInterval(stoping1);
        setTimeout(function() {
        stoping1=setInterval("moveDivObj1(scrollDiv1_ul)",2000)
        }, 5000); 
    }
    scrollDiv1_ul.style.marginTop=countNum+"px";
    //alert(cc);
    }

    scrollDiv2_ul=document.getElementById("scrollDiv2_ul");
    setTimeout(function(){stoping2=setInterval("moveDivObj2(scrollDiv2_ul)",2000)},5000);
    function moveDivObj2(scrollDiv2_ul){
        countNum--;
        countNum-=24;
        //获取li的个数
        var li_count=scrollDiv2_ul.getElementsByTagName('li').length;
        countNumS=countNum*-1;
        if (countNumS>=(li_count-2)*12) {
            scrollDiv2_ul.style.marginTop=0+"px";
            countNum=0;
            clearInterval(stoping2);
            setTimeout(function() {
            stoping2=setInterval("moveDivObj2(scrollDiv2_ul)",2000)
            }, 5000); 
        }
        scrollDiv2_ul.style.marginTop=countNum+"px";
        //alert(cc);
  }
</script>
    </div>



         
       
        
    </div>
    <div class="left" area="bottom_left" widget_type="area">
        <?php $this->display_widgets(array('page'=>'index','area'=>'bottom_left')); ?>
    </div>

    <div class="right" widget_type="area" area="bottom_right">
        <?php $this->display_widgets(array('page'=>'index','area'=>'bottom_right')); ?>
    </div>
</div>

<div class="clear"></div>
<div class="content" area="bottom_down" widget_type="area">
    <?php $this->display_widgets(array('page'=>'index','area'=>'bottom_down')); ?>
</div>

<?php echo $this->fetch('footer.html'); ?>