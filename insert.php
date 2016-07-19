<?php 
if ($_FILES['img']['error'] > 0) 
{ 
	echo "Return Code: " . $_FILES['img']['error'] . "</br>"; 
} 
elseif (empty($_FILES['img']['tmp_name']))
{
	echo "未选择图片。".'</br>';
}
elseif (empty($_POST['pm']))
{
	echo "未填写PM2.5的值。".'</br>';
}
else 
{ 
	//图片保存路径
	$savepath = "upload/";
	//获取图片类型
	$arr=explode(".", $_FILES['img']['name']);
	$imgtype=$arr[count($arr)-1];
	//产生时间戳+随机数字作为文件名
	do
	{
		$randname=date("Y").date("m").date("d").date("H").date("i").date("s").rand(100,999).".".$imgtype;
	}
	while(file_exists($savepath . $randname));
	$savename = $savepath . $randname;
	move_uploaded_file($_FILES['img']['tmp_name'], $savename); 
	echo "上传图片成功！图片保存在 " . $savename . "<br>"; 
	//连接到数据库
	$con = mysqli_connect("localhost","root","123123",'image')  or die('连接到数据库失败'.mysqli_connect_error());	
	mysqli_query($con,"set names utf8");
	$pm_num = (int)$_POST["pm"];
	$sql = "INSERT INTO image(img_path,pm) VALUES('$savename','$pm_num')"; 
	mysqli_query($con,$sql) or die('向数据库插入数据失败'.mysqli_error($con));  
	$sql = 'SELECT * FROM image WHERE 1';
	$result = mysqli_query($con,$sql) or die('向数据库插入数据失败'.mysqli_error($con));  
	echo "当前数据库数据如下：".'</br>';
	while( $row = mysqli_fetch_assoc($result) )
	{
		echo $row['img_path']."  ".$row['pm'].'</br>';
	}
	mysqli_close($con);
} 
?> 
