/*
Copyright Scand LLC http://www.scbr.com
To use this component please contact info@scbr.com to obtain license
*/ 
/*
Purpose: item's form  extension for dhtmlxTree
Last updated: 18.01.2006
*/


/**
*   @desc: assign custom form to tree
*	@param: formId - id of form node
*   @type: public
*   @edition: Professional
*   @topic: 0
*/
dhtmlXTreeObject.prototype.defineItemForm=function(formId)
{
    this._IFformId=formId;
    this.enableHighlighting(true);
    this._itemMouseIn=this._IFonMouseOver;
    this._itemMouseOut=function (){return false;};
    this.setOnClickHandler(this._IFonClick);
    this.setOnDblClickHandler(this._IFonDblClick)

    this.setOnClickHandler=this._IFsetOnClickHandler;
    this.setOnDblClickHandler=this._IFsetOnDblClickHandler;

    dhtmlXTreeObject.prototype._HideShow

    this._IFHideShow2=this._HideShow;
    this._HideShow=this._IFHideShow;

    this._IFeventsA=new Array(false,false,false);
    this._IFeventsB=new Array(false,false,false);

}

dhtmlXTreeObject.prototype._IFFromShownCheck=function(item,pItem){
    if (item.parentObject.id==pItem.id) return true;
    if (item.parentObject.id!=this.rootId)
        return this._IFFromShownCheck(item.parentObject,pItem);

    return false;
}

dhtmlXTreeObject.prototype._IFHideShow=function(itemObject,mode){
      if ((this._IFFromShown)&&(this._IFFromShownCheck(this._globalIdStorageFind(this._IFFromShown),itemObject)))
            this.hideItemForm(this._IFFromShown);

      this._IFHideShow2(itemObject,mode);
}


//onmouseover event
dhtmlXTreeObject.prototype._IFonMouseOver=function(){
    var that=this.parentNode.parentObject.treeNod;
    var id=this.parentNode.parentObject.id;
    if (id!=that._IFFromShown){
        if (that._IFeventsB[2]) that.hideItemForm(id,"m");
        if (that._IFeventsA[2]) that.showItemForm(id,"m");
    }
}
//onclick event
dhtmlXTreeObject.prototype._IFonClick=function(id,_a,_b,_c,that,flag){
    that=that||this.treeNod;
    if (id!=that._IFFromShown){
            //to allow correct doubleclisking timeout is necessary
            if ((that._timeoutFormId==id)&&(flag))
                {
                if (that._IFeventsB[0]) that.hideItemForm(id,"c");
                if (that._IFeventsA[0]) that.showItemForm(id,"c");

                that._timeoutFormId=null;
                }
            else
                {
                that._timeoutFormId=id;
                window.setTimeout( function(){ that._IFonClick(id,0,0,0,that,1); },300);
                return;
                }
    }
    if (that._IFaFunc) return  that._IFaFunc(id);
}
//ondblclick event
dhtmlXTreeObject.prototype._IFonDblClick=function(id){
    this._timeoutFormId=null;
    if (id!=this._IFFromShown){
        if (this._IFeventsB[1]) this.hideItemForm(id,"d");
        if (this._IFeventsA[1]) this.showItemForm(id,"d");
    }
    if (this._IFdblclickFuncHandler) return  this._IFdblclickFuncHandler(id);
}

dhtmlXTreeObject.prototype._IFsetOnClickHandler=function(func){
    if (typeof(func)=="function") this._IFaFunc=func; else this._IFaFunc=eval(func);
}

dhtmlXTreeObject.prototype._IFsetOnDblClickHandler=function(func){
    if (typeof(func)=="function") this._IFdblclickFuncHandler=func; else this._IFdblclickFuncHandler=eval(func);
}



