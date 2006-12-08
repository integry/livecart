TimeTrack = Class.create();
TimeTrack.prototype = 
{
  	start: false,
  	
	initialize: function()
  	{
		this.start = this.dateToSec(new Date());    
	},
	
	track: function(pointName)
	{
	  	var curr = this.dateToSec(new Date());
	 	addlog(pointName + ' - ' + (curr - this.start));
		this.start = curr; 	
	},
	
	dateToSec: function(dat)
	{
	  	return (dat.getHours() * 3600) + (dat.getMinutes() * 60) + dat.getSeconds() + '.' + dat.getMilliseconds();
	}
	
}