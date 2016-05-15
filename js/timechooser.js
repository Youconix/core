function TimeChooser(){
  this.months = new Array();
  this.dayNames = new Array();
  this.callback = null;
  this.month = null;
  this.year = null;
  this.startDayWeek = null;
  this.choosenMonth = - 1;
  this.choosenYear = - 1;
  this.choosenDay = - 1;
  this.choosenHour = - 1;
  this.choosenMinute = - 1;
}
TimeChooser.prototype.init = function(){
  this.months = timeChooserData['months'];
  this.dayNames = timeChooserData['dayNames'];
  this.callback = timeChooserData['callback'];
  this.month = timeChooserData['month'];
  this.year = timeChooserData['year'];
  this.startDayWeek = timeChooserData['startDayWeek'];
  this.generateDays();
  this.generateTime();
  this.choosenMonth = - 1;
  this.choosenYear = - 1;
  this.choosenDay = - 1;
  this.choosenHour = - 1;
  this.choosenMinute = - 1;
};
TimeChooser.prototype.generateDays = function(){
  var days = getDaysMonth(this.month, this.year);
  $("#calenderChooser tbody").empty();
  var header = '<td><a href="javascript:timeChooser.previousMonth()">&lt;&lt;</a></td> '+
	'<td class="bold textCenter"> '+ this.months[this.month]+' '+ this.year+' </td> '+
	'<td><a href="javascript:timeChooser.nextMonth()">&gt;&gt;</a></td>';

  var dayNames = "";
  for (i = this.startDayWeek; i < 7; i++){
    dayNames += "<span>" + this.dayNames[i] + "</span>";
  }
  for (i = 0; i < this.startDayWeek; i++){
    dayNames += "<span>" + this.dayNames[i] + "</span>";
  }

  var output = "";
  /* Generate start space */
  var d = new Date(this.year, this.month - 1, 1);
  var n = d.getDay() - this.startDayWeek;
  var current = new Date();
  current.setHours(23);
  current.setMinutes(59);
  current.setSeconds(59);
  currentDay = 0;
  if ((this.year < current.getFullYear()) || (this.month - 1) < current.getMonth()){
    currentDay = 32;
  }
  else if ((this.month - 1) === current.getMonth()){
    currentDay = current.getDate();
  }

  if (n < 0)	n = 7 + n;
  for (i = 1; i <= n; i++){
    output += "<span>&nbsp;</span>";
  }

  /* Generate days */
  for (i = 1; i <= days; i++){
    if (i <= currentDay){
      output += '<span class="dayDisabled">'+i+'</span>';
    }
    else {
      output += '<span id="day_'+i+'">'+i+'</span>';
    }
  }

  $("#calenderChooser tbody").append('<tr> \
    ' + header + ' \
    </tr>');
  $("#calenderChooser tbody").append('<tr id="dayNames"> \
      <td><br/></td> \
      <td style="width:180px">'+dayNames+'</td> \
      <td><br/></td> \
    </tr>');
  $("#calenderChooser tbody").append('<tr id="calenderDays">\
      <td><br/></td> \
      <td>'+output+'</td> \
      <td><br/></td> \
    </tr>');

  for (i = 1; i <= days; i++){
    if (i > currentDay){
      $("#day_" + i).click(function(){ timeChooser.selectDay(this.id); });
    }
  }
};
TimeChooser.prototype.generateTime = function(){
  $("#hourList").empty();
  $("#minuteList").empty();
  
  var hour, display, minute;
  for (i = 0; i <= 23; i++){
    display = i;
    if (display < 10)	display = "0" + display;
    hour = '<span id="hour_'+i+'">'+display+'</span>';
    $("#hourList").append(hour);
  }

  for (i = 0; i <= 59; i++){
    display = i;
    if (display < 10)	display = "0" + display;
    minute = '<span id="minute_'+i+'">'+display+'</span>';
    $("#minuteList").append(minute);
  }

  for (i = 0; i <= 59; i++){
    if (i <= 23){
      $("#hour_" + i).click(function(){ timeChooser.selectHour(this.id); });
    }

    $("#minute_" + i).click(function(){ timeChooser.selectMinute(this.id); });
  }
};
TimeChooser.prototype.previousMonth = function(){
  this.month--;
  if (this.month === 0){
    this.month = 12;
    this.year--;
  }

  this.init();
};
TimeChooser.prototype.nextMonth = function(){
  this.month++;
  if (this.month === 13){
    this.month = 1;
    this.year++;
  }
  this.init();
};
TimeChooser.prototype.selectDay = function(id){
  var day = parseInt(id.replace("day_", ""));
  if (this.choosenDay !== - 1){
    $("#day_" + this.choosenDay).attr("class", "");
  }

  this.choosenMonth = this.month;
  this.choosenYear = this.year;
  this.choosenDay = day;
  $("#day_" + this.choosenDay).attr("class", "selected");
  this.check();
};
TimeChooser.prototype.selectHour = function(id){
  var hour = parseInt(id.replace("hour_", ""));
  if (this.choosenHour !== - 1){
    $("#hour_" + this.choosenHour).attr("class", "");
  }

  this.choosenHour = hour;
  $("#hour_" + this.choosenHour).attr("class", "selected");
  this.check();
};
TimeChooser.prototype.selectMinute = function(id){
  var minute = parseInt(id.replace("minute_", ""));
  if (this.choosenMinute !== - 1){
    $("#minute_" + this.choosenMinute).attr("class", "");
  }

  this.choosenMinute = minute;
  $("#minute_" + this.choosenMinute).attr("class", "selected");
  this.check();
};
TimeChooser.prototype.check = function(){
  if (this.choosenMonth !== - 1 && this.choosenYear !== - 1 && this.choosenDay !== - 1 && this.choosenHour !== - 1 && this.choosenMinute !== - 1){
    eval(this.callback + "(" + this.choosenMonth + "," + this.choosenYear + "," + this.choosenDay + "," + this.choosenHour + "," + this.choosenMinute + ")");
    this.init();
  }
};

var timeChooser = new TimeChooser();
timeChooser.init();