/**
*   @desc: define events for form appearing
*	@param: onClick - form appears on click event
*	@param: onDblClick - form appears on double click event
*	@param: onMouseOver - form appears on mouseover event
*   @type: public
*   @edition: Professional
*   @topic: 0
*/
dhtmlXTreeObject.prototype.setFormAppearOn=function(onClick,onDblClick,onMouseOver)
{
    this._IFeventsA[0]=convertStringToBoolean(onClick);
    this._IFeventsA[1]=convertStringToBoolean(onDblClick);
    this._IFeventsA[2]=convertStringToBoolean(onMouseOver);
}
/**
*   @desc: define events for form disappearing
*	@param: onClick - form disappears on click event
*	@param: onDblClick - form disappears on double click event
*	@param: onMouseOver - form disappears on mouseover event
*   @type: public
*   @edition: Professional
*   @topic: 0
*/
dhtmlXTreeObject.prototype.setFormDisappearOn=function(onClick,onDblClick,onMouseOver)
{
    this._IFeventsB[0]=convertStringToBoolean(onClick);
    this._IFeventsB[1]=convertStringToBoolean(onDblClick);
    this._IFeventsB[2]=convertStringToBoolean(onMouseOver);
}

/**
*   @desc: hide item's form
*	@param: itemID -  item id
*   @type: public
*   @edition: Professional
*   @topic: 6
*/
dhtmlXTreeObject.prototype.hideItemForm=function(itemID,state)
{
    if (this._IFFromShown){
    if (this.onIFDism) this.onIFDism(this,itemID,state||"p");
    var form=document.getElementById(this._IFformId);
    form.style.display="none";

    var sNode=this._globalIdStorageFind(this._IFFromShown);
    sNode.span.style.position="";

    this.allTree.appendChild(form);
    this._IFFromShown=false;

    }
}

/**
*   @desc: show item's form
*	@param: itemID -  item id
*   @type: public
*   @edition: Professional
*   @topic: 6
*/
dhtmlXTreeObject.prototype.showItemForm=function(itemID,state)
{
    if  (this._IFFromShown==itemID) return;

    if (this._IFFromShown) this.hideItemForm(this._IFFromShown);

    var sNode=this._globalIdStorageFind(itemID);
    var flag=true;
    if (this.onIFInit) flag=this.onIFInit(this,itemID,state||"p");


    if (flag){
    this._openItem(sNode.parentObject);
    var form=document.getElementById(this._IFformId);
    form.style.display="block";
    sNode.span.style.position="relative";
    sNode.span.style.top="0px";
    sNode.span.style.left="0px";
    sNode.span.appendChild(form);



    var a1=getAbsoluteTop(sNode.span.parentNode);
    var a2=getAbsoluteTop(this.allTree);

		var z=a1-a2;
		if ((z>(this.allTree.scrollTop+this.allTree.offsetHeight-90))||(z<this.allTree.scrollTop))
		this.allTree.scrollTop=z;

    form.style.position="absolute";
    form.style.top="15px";
    var a3=getAbsoluteLeft(sNode.span.parentNode);
    var a4=getAbsoluteLeft(this.allTree);
	form.style.left="-"+(a3-a4)+"px";
    this._IFFromShown=itemID;



   // this._oldonRowClick2=this.onRowClick2;
   // this.onRowClick2=this._IFonRowClick2;

    }
}

/**
*   @desc: allow to set user routine which will be called immideatly before form appears
*	@param: func - user defined function
*   @type: public
*   @edition: Professional
*   @topic: 0
*/
dhtmlXTreeObject.prototype.setOnFormInitialisation=function(func)
{
    if (typeof(func)=="function") this.onIFInit=func; else this.onXLS=eval(func);
}

/**
*   @desc: allow to set user routine which will be called immideatly before form disappears
*	@param: func - user defined function
*   @type: public
*   @edition: Professional
*   @topic: 0
*/
dhtmlXTreeObject.prototype.setOnFormDismissal=function(func)
{
    if (typeof(func)=="function") this.onIFDism=func; else this.onXLS=eval(func);
}



