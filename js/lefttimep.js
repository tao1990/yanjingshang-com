
/*-----------------------------------------------限时抢购商品（一个）控制 2011-6-10---------------------------------------------*/

//初始化变量
var auctionDate = 0;
var _GMTEndTime = 0;
var showTime = "leftTime";
var _day     = 'day';
var _hour    = 'hour';
var _minute  = 'minute';
var _second  = 'second';
var _end     = 'end';

var cur_date = new Date();
var startTime = cur_date.getTime();
var Temp;
var timerID = null;
var timerRunning = false;

function showtime()
{
  now = new Date();
  var ts = parseInt((startTime - now.getTime()) / 1000) + auctionDate;
  var dateLeft   = 0;
  var hourLeft   = 0;
  var minuteLeft = 0;
  var secondLeft = 0;
  var hourZero   = '';
  var minuteZero = '';
  var secondZero = '';
  if (ts < 0)
  {
    ts        = 0;
    CurHour   = 0;
    CurMinute = 0;
    CurSecond = 0;
  }else{
    dateLeft = parseInt(ts / 86400);
    ts = ts - dateLeft * 86400;
    hourLeft = parseInt(ts / 3600);
    ts = ts - hourLeft * 3600;
    minuteLeft = parseInt(ts / 60);
    secondLeft = ts - minuteLeft * 60;
  }

  if(hourLeft < 10)
  {
    hourZero = '0';
  }
  if (minuteLeft < 10)
  {
    minuteZero = '0';
  }
  if (secondLeft < 10)
  {
    secondZero = '0';
  }

  if (dateLeft > 0)
  {
    Temp = "<span>剩余时间:<span class='line_6px qg'></span><span class='dhuang'>"+(dateLeft*24+hourLeft)+"<\/span><span class='line_6px'></span><span class='dhuang'>"+ minuteZero + minuteLeft +"<\/span><span class='line_6px'></span><span class='dhuang'>"+secondZero + secondLeft +"<\/span><\/span>";
  }
  else
  {
    if (hourLeft > 0)
    {
	  Temp = "<span>剩余时间:<span class='line_6px qg'></span><span class='dhuang'>"+ hourZero + hourLeft +"<\/span><span class='line_6px'></span><span class='dhuang'>"+ minuteZero + minuteLeft +"<\/span><span class='line_6px'></span><span class='dhuang'>"+secondZero + secondLeft +"<\/span><\/span>";
    }
    else
    {
      if (minuteLeft > 0)
      {
		Temp = "<span>剩余时间:<span class='line_6px qg'></span><span class='dhuang'>"+ hourZero + hourZero +"<\/span><span class='line_6px'></span><span class='dhuang'>"+ minuteZero + minuteLeft +"<\/span><span class='line_6px'></span><span class='dhuang'>"+secondZero + secondLeft +"<\/span><\/span>";		
      }
      else
      {
        if (secondLeft > 0)
        {	
		  Temp = "<span>剩余时间:<span class='line_6px qg'></span><span class='dhuang'>"+ hourZero + hourZero +"<\/span><span class='line_6px'></span><span class='dhuang'>"+ minuteZero + minuteZero +"<\/span><span class='line_6px'></span><span class='dhuang'>"+secondZero + secondLeft +"<\/span><\/span>";	  
        }
        else
        {
          Temp = '';
        }
      }
    }
  }

  if(auctionDate <= 0 || Temp == '')
  {
    Temp = "<span>剩余时间:<span class='line_6px qg'></span><span class='dhuang'>"+ hourZero + hourZero +"<\/span><span class='line_6px'></span><span class='dhuang'>"+ minuteZero + minuteZero +"<\/span><span class='line_6px'></span><span class='dhuang'>"+secondZero + secondZero +"<\/span><\/span>";
    stopclock();
  }

  if(document.getElementById(showTime))
  {
    document.getElementById(showTime).innerHTML = Temp;
  }

  timerID = setTimeout("showtime()", 1000);
  timerRunning = true;
}

var timerID = null;
var timerRunning = false;
function stopclock()
{
  if(timerRunning)
  {
    clearTimeout(timerID);
  }
  timerRunning = false;
}
function macauclock()
{
  stopclock();
  showtime();
}
function onload_leftTimep(now_time)
{
  /*第一次运行时初始化语言项目*/
  try
  {
    _GMTEndTime = gmt_end_time1;
    // 剩余时间
    _day    = day;
    _hour   = hour;
    _minute = minute;
    _second = second;
    _end    = end;
  }catch(e){}

  if (_GMTEndTime > 0)
  {
    if (now_time == undefined)
    {
      var tmp_val = parseInt(_GMTEndTime) - parseInt(cur_date.getTime() / 1000 + cur_date.getTimezoneOffset() * 60);
    }
    else
    {
      var tmp_val = parseInt(_GMTEndTime) - now_time;
    }
    if (tmp_val > 0)
    {
      auctionDate = tmp_val;
    }
  }
  macauclock();
}

//-----------------------------------------------------------------下期抢购控制-----------------------------------------------------
function CountDown(){   
	if(maxtime>=0){   
		tsz=maxtime;
		dateLeftz = parseInt(tsz / 86400);
		tsz = tsz - dateLeftz * 86400;
		hourLeftz = parseInt(tsz / 3600);
		tsz = tsz - hourLeftz * 3600;
		minuteLeftz = parseInt(tsz / 60);
		secondLeftz = tsz - minuteLeftz * 60;
	
	
		hourLeftz=dateLeftz*24+hourLeftz;
		
		if (hourLeftz < 10)
		{
			hourLeftz = ''+hourLeftz;
		}
		if (minuteLeftz < 10)
		{
			minuteLeftz = '0'+minuteLeftz;
		}
		if (secondLeftz < 10)
		{
			secondLeftz = '0'+secondLeftz;
		}
	
		var Tempz;
		Tempz = "<span >离开始时间：<span class='dhuang'>"+(hourLeftz)+"<\/span>时<span class='dhuang'>"+ minuteLeftz +"<\/span>分<span class='dhuang'>"+secondLeftz +"<\/span>秒<\/span>";	
		document.getElementById("showTimez").innerHTML = Tempz;	
		--maxtime;   
	}   
	else{   
		clearInterval(timer);   
	}   
}   
//---------------------------------------------------------------下期抢购end-----------------------------------------------------
