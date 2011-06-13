/*
Purpose: keyboard navigation extension
*/
document.write("<style>.a_dhx_hidden_input{ position:absolute;  top:0px; left:0px; width:1px; height:1px; border:none; background:none; }</style>");

/**
*     @desc: enable keyboard navigation in tree
*     @param: mode - true/false
*     @edition: Professional
*     @type: public
*     @topic: 4
*/
dhtmlXTreeObject.prototype.enableKeyboardNavigation=function(mode){
        this._enblkbrd=convertStringToBoolean(mode);
        if (this._enblkbrd){
            if (_isFF){
                var z=window.getComputedStyle(this.parentObject,null)["position"];
                if ((z!="absolute")&&(z!="relative"))
                    this.parentObject.style.position="relative";
                }
            this._navKeys=[["up",38],["down",40],["open",39],["close",37],["call",13],["edit",113]];
            var self=this;
            var z=document.createElement("INPUT");
                z.className="a_dhx_hidden_input";
            this.parentObject.appendChild(z);
            this.parentObject.onkeydown=function(e){
                self._onKeyDown(e||window.event)
            }
            this.parentObject.onclick=function(e){
                    var tz=document.body.scrollTop;
                    self.parentObject.lastChild.focus();
                    //workaround for long trees
                    if (_isIE)
                        window.setTimeout(function (){document.body.scrollTop=tz; },0);
                    else
                        document.body.scrollTop=tz;
            }
        }
        else
            this.parentObject.onkeydown=null;
}


dhtmlXTreeObject.prototype._onKeyDown=function(e){
    var self=this;
    for (var i=0; i<this._navKeys.length; i++)
        if (this._navKeys[i][1]==e.keyCode){
            eval("self._onkey_"+this._navKeys[i][0]+"();");
            if (e.preventDefault) e.preventDefault();
			(e||event).cancelBubble=true;
            return false;
            }
    return true;
}

dhtmlXTreeObject.prototype._onkey_up=function(){
   	var temp=this._globalIdStorageFind(this.getSelectedItemId());
    if (!temp) return;
    var next=this._getPrevVisibleNode(temp);
    if (next.id==this.rootId) return;
    this.focusItem(next.id);
    this.selectItem(next.id,false);
}
dhtmlXTreeObject.prototype._onkey_down=function(){
   	var temp=this._globalIdStorageFind(this.getSelectedItemId());
    if (!temp) return;
    var next=this._getNextVisibleNode(temp);
    if (next.id==this.rootId) return;
    this.focusItem(next.id);
    this.selectItem(next.id,false);
}
dhtmlXTreeObject.prototype._onkey_open=function(){
    this.openItem(this.getSelectedItemId());
}
dhtmlXTreeObject.prototype._onkey_close=function(){
    this.closeItem(this.getSelectedItemId());
}
dhtmlXTreeObject.prototype._onkey_call=function(){
	if (this.stopEdit){
		this.stopEdit();
		this.parentObject.lastChild.focus();
		this.parentObject.lastChild.focus();
	    this.selectItem(this.getSelectedItemId());
		}
	else
	    this.selectItem(this.getSelectedItemId(),true);
}
dhtmlXTreeObject.prototype._onkey_edit=function(){
	if (this.editItem)
   		this.editItem(this.getSelectedItemId());
}


dhtmlXTreeObject.prototype._getNextVisibleNode=function(item,mode){
	if ((!mode)&&(this._getOpenState(item)>0)) return item.childNodes[0];
	if ((item.tr)&&(item.tr.nextSibling)&&(item.tr.nextSibling.nodem))
    	return item.tr.nextSibling.nodem;

    if (item.parentObject) return  this._getNextVisibleNode(item.parentObject,1);
	return item;
};

dhtmlXTreeObject.prototype._getPrevVisibleNode=function(item){
	if ((item.tr)&&(item.tr.previousSibling)&&(item.tr.previousSibling.nodem))
    	return this._lastVisibleChild(item.tr.previousSibling.nodem);

	if (item.parentObject)
		return item.parentObject;
	else return item;
};

dhtmlXTreeObject.prototype._lastVisibleChild=function(item){
	if (this._getOpenState(item)>0)
		return this._lastVisibleChild(item.childNodes[item.childsCount-1]);
	else return item;
};


/**
*     @desc: configure keys used for keyboard navigation
*     @param: keys - configuration array, please check samples/pro_key_nav.html for more details
*     @edition: Professional
*     @type: public
*     @topic: 4
*/
dhtmlXTreeObject.prototype.assignKeys=function(keys){
      this._navKeys=keys;
}
