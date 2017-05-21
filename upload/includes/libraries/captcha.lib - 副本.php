<?php
/**
 * ECMALL: 验证码类
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: captcha.lib.php 7840 2009-05-21 06:14:05Z lizhaosheng $
 */
if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}
/**
 * 用例如下
 */

//验证码工具类
class Captcha{
    //属性
    private $width;
    private $height;
    private $fontsize;
    private $pixes;
    private $lines;
    private $str_len;
    /*
     * 构造方法
     * @param1 array $arr = array()，初始化属性的关联数组
    */
    public function __construct($arr = array()){
      //初始化
      $this->width = isset($arr['width']) ? $arr['width'] : $GLOBALS['config']['captcha']['width'];
      $this->height = isset($arr['height']) ? $arr['height'] : $GLOBALS['config']['captcha']['height'];
      $this->fontsize = isset($arr['fontsize']) ? $arr['fontsize'] : $GLOBALS['config']['captcha']['fontsize'];
      $this->pixes = isset($arr['pixes']) ? $arr['pixes'] : $GLOBALS['config']['captcha']['pixes'];
      $this->lines = isset($arr['lines']) ? $arr['lines'] : $GLOBALS['config']['captcha']['lines'];
      $this->str_len = isset($arr['str_len']) ? $arr['str_len'] : $GLOBALS['config']['captcha']['str_len'];
    }
    /*
     * 产生验证码图片
    */
    public function generate(){
      //制作画布
      $img = imagecreatetruecolor($this->width,$this->height);
      //给定背景色
      $bg_color = imagecolorallocate($img,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
      imagefill($img,0,0,$bg_color);
      //制作干扰线
      $this->getLines($img);
      //增加干扰点
      $this->getPixels($img);
      //增加验证码文字
      $captcha = $this->getCaptcha();
      //文字颜色
      $str_color = imagecolorallocate($img,mt_rand(0,100),mt_rand(0,100),mt_rand(0,100));
      //写入文字
      //计算文字应该出现的起始位置
      $start_x = ceil($this->width/2) - 25;
      $start_y = ceil($this->height/2) - 8;
      if(imagestring($img,$this->fontsize,$start_x,$start_y,$captcha,$str_color)){
        //成功：输出验证码
        header('Content-type:image/png');
        imagepng($img);
      }else{
        //失败
        return false;
      }
    }
    /*
     * 获取验证码随机字符串
     * @return string $captcha，随机验证码文字
    */
    private function getCaptcha(){
      //获取随机字符串
      $str = implode('',array_merge(range('a','z'),range('A','Z'),range(1,9)));
      //随机取
      $captcha = '';  //保存随机字符串
      for($i = 0,$len = strlen($str);$i < $this->str_len;$i++){
        //每次随机取一个字符
        $captcha .= $str[mt_rand(0,$len - 1)] . ' ';
      }
      //将数据保存到session
      $_SESSION['captcha'] = str_replace(' ','',$captcha);
      //返回值
      return $captcha;
    }
    /*
     * 增加干扰点
     * @param1 resource $img
    */
    private function getPixels($img){
      //增加干扰点
      for($i = 0;$i < $this->pixes;$i++){
        //分配颜色
        $pixel_color = imagecolorallocate($img,mt_rand(100,150),mt_rand(100,150),mt_rand(100,150));
        //画点
        imagesetpixel($img,mt_rand(0,$this->width),mt_rand(0,$this->height),$pixel_color);
      }
    }
    /*
     * 增加干扰线
     * @param1 resource $img，要增加干扰线的图片资源
    */
    private function getLines($img){
      //增加干扰线
      for($i = 0;$i < $this->lines;$i++){
        //分配颜色
        $line_color = imagecolorallocate($img,mt_rand(150,200),mt_rand(150,200),mt_rand(150,200));
        //画线
        imageline($img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$line_color);
      }
    }
    /*
     * 验证验证码
     * @param1 string $captcha，用户提交的验证码
     * @return bool，成功返回true，失败返回false
    */
    public static function checkCaptcha($captcha){
      //验证码不区分大小写
      return (strtolower($captcha) === strtolower($_SESSION['captcha']));
    }
}
/**
 * 生成随机串
 *
 * @param   int     $len
 * @return  string
 */
function generate_code($len = 4)
{
    $chars = '23457acefhkmprtvwxy';
    for ($i = 0, $count = strlen($chars); $i < $count; $i++)
    {
        $arr[$i] = $chars[$i];
    }

    mt_srand((double) microtime() * 1000000);
    shuffle($arr);

    $code = substr(implode('', $arr), 5, $len);

    return $code;
}