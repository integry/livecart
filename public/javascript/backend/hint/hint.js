
/*
Script by RoBorg
RoBorg@geniusbug.com
http://javascript.geniusbug.com | http://www.roborg.co.uk
Please do not remove or edit this message
Please link to this website if you use this script!
*/

/**
 * Denis Slaveckij made some changes
 * denis@integry.net
 */


hintArray = new Array();

simpleHint = new hint(false, 500);
movingHint = new hint(true);

document.write(writeHints()); 

function hint(moveWithMouse, delay)
{
	this.id = hintArray.length;
	hintArray[this.id] = this;
	this.CSS = '#hintDiv' + this.id + ' {position:absolute; border:1px #000000 solid; visibility:hidden;z-index:100}';
	this.HTML = '<div id="hintDiv' + this.id + '"> </div>';
	this.div = new divObject('hintDiv' + this.id, 'document.');
	this.activate = new Function("this.div.activate(); this.div.setBgColour(this.backgroundColour);");
	this.show = hintShow;
	this.shown = false;
	//denis
	//this.backgroundColour = bgColour!=null?bgColour:'#FFFFE1';
	this.delay = delay!=null?delay:0;
	this.showTimer = false;
	this.setBackgroundColour = new Function("bgColour", "this.backgroundColour = bgColour; this.div.setBgColour(bgColour);");
	this.move = hintMove;
	this.moveTimer = setInterval('if(hintArray[' + this.id + '].moveWithMouse) hintArray[' + this.id + '].move();', 50);
	this.moveWithMouse = moveWithMouse;
	this.width = 0;
}

function hintShow(message, bgColour)
{
	if(typeof(message) == 'undefined')
	{
		if(this.showTimer) 
			clearTimeout(this.showTimer);
		this.showTimer = false;
		this.div.hide();
		this.shown = false;
		return;
	}
	//denis	
	this.div.setBgColour(bgColour!=null?bgColour:'#FFFFE1');
	//end denis
	this.div.write('<nobr>' + message + '</nobr>');
	this.move();

	if(browserVars.type.ns4) this.width = this.div.div.clip.width;
	else this.width = this.div.baseDiv.offsetWidth;

	this.showTimer = setTimeout('hintArray[' + this.id + '].div.show(); hintArray[' + this.id + '].shown = true;', this.delay);
}



function hintMove()
{
	browserVars.updateVars();
	var screenRight = browserVars.scrollLeft + browserVars.width - 16;
	var x = browserVars.mouseX + 16;
	var y = browserVars.mouseY + 16;

	if(x + this.width > screenRight)
	{
		x = screenRight - this.width;
		y += 16;
	}

	if(x < 0) x = 0;

	this.div.moveTo(x, y);
}



function writeHints()
{
	var CSS = '';
	var HTML = ''
	for(var x=0; x<hintArray.length; x++)
	{
		CSS += hintArray[x].CSS;
		HTML += hintArray[x].HTML;
	}
	return '<style>' + CSS + '</style>' + HTML;
}



function activateHints()
{
	for(var x=0; x<hintArray.length; x++)
		hintArray[x].activate();
}


