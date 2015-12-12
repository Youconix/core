function Calendar() {
	this.month;
	this.year;
	this.data = {};
	this.startPos = 0;
	this.months = {};
	this.caller = "";	
}
Calendar.prototype.init = function(){
	var _this = this;
	$('#calendar_back').click(function(){
		_this.decreaseMonth();
	});
	$('#calendar_next').click(function(){
		_this.increaseMonth();
	});
}
Calendar.prototype.setWeekStart	= function(startPos){
	this.startPos = startPos;
}
Calendar.prototype.setMonths 	= function(months){
	this.months = months;
}
Calendar.prototype.setMonth	= function(month){
	this.month = month;
}
Calendar.prototype.setYear	= function(year){
	this.year = year;
}
Calendar.prototype.setData	= function(data){
	this.data = data;
}
Calendar.prototype.setCaller	= function(caller){
	this.caller	= caller;
}
Calendar.prototype.increaseMonth = function(){
	this.month++;
			
	if( this.month == 13 ){
		this.month = 1;
		this.year++;
	}
			
	this.display();
}
Calendar.prototype.decreaseMonth = function(){
	this.month--;
			
	if( this.month == 0){
		this.month = 12;
		this.year--;
	}
		
	this.display();
}
Calendar.prototype.display	= function(){
	daysMonth = getDaysMonth(this.month,this.year);
						
	date = new Date();
	date.setDate(1);
	date.setFullYear(this.year);
	date.setMonth((this.month-1));
			
	startdate = date.getDay() - this.startPos;
	if( startdate < 0 ){	startdate = (7-(startdate*-1));	}
			
	$("#calender_days").empty();
	for(i=0; i<startdate; i++){
		$("#calendar_days").append("<li><br/></li>");
	}
			
	for(i=1; i<=daysMonth; i++){
		if( this.data.hasOwnProperty(this.year) && this.data[this.year].hasOwnProperty(this.month) && this.data[this.year][this.month].hasOwnProperty(i) ){
			if( this.caller != "" ){
				$("#calendar_days").append(\'<li class="bold" onclick="calender.callback(\'+i+\')">\'+i+\'</li>\');
			}
			else {
				$("#calendar_days").append(\'<li class="bold">\'+i+\'</li>\');
			}
		}
		else { 
			$("#calendar_days").append("<li>"+i+"</li>");
		}
	}
			
	$("#calendar_month").html(this.months[this.month]+" "+this.year);
}
		
Calendar.prototype.callback	= function(day){
	callback = this.caller+"("+this.month+","+day+","+this.year+")";
	eval(callback);
}
		
calendar = new Calendar();
calendar.init();