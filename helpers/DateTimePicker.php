<?php
namespace youconix\core\helpers;

class DateTimePicker extends \youconix\core\helpers\DatePicker {
  public function setTime($s_time){
    $this->s_time = date('H:i:s',  strtotime($s_time));
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

  public function generate(){
    $s_output = '<section class="datetimepicker" id="datepicker_'.$this->s_name.'">
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

