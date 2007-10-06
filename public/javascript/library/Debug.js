/**
 *	@author Integry Systems
 */
 
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
	 	console.log(pointName + ' - ' + (curr - this.start));
		this.start = curr; 	
	},
	
	dateToSec: function(dat)
	{
		var ret = (dat.getHours() * 3600) + (dat.getMinutes() * 60) + dat.getSeconds() + '.' + dat.getMilliseconds();
	  	return ret;
	}
	
}

function print_r(input, _indent)
{
    if(typeof(_indent) == 'string') {
        var indent = _indent + '    ';
        var paren_indent = _indent + '  ';
    } else {
        var indent = '    ';
        var paren_indent = '';
    }
    switch(typeof(input)) {
        case 'boolean':
            var output = (input ? 'true' : 'false') + "\n";
            break;
        case 'object':
            if ( input===null ) {
                var output = "null\n";
                break;
            }
            var output = ((input.reverse) ? 'Array' : 'Object') + " (\n";
            for(var i in input) {
                output += indent + "[" + i + "] => " + print_r(input[i], indent);
            }
            output += paren_indent + ")\n";
            break;
        case 'number':
        case 'string':
        default:
            var output = "" + input  + "\n";
    }
    return output;
}

function addlog(info)
{
	document.getElementById('log').innerHTML += info + '<br />';
}