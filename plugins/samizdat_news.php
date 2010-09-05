<?php
//TODO: Maybe inherit from a base plugin class as real people do?
class samizdat_news
{
  function banner()
  {
    echo "Samizdat news plugin, v. 0.2a\n";
    $this->interval = $this->core->get_config("zhurnal_news_interval");
    $this->lastcheck = 0;
    echo "Checking every: ".$this->interval.", Last check: ".$this->lastcheck."\n";
  }

	
function str_to_id($str)
{
  switch($str)
  {
    case 'page':
      return 0;
    case 'author':
      return 2;
    case 'title':
      return 1;
  }
  return -1;
}
function id_to_str($str)
{
  
}


  function update()
    {
    if (time() - $this->lastcheck >= $this->interval) 
	  {
	    echo "update in progress\n";
	    $this->get_news();
	    $this->lastcheck=time();
	    //Do we really need that?
	    $this->core->jconn->presence($status="Последнее обновление в ".$this->lastcheck." по времени UNIX, следующее в ".($this->lastcheck + $this->interval).".");
	  }
    }

  function process_message($userdata, $body, $words)
    {
     //$this->core->send_message("aifiltr0@invyl.ath.cx","Okay!");
     if ($words[0]=="sub")
	{
        $this->core->send_message($userdata['owner'],"С версии 0.2 команды для подписки на новости доступны через news вместо sub. Подробнее смотрите справку.");
        return true;
	}

     switch ($words[0])
	{
	 case 'news':
	    switch ($words[1])
	    {
	      case 'list':
		$msg = "Список подписок:\n";
		$msg.= "id	значение\n";
		$msg.= "----------------------------\n";
		$r = mysql_query("SELECT * FROM `subscriptions` WHERE `owner`='".$userdata['owner']."';");
		$i=0;
		while ($row=mysql_fetch_array($r))
		{
			$i++;
			$msg.=$i.".\t".$row['data']."\n";
		}
		break;
	      case 'add':
	      echo 
		$id = $this->str_to_id($words[2]);
		$d = eregi_replace($words[0]." ".$words[1]." ".$words[2]." ","",$body);
		if ($id>=0)
		{
			mysql_query("INSERT INTO `subscriptions` (`owner` ,`field` ,`data`) VALUES ('".$userdata['owner']."',  '".$id."',  '".mysql_real_escape_string($d)."');");
			$msg = "Подписка успешно добавлена";
		}else
		{
		  $msg = "Неправильное/неподдерживаемое поле в подписке";
		}
		break;
	      case 'del':
		$id = $words[2];
		echo "==> $id\n";
		if ((!is_numeric($id)) or ($id<=0)) $msg="Странный какой-то id. Это точно число больше нуля?"; else
		{
			$r = mysql_query("SELECT * FROM `subscriptions` WHERE `owner`='".$userdata['owner']."' LIMIT ".($id-1).",$id;");
			
			if ($row=mysql_fetch_array($r))
			{
		  	print_r($row);
		  	$r = mysql_query("DELETE FROM `subscriptions` WHERE `owner`='".$userdata['owner']."' AND `field`='".$row['field']."' AND `data`='".mysql_real_escape_string($row['data'])."';");
		  	$data['body']="news list";
		  	$data['from']=$userdata['owner'];
		  	$this->core->process_message($data);
		  	$msg="Подписка удалена.";
			}else
			{
		  	$msg = "Подписка с таким id не существует!";
			}
		}
		break;
		case 'wipe':
		  $msg = "Все новостные подписки удалены";
		  mysql_query("DELETE FROM `subscriptions` WHERE `owner`='".$userdata['owner']."';");
		  
		break;
		case 'export':

		break;
		default:
		$msg= "Неизвестный запрос новостной подписки. Попробуйте еще раз или обратитесь к справке командой help";
		break;
	    }
	    break;
	default: 
	  return false; 

        break;
	}
     $this->core->send_message($userdata['owner'], $msg);
      // "Break the chain!\n";
     return true;  //true will stop the chain
    }


////////

//ToDo: Multiple templates for moar fun
 private function build_reply($item,$entry)
{
  $msg['to']=$entry['owner'];
  $msg['body']="Привет, хочу сообщить, что ".$item[2]." изволили выложить нечто свеженькое в жанре ".$item[5].". 
  Это свеженькое зовется '".$item[1]."' и весит ".$item[3]."
  Ссылка на произведение: ".$item[0]."
  Приятного прочтения. 
  Всегда Ваш - Самиздатовский жаббер бот.";
  return $msg;
  
}

private function get_news()
  {
	$data=file_get_contents("http://zhurnal.lib.ru/4lib_news");
        //print_r($http_response_header);
	//$data=file_get_contents("/tmp/4lib_news.1");
	$dt = gzinflate($data);
	
	if ($dt!==false) 
	  {
	  unset($data);
	  $data=$dt;
	  }
	
	//file_put_contents("/tmp/4lib_news",$data);
	//Sometimes that will be KOI8. TODO: autodetection. 
	foreach ($http_response_header as $hd)
	{
	  //ToDo: parse and find out what charset we have in there today.
	}
	$data = iconv("CP1251", "UTF-8", $data);
	print_r($data);
	$data = explode ("\n", $data);
	//
	
	$item = explode("\t",$data[0]);
	//$r = mysql_query("SELECT `value` FROM `config` WHERE `key`='nup_timestamp';");  
	//$r = mysql_fetch_array($r);
	$last_check = $this->core->get_config("zhurnal_news_timestamp");
	//echo "last check: $last_check";
	print_r($item);
	if ($item[4]>0) $this->core->write_config("zhurnal_news_timestamp", $item[4]);
	//mysql_query("UPDATE  `subscriptions` SET  `data` =  '".$item[4]."' WHERE `owner` =  'sdata' ;");  
	
	echo "\n".mysql_error().$item[4];
	
	foreach($data as $line)
	{
	  $item = explode("\t",$line);
	  if ($item[4] >= $last_check)
	  {
	  //FixMe: Once we have over 9k rules - this place will suck hard
	  $list = mysql_query("SELECT * FROM `subscriptions`");
	  while ($entry = mysql_fetch_array($list))
	  {
	    if (strpos($item[$entry['field']],$entry['data'])!==false)
	    {
	      echo "Match!\n";
	      $msg = $this->build_reply($item,$entry);
	      $this->core->queue_message($msg['to'],$msg['body']);
	    }
	  }
	 }else break;
	}
	unset ($data);
  }
}
?>