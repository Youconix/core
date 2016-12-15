<?php
namespace youconix\core\helpers;

class DateTimePicker extends \youconix\core\helpers\Helper {
  /**
   *
   * @var \Language
   */
  protected $language;

  /**
   *
   * @var \Config
   */
  protected $config;

  protected $s_name;
  protected $s_date;
  protected $s_time;
  protected $s_field;

  public function __construct(\Language $language,\Config $config){
    $this->language = $language;
    $this->config = $config;
  }

  public function setName($s_name){
    $this->s_name = $s_name;
  }

  public function setDate($s_date){
    $this->s_date = date('Y-m-d',strtotime($s_date));
  }

  public function setTime($s_time){
    $this->s_time = date('H:i:s',  strtotime($s_time));
  }

  public function setField($s_field){
    $this->s_field = $s_field;
  }

  protected function generateDays(){
    $s_output = '';

    for($i=0; $i<=6; $i++){
      $s_output .= '<span class="datepicker_day_names">'.$this->language->get('system/weekdaysShort/day'.$i).'</span>';
    }

    return $s_output;
  }

  protected function generateTimePicker(){
    return '<table>
      <tbody>
      <tr>
        <td class="timepicker_hour_up"></td>
        <td class="timepicker_minute_up"></td>
        <td class="timepicker_second_up"></td>
      </tr>
      <tr>
        <td class="timepicker_hour">0</td>
        <td class="timepicker_minute">0</td>
        <td class="timepicker_second">0</td>
      </tr>
      <tr>
        <td class="timepicker_hour_down"></td>
        <td class="timepicker_minute_down"></td>
        <td class="timepicker_second_down"></td>
      </tr>
      </tbody>
      </table>
      ';
  }

  public function addHead(\Output $template){
    $a_months = [];
    for($i=1; $i<=12; $i++){
      $a_months[] = $this->language->get('system/months/month'.$i);
    }

    $template->append('head','<script src="/js/widgets/datepicker.js" type="text/javascript"></script>');
    $template->append('head','<link rel="stylesheet" href="/'.$this->config->getSharedStylesDir().'css/widgets/datepicker.css" type="text/css">');
    $template->append('head','<script type="text/javascript">
        var monthNames = '.json_encode($a_months).';
        </script>');
  }

  public function generate(){
    $s_output = '<section class="datepicker" id="datepicker_'.$this->s_name.'">
      <div class="datepicker_bar">
        <div class="datepicker_left"></div>
        <div class="datepicker_month_year"></div>
        <div class="datepicker_right"></div>
      </div>
      <div class="datepicker_bar_days">
        '.$this->generateDays().'
      </div>
      <div class="datepicker_days"></div>
      <div class="datepicker_time">'.
        $this->generateTimePicker().'
      </div>
      <div class="datepicker_buttons">
        <input type="button" class="datepicker_oke" value="'.$this->language->get('system/buttons/edit').'">
        <input type="button" class="datepicker_cancel" value="'.$this->language->get('system/buttons/cancel').'">
      </div>
    </section>

    <script type="text/javascript">
    <!--
    var datepicker_'.$this->s_name.' = new DateTimePicker();    
    datepicker_'.$this->s_name.'.init("'.$this->s_name.'");
    datepicker_'.$this->s_name.'.bind("'.$this->s_field.'");
    datepicker_'.$this->s_name.'.show();
   ';

    if( !empty($this->s_date) ){
      $s_output .= 'datepicker_'.$this->s_name.'.setDate('.$this->s_date.' '.$this->s_time.');';
    }

    $s_output .= '//-->
    </script>';

    return $s_output;
  }
}

