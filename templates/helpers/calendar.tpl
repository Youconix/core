<style type="text/css">
<!--
#calendar {	width:215px; height:auto; }
#calendar li { width:30px; float:left; font-size:1em;}
#calendar li.bold { font-size:0.95em;}
-->
</style>

<div id="calender">
  <table>
  <thead>
    <tr>
      <td><span class="link" id="calendar_back">&lt;&lt;</span></td>
      <td class="textCenter" id="calendar_month"></td>
      <td><span class="link" id="calendar_next">&gt;&gt;</span></td>
    </tr>
    <tr>
      <td colspan="3"><ul>
	<block {calendar_day}>
	  <li class="bold">{name}</li>
	</block>
      </ul></td>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td colspan="3"><ul id="calender_days"></ul></td>
    </tr>
  </tbody>
  </table>
</div>