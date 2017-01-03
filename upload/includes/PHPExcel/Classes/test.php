<?php 

require 'PHPExcel.php';
$filePath="text.xlsx";

$PHP=new PHPExcel_Reader_Excel2007();
echo "<pre>";
var_dump($PHP);

?>