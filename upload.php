<?php 

//计算两个经纬度之间距离的函数
function getDistance($pos1, $pos2) 
{ 
	$earthRadius = 6367000; //approximate radius of earth in meters 
	$arr1=explode("_",$pos1);
	$lat1 = (float)$arr1[1];
	$lng1 = (float)$arr1[0];

	$arr2=explode("_",$pos2);
	$lat2 = (float)$arr2[1];
	$lng2 = (float)$arr2[0];

	$lat1 = ($lat1 * pi() ) / 180; 
	$lng1 = ($lng1 * pi() ) / 180; 

	$lat2 = ($lat2 * pi() ) / 180; 
	$lng2 = ($lng2 * pi() ) / 180; 

	$calcLongitude = $lng2 - $lng1; 
	$calcLatitude = $lat2 - $lat1; 
	$stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2); 
	$stepTwo = 2 * asin(min(1, sqrt($stepOne))); 
	$calculatedDistance = $earthRadius * $stepTwo; 

	return round($calculatedDistance); 
}



if ($_FILES["img"]["error"] > 0) 
{ 
	echo "Return Code: " . $_FILES["img"]["error"] . "</br>"; 
} 
elseif (empty($_FILES['img']['tmp_name']))
{
	echo "未选择图片。".'</br>';
}
else 
{ 
	//图片保存路径
	$savepath = "upload/";
	//获取图片类型
	$st = $_FILES["img"]["name"];
	$arr=explode(".", $st);
	$imgtype=$arr[count($arr)-1];
	//解析文件名
	$st = substr($st,0,strlen($st)-strlen($imgtype)-1);
	$arr = explode('_', $st);
	$time = $arr[0];
	$location = $arr[1] . "_" . $arr[2];
	//生成唯一ID
	$randname = md5(uniqid(md5(microtime(true)),true)). "." . $imgtype;
	$savename = $savepath . $randname;
	move_uploaded_file($_FILES["img"]["tmp_name"], $savename); 
	//echo "上传图片成功！图片保存在 " . $savename . "<br>"; 
	//连接到数据库
	$con = mysqli_connect("localhost","root","123123",'image')  or die('连接到数据库失败'.mysqli_connect_error());	
	mysqli_query($con,"set names utf8");

    //向数据库插入
	$sql = "INSERT INTO upload(path,time,location) VALUES('$randname','$time','$location')";
	mysqli_query($con,$sql) or die('插入数据失败'.mysqli_error($con));  

	$sql = 'SELECT * FROM search WHERE 1';
	$result = mysqli_query($con,$sql) or die('查询数据失败'.mysqli_error($con));  

	$min_time_diff = 86400000;
	$min_dis = 1355029117;
	$AQI = 1000;
	$PM25= 1000;
	$PM10= 1000;
	while( $row = mysqli_fetch_assoc($result) )
	{
		//echo $row['time']." ".$row['location']." ".$row['AQI']." ".$row['PM25']." ".$row['PM10'].'</br>';
		$time_diff = abs(strtotime($row['time']) - strtotime($time));
		$location_diff = getDistance($location,$row['location']);
		//echo $time_diff . "</br>";
		//echo $location_diff . "</br>";
		if($location_diff <= $min_dis and $time_diff < $min_time_diff)
		{
			$min_dis = $location_diff;
			$min_time_diff = $time_diff;
			$AQI = $row['AQI'];
			$PM25= $row['PM25'];
			$PM10= $row['PM10'];
		}
	}
	mysqli_close($con);

	//echo '距离最近点的AQI值为：' . $AQI . "，PM2.5值为：" . $PM25 . "，PM10值为：" . $PM10 . '。</br>';
	$result = array("AQI" => $AQI,"PM25" => $PM25,"PM10" => $PM10);
	echo json_encode($result);
} 
?> 
