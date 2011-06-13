/*
Copyright Scand LLC http://www.scbr.com
To use this component please contact info@scbr.com to obtain license
*/ 
/*
Purpose: drag and drop extension
*/

/**
*     @desc: create html element for dragging
*     @type: private
*     @param: htmlObject - html node object
*     @topic: 1
*/

dhtmlXTreeObject.prototype._createDragNode=function(htmlObject,e){
      if (!this.dADTempOff) return null;

     var obj=htmlObject.parentObject;
	 if (!obj.i_sel)
			this._selectItem(obj,e);

      this._checkMSelectionLogic();
      dhtmlObject=htmlObject.parentObject;
		var dragSpan=document.createElement('div');
            if (this._itim_dg){
                    var src=dhtmlObject.span.parentNode.previousSibling.childNodes[0].src;
        			dragSpan.innerHTML="<span><img width='18px' height='18px' src='"+src+"'></span>"+dhtmlObject.label;
            }
            else
    			dragSpan.innerHTML="<span><img width='14px' height='14px' src='"+this.imPath+"red.gif'></span>"+dhtmlObject.label;

			dragSpan.style.position="absolute";
			dragSpan.className="dragSpanDiv";
            this._dragged=(new Array()).concat(this._selected);
        	this._clearMove();
			return dragSpan;
}



/**
*     @desc: marking drag on dragIn
*     @param: func - event handling function
*     @type: private
*     @topic: 4
*/
	dhtmlXTreeObject.prototype._extSetMove=function(htmlObject,x,y){
        if ((!this._itim_dg)&&(this.dragger.dragNode))
            this.dragger.dragNode.childNodes[0].childNodes[0].src=this.imPath+"green.gif";
        this._setMoveA(htmlObject,x,y);
        };
	dhtmlXTreeObject.prototype._extClearMove=function(htmlObject,x,y){
        if ((!this._itim_dg)&&(this.dragger.dragNode))
            this.dragger.dragNode.childNodes[0].childNodes[0].src=this.imPath+"red.gif";
        this._clearMoveA();
        };

    dhtmlXTreeObject.prototype._clearMoveA=dhtmlXTreeObject.prototype._clearMove;
    dhtmlXTreeObject.prototype._clearMove=dhtmlXTreeObject.prototype._extClearMove;

    dhtmlXTreeObject.prototype._setMoveA=dhtmlXTreeObject.prototype._setMove;
    dhtmlXTreeObject.prototype._setMove=dhtmlXTreeObject.prototype._extSetMove;



/**
*     @desc: disable parent-child check while drag and drop
*     @param: mode - 1 - on, 0 - off;
*     @type: public
*     @edition: Professional
*     @topic: 0
*/
    dhtmlXTreeObject.prototype.disableDropCheck=function(mode){
        if ( convertStringToBoolean(mode) )
            {
                if (!this._old_checkPNodes){
                this._old_checkPNodes=this._checkPNodes;
                this._checkPNodes=function() { return 0;  };
                }
            }
        else{
                if (this._old_checkPNodes){
                this._checkPNodes=this._old_checkPNodes;
                this._old_checkPNodes=null;
                }
            }
    }

