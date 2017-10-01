<?php
namespace youconix\core\helpers;

class Localisation extends \youconix\core\helpers\Helper {
  public function dateTime($timestamp){

  }

  public function dateOrTime($timestamp){
    $now = $this->now();
    if( ($now - $timestamp) < 86400 ){
      return date('H:i:s',$timestamp);
    }
    else {
      return date('d-m-Y',$timestamp);
    }
  }

  public function now(){
    return time();
  }
}

