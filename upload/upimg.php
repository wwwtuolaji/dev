
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
<?php  
if(empty($_GET[submit]))  
{  
?>  
<form enctype="multipart/form-data" action="<?php $_SERVER['PHP_SELF']?>?submit=1" method="post">  
Send this file: <input name="filename" type="file">  
<input type="submit" value="确定上传">  
</form>  
<?php   
}else{  
    $path="uploadfiles/";        //上传路径  

//echo $_FILES["filename"]["type"];  


if(!file_exists($path))  
{  
    //检查是否有该文件夹，如果没有就创建，并给予最高权限  
    mkdir("$path", 0700);  
}//END IF  
//允许上传的文件格式  
$tp = array("image/gif","image/pjpeg","image/png","image/jpeg");  
//检查上传文件是否在允许上传的类型 

if(!in_array($_FILES["filename"]["type"],$tp))  
{  
    echo "格式不对";  
    exit;  
}//END IF  
function getRandom($param){
    $str="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $key = "";
    for($i=0;$i<$param;$i++)
     {
         $key .= $str{mt_rand(0,32)};    //生成php随机数
     }
     return $key;
 }
if($_FILES["filename"]["name"])  
{  
		$houzhui=strstr($_FILES["filename"]["name"],".");
        $file1 =getRandom(5);
        $file1 .=$houzhui;
        $file2  = $path.time().$file1;  
        $flag=1;  
}//END IF  
if($flag) $result=move_uploaded_file($_FILES["filename"]["tmp_name"],$file2);  
//特别注意这里传递给move_uploaded_file的第一个参数为上传到服务器上的临时文件  
if($result)  
{  
    //echo "上传成功!".$file2;  
    echo "<script language='javascript'>";  
    echo "alert(\"上传成功！\");";

    echo " location='/$file2'";  
    echo "</script>";  
}//END IF  



}  

?>
	
</body>
</html>
