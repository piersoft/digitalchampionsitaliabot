<?php
/**
* Telegram Bot example for Italian Museums of DBUnico Mibact Lic. CC-BY
* @author Francesco Piero Paolicelli @piersoft
*/
//include("settings_t.php");
include("Telegram.php");

class mainloop{
const MAX_LENGTH = 4096;
function start($telegram,$update)
{

	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");
	//$data=new getdata();
	// Instances the class

	/* If you need to manually take some parameters
	*  $result = $telegram->getData();
	*  $text = $result["message"] ["text"];
	*  $chat_id = $result["message"] ["chat"]["id"];
	*/


	$text = $update["message"] ["text"];
	$chat_id = $update["message"] ["chat"]["id"];
	$user_id=$update["message"]["from"]["id"];
	$location=$update["message"]["location"];
	$reply_to_msg=$update["message"]["reply_to_message"];

	$this->shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg);
	$db = NULL;

}

//gestisce l'interfaccia utente
 function shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg)
{
	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");

	if ($text == "/start") {
		$reply = "Benvenuto. Per ricercare un Digital Champions italiano, clicca sulla graffetta (📎) e poi 'posizione' oppure digita il nome del Comune. Verrà interrogato il DataBase e verranno elencati fino a max 20 campioni digitali. In qualsiasi momento scrivendo /start ti ripeterò questo messaggio di benvenuto.\nQuesto bot, non ufficiale, è stato realizzato da @piersoft e il codice sorgente per libero riuso si trova su https://github.com/piersoft/digitalchampionsbot. La propria posizione viene ricercata grazie al geocoder di openStreetMap con Lic. odbl.";
		$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);
		$log=$today. ";new chat started;" .$chat_id. "\n";

		}

		//gestione segnalazioni georiferite
		elseif($location!=null)
		{

			$this->location_manager($telegram,$user_id,$chat_id,$location);
			exit;

		}
//elseif($text !=null)

		else{

			$location="Sto cercando i Campioni digitali censiti nel Comune di: ".$text;
			$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
			sleep (1);
					$text=str_replace(" ","%20",$text);
			$json_string = file_get_contents('http://www.digitalchampions.it/?geo_mashup_content=geo-query&saved_name='.$text);

			$parsed_json = json_decode($json_string);
			$count=0;
			$temp_c1="";
			foreach($parsed_json->{'objects'} as $data=>$csv1){
			   $count = $count+1;
				}
				if ($count==0) {
					$json_string = file_get_contents('http://www.digitalchampions.it/?geo_mashup_content=geo-query&locality_name='.$text);

			//		$content = array('chat_id' => $chat_id, 'text' => "Non ci risultano digitalchampions in questo luogo",'disable_web_page_preview'=>true);
			//			$telegram->sendMessage($content);
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
				}
				  for ($i=0;$i<$count;$i++){
			$temp_c1 .= $parsed_json->{'objects'}[$i]->{'title'}."\nhttp://www.digitalchampions.it/?p=".$parsed_json->{'objects'}[$i]->{'object_id'};
			$temp_c1 .="\n";
			//var_dump($parsed_json);
			}

	$chunks = str_split($temp_c1, self::MAX_LENGTH);
		foreach($chunks as $chunk) {
//	$forcehide=$telegram->buildForceReply(true);
		//chiedo cosa sta accadendo nel luogo
	$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);

			$telegram->sendMessage($content);

		}
		$content = array('chat_id' => $chat_id, 'text' => "Digita un Comune oppure invia la tua posizione tramite la graffetta (📎)");
			$telegram->sendMessage($content);
/*

			 $reply = "Hai selezionato un comando non previsto. Ricordati che devi prima inviare la tua posizione cliccando sulla graffetta (📎) ";
			 $content = array('chat_id' => $chat_id, 'text' => $reply);
			 $telegram->sendMessage($content);

			 $log=$today. ";wrong command sent;" .$chat_id. "\n";
			 //$this->create_keyboard($telegram,$chat_id);

*/
	}


}


// Crea la tastiera
function create_keyboard($telegram, $chat_id)
 {
	 $forcehide=$telegram->buildKeyBoardHide(true);
	 $content = array('chat_id' => $chat_id, 'text' => "Invia la tua posizione cliccando sulla graffetta (📎) in basso e, se vuoi, puoi cliccare due volte sulla mappa e spostare il Pin Rosso in un luogo specifico", 'reply_markup' =>$forcehide);
	 $telegram->sendMessage($content);

 }




function location_manager($telegram,$user_id,$chat_id,$location)
	{

			$lon=$location["longitude"];
			$lat=$location["latitude"];
			$response=$telegram->getData();
			$location="Sto cercando i Campioni digitali censiti nei 20Km attorno a ".$lat.",".$lon;
			$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
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
			$temp_c1 .= $parsed_json->{'objects'}[$i]->{'title'}."\nhttp://www.digitalchampions.it/?p=".$parsed_json->{'objects'}[$i]->{'object_id'};
			$temp_c1 .="\n";
			//var_dump($parsed_json);
			}


			//	echo $alert;

				$chunks = str_split($temp_c1, self::MAX_LENGTH);
				foreach($chunks as $chunk) {
		    $forcehide=$telegram->buildForceReply(true);
		   	$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
  			$telegram->sendMessage($content);

				}


				$content = array('chat_id' => $chat_id, 'text' => "Digita un Comune oppure invia la tua posizione tramite la graffetta (📎)");
					$telegram->sendMessage($content);
	}


}

?>
