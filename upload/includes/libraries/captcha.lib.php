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
完整写法
include_once(ROOT_PATH.'/includes/cls.captcha.new.php');
$seccode = 'asdf';
$code = new Captcha();
$code->code = $seccode;
$code->width = 150;
$code->height = 60;
$code->background = 1;
$code->adulterate = 1;
$code->ttf = 1;
$code->angle = 1;
$code->color = 1;
$code->size = 1;
$code->shadow = 1;
$code->animator = 0;
$code->display();
$code->fontpath = ROOT_PATH.'/includes/captcha/fonts/';
$code->imagepath = ROOT_PATH.'/includes/captcha/';

简单用法
include_once(ROOT_PATH.'/includes/cls.captcha.new.php');
$seccode = 'asdf';
$code = new Captcha();
$code->code = $seccode;
$code->display();
 */

class Captcha
{

    var $code;            //a-z 范围内随机
    var $width     = 150;        //宽度
    var $height     = 60;        //高度
    var $background    = 1;        //随机图片背景
    var $adulterate    = 1;        //随机背景图形
    var $ttf     = 1;        //随机 TTF 字体
    var $angle     = 0;        //随机倾斜度
    var $color     = 1;        //随机颜色
    var $size     = 0;        //随机大小
    var $shadow     = 1;        //文字阴影
    var $animator     = 0;        //GIF 动画
    var $fontpath    = '';        //TTF字库目录
    var $imagepath    = '';        //图片目录

    var $fontcolor;
    var $im;

   
    function vCode($word,$num = 4, $size = 20, $width = 0, $height = 0) {
    !$width && $width = $num * $size * 4 / 5 + 5;
    !$height && $height = $size + 10; 
    // 去掉了 0 1 O l 等
    if (empty($word)) {
       $str = "23456789abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVW";
        $code = '';
        for ($i = 0; $i < $num; $i++) {
            $code .= $str[mt_rand(0, strlen($str)-1)];
        }
    }else{
       $code = $word;
    }
     
    // 画图像
    $im = imagecreatetruecolor($width, $height); 
    // 定义要用到的颜色
    $back_color = imagecolorallocate($im, 235, 236, 237);
    $boer_color = imagecolorallocate($im, 118, 151, 199);
    $text_color = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120)); 
    // 画背景
    imagefilledrectangle($im, 0, 0, $width, $height, $back_color); 
    // 画边框
    imagerectangle($im, 0, 0, $width-1, $height-1, $boer_color); 
    // 画干扰线
    for($i = 0;$i < 5;$i++) {
        $font_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
        imagearc($im, mt_rand(- $width, $width), mt_rand(- $height, $height), mt_rand(30, $width * 2), mt_rand(20, $height * 2), mt_rand(0, 360), mt_rand(0, 360), $font_color);
    } 
    // 画干扰点
    for($i = 0;$i < 50;$i++) {
        $font_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
        imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $font_color);
    } 
    // 画验证码
    @imagefttext($im, $size , 0, 5, $size + 3, $text_color, 'c:\\WINDOWS\\Fonts\\simsun.ttc', $code);
    $_SESSION["VerifyCode"]=$code; 
    header("Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate");
    header("Content-type: image/png;charset=gb2312");
    imagepng($im);
    imagedestroy($im);
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

?>