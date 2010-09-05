<?php
//TODO: Maybe inherit from a base plugin class as real people do?
class basic
{
  function banner()
  {
    echo "Basic plugin example v. 0.1\n";
  }

  function update()
    {
    //echo "basic: update\n";
    }

  function process_message($userdata, $body, $words)
    {
     print_r($userdata);
     print_r($body);
     print_r($words);
     //$this->core->send_message("aifiltr0@invyl.ath.cx","Okay!");
     echo "basic: msg\n";
     return false;  //true will stop the chain
    }
}
?>