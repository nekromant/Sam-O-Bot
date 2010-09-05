<?php

/*
 CREATE TABLE  `sam-o-bot`.`subscriptions` (
`owner` VARCHAR( 255 ) NOT NULL ,
`field` SMALLINT( 255 ) NOT NULL ,
`data` VARCHAR( 2048 ) NOT NULL ,
INDEX (  `owner` ,  `field` )
) ENGINE = MYISAM ;
 */
class samolink
{
	var $lastcheck = 0;
	var $interval = 1800; //30 min
	var $fifo=array();
	var $fifoChecks=0;
	var $session_started=false;
	var $owner="aifiltr0@invyl.ath.cx";

	function __construct($dbhost, $dbuser, $dbpass, $dbname, $int, $owner )
	{
	$link = mysql_connect($dbhost, $dbuser, $dbpass);
	if (!$link) {
    		die('Could not connect: ' . mysql_error()."\n");
	}
	$this->interval=$int;
	$this->owner = $owner;
	echo "Connected successfully\n";;
	mysql_select_db($dbname);
	
	}
	



function fetch_userdata($user)
{
  $r=mysql_query("SELECT * FROM `userdata` WHERE `owner`='$user'");
  if (mysql_affected_rows()==0)
    {
    //Userdata table has a bunch of default opts that will be filled in.
    mysql_query("INSERT INTO  `userdata` (`owner`) VALUES ('$user'); ");
    return $this->fetch_userdata($user);
    }else
  return mysql_fetch_assoc($r);
  
}
	
function load_plugins($plugs)
{
  $this->fetch_userdata("test");
  foreach($plugs as $plug)
  {
  echo "Loading plugin $plug\n";
  if (is_file("plugins/$plug.php"))
    {
    include "plugins/$plug.php";
    $p = new $plug;
    $p->core = $this;
    $p->banner();
    $this->plugins[] = $p;
    }else
    {
    echo "Failed to load plugin $plug\n";
    }
  }
}

function get_config($key)
{
	$r = mysql_query("SELECT `value` FROM `config` WHERE `key`='$key';");  
	$r = mysql_fetch_array($r);
	return $r[0];
}


function write_config($key,$value)
{
  mysql_query("UPDATE  `config` SET  `value` =  '".$value."' WHERE `key` =  '$key' ;");  
}


function update()
{
if ($this->session_started)
  {
  foreach ($this->plugins as $plug)
    {
    $plug->update();
    }
    //Now pop somethingfrom fifo

  if (count($this->fifo)==0) return false;
    else
    return array_shift($this->fifo);
  }
}


//TODO: event logging right here!
// Message is put at the end of the fifo
// All update results should go there
function queue_message($to, $text)
{
  array_push($this->fifo, array('to'=>$to, 'body'=> $text));
}

// Message is put at the top of the fifo, for faster delivery.
// All responds to cmds should go there.
function send_message($to, $text)
{
  array_unshift($this->fifo, array('to'=>$to, 'body'=> $text));
}


function process_message($data)
{
  $f = explode ( "/", $data['from']);
  $a = explode (" ",$data['body']);
  $userdata = $this->fetch_userdata($f[0]);
  $userdata['resource']=$f[1];
  foreach ($this->plugins as $plug)
    {
    if ($plug->process_message($userdata,$data['body'],$a)!=false) return;
    }
    $this->send_message($data['from'],"Неизвестная команда. Отправьте help для списка команд, motd - для новостей");
}

function checkFifo()
	{
	  if ($this->fifoChecks==1)
	    {
	      if (count($this->fifo) == 0)
	      {
	      $msg['to']=$this->owner;
 	      $msg['body']="FiFo опустело. Самое время вырубить бота и что-то сделать.";
	      $this->fifoChecks=0;
	      return $msg;
	      }
	    }
	    
	  
	    
	    
	  
	  

	}


	function update_news()
	{
	  //Sometimes samizdat will send us deflated output, sometimes not
	
	}
	


function process_message_old($data)
	{
	$f = explode ( "/", $data['from']);
	
	$body = explode(" ", $data['body']);
	$msg = "Неизвестная команда, сообщение, либо что-то пошло зверски не так. Если считаете, что это ошибка - сообщите об этом! ";
	
	switch ($body[0])
	{
	  case "help":
	       $msg = " Привет, вот краткая справка того, что я умею. 
	       sub add [поле] [данные_подписки] - добавить подписку. Поле подписки может быть: page (для фильтрации под адресу странички автора), author (инициалы автора), title - фильтрация по кусочку названия произведения
	       Пример 1: sub add page progin_w_i - Вы получите оповещение обо всех произведениях, в адресе которых встретиться текст progin_w_i
	       Пример 2: sub add title Демонология для чайников - Вы получите оповещение обо всех произведениях, в названии которых содержится \"Демонология для чайников\", т.е. \"Демонология для чайников: Глава 1\", \"Демонология для чайников: Глава 2\"  и т.д.
	       Пример 3: sub add author Прогин В., Пасика К. - Оповещения будут поступать на совпадение поля \"автор\"  с заданным текстом.
	       Во всех вышеуказанных примерах можно укоротить строку поиска, например, вместо \"Демонология для чайников\", написать \"Демонология\". Тогда под такой фильтр попадет и \"Демонология для профессионалов\" и \"Аццкая Демонология\"
	       sub list - список подписок.
	       sub del id - удалить подписку c id. В ответ придет список текущих подписок.
	       shutup - Эта команда удалит все подписки, т.е. от бота не будет больше приходить никаких сообщений.
	       Полный список команд и более подробную инструкцию смотрите на страничке автора: http://zhurnal.lib.ru/p/progin_w_i
	       Об ошибках сообщайте туда же";
	       
	  break;
	  
	  
	}
	return $msg;
	//return "STUB: msg from ". $f[0]." processed.". mysql_error();
	}
}
?>