<?php

namespace youconix\core\helpers;

class Stats extends \youconix\core\helpers\Helper
{
  /**
   *
   * @var \Config
   */
  private $config;

  public function __construct(\Config $config){
    $this->config = $config;
  }
  
  public function load(\Output $template)
  {
    if ($this->config->isAjax() || (stripos($_SERVER['PHP_SELF'], 'admin/') !== false)
        || !$this->config->statsEnabled()) {
      return;
    }

    $s_stats = '<div><img src="" id="stats" alt=""/></div>
    <script type="text/javascript">
    <!--
    var width;
    var height;
    var colors = screen.colorDepth;

    //IE
    if( !window.innerWidth ){
        if( !(document.documentElement.clientWidth == 0) ){
            //strict mode
            width = document.documentElement.clientWidth;
                    height = document.documentElement.clientHeight;
        }
            else{
            //quirks mode
            width = document.body.clientWidth;
                    height = document.body.clientHeight;
        }
    } else {
        //w3c
        width = window.innerWidth;
            height = window.innerHeight;
    }

    document.getElementById("stats").src = "/stats/stats.php?page='.$this->config->getPage().'&colors="+colors+"&width="+width+"&height="+height;
    //-->
    </script>

    <noscript><div><img src="/stats/stats.php?page='.$this->config->getPage().'" alt=""/></div></noscript>
  ';

    $template->set('statisticsImg', $s_stats);
  }
}