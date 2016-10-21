<?php echo $this->fetch('header.html'); ?>
<style type="text/css">


#rightCon {border-top: 1px solid #b0d3ee; padding: 10px 10px 60px; background: #fbfdff; margin-left: 2px; overflow: hidden;}
#rightCon dt {line-height: 30px; font-size: 14px; color: #09f; font-weight: bold; padding: 10px 40px 0;}
#rightCon dd {border-bottom: 1px solid #b0d3ee; line-height: 25px; padding: 0 40px 20px;}
#rightCon dd th {text-align: left; width:220px; color: #444;}
.td {width: 200px; padding-left: 20px;}
#rightCon .thbig{text-align: left; width: 100%; color: #444;font-size:12px; font-weight:normal}

</style>



<div id="rightTop">
<p>
   授权证书
</p>
</div>

<dl id="rightCon">

<dt>ECmall授权证书分为商业授权和非商业授权。</dt>
<dd>
    <table>
        <tr>
            <th class='thbig'><b>商业授权用户</b>是由上海商派授权中心颁发的ECMall多店商城系统商业运营授权许可证，经过认证后您将拥有商业授权用户身份，并且享有使用ECMall多店商城系统进行商业运营的合法权利。 适合所有正在使用或将要使用ECMall免费多店商城系统（免费下载体验） 的网商用户选择！</th>
        </tr>
        <tr>
            <th class='thbig'><b>非商业授权用户</b>是使用ECMall多店商城系统(免费版)的用户，仅供从 事学习究之用，不具备商业运作的合法性；如果未获取上海商派官 方授权而从事商业行为，上海商派保留对其使用系统停止升级、 关闭、甚至对其商业运作行为媒体曝光和追究法律责任的起诉权利。</th>
        </tr>
    </table>
</dd>



<dt>授权证书</dt>
<dd>
    <table>
        <tr>
            <th>授权证书号:</th>
            <td class="td"><?php echo $this->_var['license_number']; ?></td>
            <th>&nbsp</th>
            <td class="td"><?php if ($this->_var['is_license']): ?> 您已是商业授权用户<?php else: ?><a href="http://ecmall.shopex.cn" target="_blank">您是非商业授权用户</a><?php endif; ?></td>
        </tr>
    </table>
</dd>
<dt></dt>
<dd>
<form action="index.php?app=license&act=l_det"  method="post">
    <table width="712">
        <tr>
             <td width="425" >当您发现获取到的证书号有错误，请点击 <B>“重新获取”</B>按钮来恢复正确的证书。</td>
          <th width="114"><input type="submit" value="重新获取" name="Submit" class="formbtn" /></th>
        </tr>
        <tr>
             <td class="td"></td>
            <th></th>
        </tr>
    </table>
 </form>  
</dd>


</dl>
<?php echo $this->fetch('footer.html'); ?>
