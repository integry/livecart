/* Browser Detection Script */
browserVars = new browserVarsObj();
if(!browserVars.type.getById) document.captureEvents(Event.MOUSEMOVE)
document.onmousemove = new Function('e', 'browserVars.updateMouse(e)');

function browserDetect()
{
this.getById = document.getElementById?true:false;
this.layers = document.layers?true:false;
this.ns4 = ((this.layers) && (!this.getById));
this.ns6 = ((navigator.userAgent.indexOf('Netscape6') != -1) && (this.getById));
this.moz = ((navigator.appName.indexOf('Netscape') != -1) && (this.getById) && (!this.ns6));
this.ie  = ((!this.layers) && (this.getById) && (!(this.ns6 || this.moz)));
this.opera = window.opera?true:false;
}


function browserVarsObj()
{
this.updateMouse = browserVarsObjUpdateMouse;
this.updateVars = browserVarsObjUpdateVars;

this.mouseX = 0;
this.mouseY = 0;

this.type = new browserDetect();
this.width = 0;
this.height = 0
this.screenWidth = screen.width;
this.screenHeight = screen.height;
this.scrollWidth = 0;
this.scrollHeight = 0;
this.scrollLeft = 0;
this.scrollTop = 0;
this.updateVars();
}

function browserVarsObjUpdateMouse(e)
{
if(!this.type.ie)
{
this.mouseX = e.pageX;
this.mouseY = e.pageY;
}
else
{
this.mouseX = window.event.clientX + this.scrollLeft;
this.mouseY = window.event.clientY + this.scrollTop;
}
}

function browserVarsObjUpdateVars()
{
if(!this.type.getById)
{
this.width = window.innerWidth;
this.height = window.innerHeight;
this.scrollWidth = document.width;
this.scrollHeight = document.height;
this.scrollLeft = window.pageXOffset;
this.scrollTop = window.pageYOffset;
if(this.width < this.scrollWidth) this.width -= 16
if(this.height < this.scrollHeight) this.height -= 16
}
else
{
if((!(this.type.ns6 || this.type.moz)) && (document.body))
{
this.width = document.body.offsetWidth;
this.height = document.body.offsetHeight;
this.scrollWidth = document.body.scrollWidth;
this.scrollHeight = document.body.scrollHeight;
this.scrollLeft = document.body.scrollLeft;
this.scrollTop = document.body.scrollTop;
}
if((this.type.ns6 || this.type.moz) && (document.body))
{
this.width = window.innerWidth;
this.height = window.innerHeight;
this.scrollWidth = document.body.scrollWidth;
this.scrollHeight = document.body.scrollHeight;
this.scrollLeft = window.pageXOffset;
this.scrollTop = window.pageYOffset;
}
}
}
