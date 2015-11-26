<?php
include('settings_t.php');
$lat=40.6701;
$lon=16.5999;
$text="catania";
$url="http://www.digitalchampions.it/?geo_mashup_content=geo-query&near_lat=".$lat;
	$url .="&near_lng=".$lon;
	$url .="&radius_km=20";
$json_string = file_get_contents($url);
$parsed_json = json_decode($json_string);
$count=0;
$temp_c1="";
foreach($parsed_json->{'objects'} as $data=>$csv1){
	 $count = $count+1;
	}
	if ($count==0) {
		$content = array('chat_id' => $chat_id, 'text' => "Non ci risultano digitalchampions in questo luogo",'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
	}
		for ($i=0;$i<$count;$i++){
$temp_c1 .= $parsed_json->{'objects'}[$i]->{'title'}.", ".$parsed_json->{'objects'}[$i]->{'object_id'};
$temp_c1 .="\n";
//var_dump($parsed_json);
}

echo $temp_c1;


?>
