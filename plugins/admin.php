<?php
//TODO: Maybe inherit from a base plugin class as real people do?
class admin
{
  function banner()
  {
    echo "Admin helper funcs v. 0.1\n";
  }

  function update()
    {
    //Nothing to do here.
    }

  function process_message($userdata, $body, $words)
    {
    $msg=null;
    if ($userdata['owner']==$this->core->owner)
	{
	switch ($words[0])
		{
	  	case 'stats':
	  	$r = mysql_query("SELECT COUNT(*) FROM `subscriptions`;");  
	  	$d = mysql_fetch_array($r);
	  	$msg = "Правил в БД: ".$d[0].", Элементов в FIFO: ". count($this->core->fifo)."\n";
		$msg .= "Используемая память: ".number_format(memory_get_usage())."\n";
		$msg .= "Пиковое использование памяти: ".number_format(memory_get_peak_usage())."\n";
	  	break;
	        case 'dumprules':
		  $msg = "Список подписок:\n";
		  $msg.= "id		владелец		поле		значение\n";
		  $msg.= "----------------------------\n";
		  $r = mysql_query("SELECT * FROM `subscriptions` ORDER BY `owner` ASC;");
		  $i=0;
		  while ($row=mysql_fetch_array($r))
		  {
			$i++;
			$msg.=$i."\t".$row['owner']."\t".$row['field']."\t".$row['data']."\n";
		  }
		  mysql_free_result($r);
		  break;
		case 'status':
		  $stat = eregi_replace("status ", "",$data['body']);
		  $this->core->jconn->presence($status=$stat);
		  $msg = "Статус выставлен. ";
		  break;
		}
	}
	if ($msg)
	{
	$this->core->send_message($userdata['owner'],$msg);
	return true;
	}
     return false;  //true will stop the chain
    }
}
?>