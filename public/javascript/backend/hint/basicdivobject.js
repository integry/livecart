/*
Script by RoBorg
RoBorg@RoBorg.co.uk
http://www.roborg.co.uk
Do NOT remove this message!
*/

/*
Usage:
myDivObj = new divObject('myDivId', 'document.');
onload="activateDivs()"
myDiv.write('text')
myDiv.setBgColour('#xxxxxx')
myDiv.hide()
myDiv.show()
myDiv.swapImage('imgName', 'src')
myDiv.captureEvents('event', 'action')
myDiv.moveTo(x, y)
myDiv.moveby(x, y)
myDiv.resizeTo(x, y)
myDiv.resizeBy(x, y)
myDiv.clip(top, left, width, height)
*/

divObjectArray = new Array();

function activateDivs()
{
	for(var x=0; x<divObjectArray.length; x++)
		divObjectArray[x].activate();
}

function divObject(divName, parent)
{
	if(!browserVars.type.getById)
	{
		this.div = parent + divName;
		this.baseDiv = parent + divName;
		this.divName = parent + divName;
		this.write = new Function("text", "this.div.document.open(); this.div.document.write(text); this.div.document.close(); this.width = this.div.clip.width; this.height = this.div.clip.height;");
		this.setBgColour = new Function("colour", "this.div.bgColor = colour;");
		this.setBgImage = new Function("src", "this.div.bgImage = 'url(' + src + ')';");
		this.hide = new Function("this.div.visibility = 'hide';");
		this.show = new Function("this.div.visibility = 'inherit';");
		this.setSize = new Function("left", "top", "width", "height", "this.div.clip.left = left; this.div.clip.top = top; this.div.clip.width = width; this.div.clip.height = height; this.div.width = width; this.div.height = height; this.div.clip.height = height; this.width = width; this.height = height;");
		this.swapImage = new Function("image", "src", "this.div.document.images[image].src = src");
		this.getImageSrc = new Function("image", "return this.div.document.images[image].src");
	}
	else
	{
		this.div = divName;
		this.baseDiv = divName;
		this.divName = divName;
		this.write = new Function("text", "document.getElementById('" + divName + "').innerHTML = text; this.width = this.baseDiv.offsetWidth; this.height = this.baseDiv.offsetHeight;");
		this.setBgColour = new Function("colour", "this.div.backgroundColor = colour;");
		this.setBgImage = new Function("src", "this.div.backgroundImage = 'url(' + src + ')';");
		this.hide = new Function("this.div.visibility = 'hidden';");
		this.show = new Function("this.div.visibility = 'inherit';");
		this.setSize = new Function("left", "top", "width", "height", "setWidthAndHeight", "this.div.clip = 'rect(' + top + ',' + (left+width) + ',' + (top+height) + ',' + left + ')'; if(setWidthAndHeight != false){this.div.width = width; this.div.height = height; this.width = width; this.height = height;}");
		this.swapImage = new Function("image", "src", "document.images[image].src = src");
		this.getImageSrc = new Function("image", "return document.images[image].src");
	}
	this.activate = activateDiv;
	this.setXY = new Function("x", "y", "this.div.left = x; this.div.top = y;");
	this.captureEvents = captureDivEvents;
	this.idNo = divObjectArray.length;
	divObjectArray[divObjectArray.length] = this;
	
	this.width = 0;
	this.height = 0;
	this.originalWidth = 0;
	this.originalHeight = 0;
	this.left = 0;
	this.top = 0;
	this.clipLeft = 0;
	this.clipTop = 0;
	
	this.moveTo = new Function("x", "y", "this.left=x; this.top=y; this.setXY(x, y)");
	this.moveBy = new Function("x", "y", "this.left+=x; this.top+=y; this.setXY(this.left, this.top)");
	this.resizeTo = new Function("x", "y", "this.width=x; this.height=y; this.setSize(this.clipLeft, this.clipTop, this.width, this.height)");
	this.resizeBy = new Function("x", "y", "this.width+=x; this.height+=y; this.setSize(this.clipLeft, this.clipTop, this.width, this.height)");
	this.clip = new Function("left", "top", "width", "height", "setWidthAndHeight", "this.clipLeft=left; this.clipTop=top; this.clipWidth=width; this.clipHeight=height; this.setSize(left, top, width, height, setWidthAndHeight);");
	this.setZIndex = new Function("z", "this.div.zIndex=z;");
}


function activateDiv()
{
	if(typeof(this.div) != 'string') return;

	if(!browserVars.type.getById)
	{
		this.baseDiv = eval(this.div);
		this.div = this.baseDiv
		
		this.width = this.div.clip.width;
		this.height = this.div.clip.height;
		this.left = this.div.left;
		this.top = this.div.top;
	}
	else
	{
		this.baseDiv = document.getElementById(this.div);
		this.div = this.baseDiv.style;
		
		this.width = this.baseDiv.offsetWidth;
		this.height = this.baseDiv.offsetHeight;
		this.left = this.baseDiv.offsetLeft;
		this.top = this.baseDiv.offsetTop;
	}
	
	this.clipWidth = this.width;
	this.clipHeight = this.height;
	this.originalWidth = this.width;
	this.originalHeight = this.height;
}


function captureDivEvents(eventName, action)
{
	if(!browserVars.type.getById)
		eval('this.div.captureEvents(Event.' + eventName.toUpperCase() + ')');
	eval('this.baseDiv.on' + eventName + ' = new Function("e", "' + action + '")');
}