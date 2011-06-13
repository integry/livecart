/*
Copyright Scand LLC http://www.scbr.com
To use this component please contact info@scbr.com to obtain license
*/ 
/*
Purpose: sorting extension for dhtmlxTree
Last updated: 03.05.2005
*/


/**
*     @desc: reorder items in tree
*     @type: public
*     @param: nodeId - id of top node
*     @param: all_levels - sorting all levels or only current level
*     @param: order - sorting order - ASC or DES
*     @edition: Professional
*     @topic: 0
*/

dhtmlXTreeObject.prototype.sortTree=function(nodeId,order,all_levels)
	{
		var sNode=this._globalIdStorageFind(nodeId);
		if (!sNode) return false;

		this._reorderBranch(sNode,(order=="ASC"),convertStringToBoolean(all_levels))
	};

/**
*     @desc: set custom sort functions, which has two parametrs - id_of_item1,id_of_item2
*     @type: public
*     @param: func - sorting function
*     @edition: Professional
*     @topic: 0
*/
dhtmlXTreeObject.prototype.setCustomSortFunction=function(func)
	{
        this._csfunca=func;
	};
	
	
dhtmlXTreeObject.prototype._reorderBranch=function(node,order,all_levels){
	var m=new Array();
	var count=node.childsCount;
	if (!count) return;

	var parent =	node.childNodes[0].tr.parentNode;
	for (var i=0; i<count; i++){
			m[i]=node.childNodes[i];
			parent.removeChild(m[i].tr);
			}

var self=this;
if (order)
    if(this._csfunca)
    	m.sort( function(a,b){ return self._csfunca(a.id,b.id); } );
    else
    	m.sort( function(a,b){ return ((a.span.innerHTML.toUpperCase()>b.span.innerHTML.toUpperCase())?1:((a.span.innerHTML.toUpperCase()==b.span.innerHTML.toUpperCase())?0:-1)) } );
else
    if(this._csfunca)
    	m.sort( function(a,b){ return self._csfunca(b.id,a.id); } );
    else
    	m.sort( function(a,b){ return ((a.span.innerHTML.toUpperCase()<b.span.innerHTML.toUpperCase())?1:((a.span.innerHTML.toUpperCase()==b.span.innerHTML.toUpperCase())?0:-1)) } );

	for (var i=0; i<count; i++){
		parent.appendChild(m[i].tr);
		node.childNodes[i]=m[i];
		
		if ((all_levels)&&(m[i].unParsed))
			m[i].unParsed.setAttribute("order",order?1:0);
		else
		if ((all_levels)&&(m[i].childsCount))
			this._reorderBranch(m[i],order,all_levels);
		
		}
	
	for (var i=0; i<count; i++){
		this._correctPlus(m[i]);
		this._correctLine(m[i]);
		}
}

dhtmlXTreeObject.prototype._reorderXMLBranch=function(node){
	var orderold=node.getAttribute("order");
    if (orderold=="none") return;
	var order=(orderold==1);
	var count=node.childNodes.length;
	if (!count) return;

	var m=new Array();
    var j=0;

	for (var i=0; i<count; i++)
        if (node.childNodes[i].nodeType==1)
    		{ m[j]=node.childNodes[i]; j++ }

	for (var i=count-1; i!=0; i--)
		node.removeChild(node.childNodes[i]);


	if (order)
		m.sort( function(a,b){ return ((a.getAttribute("text")>b.getAttribute("text"))?1:((a.getAttribute("text")==b.getAttribute("text"))?0:-1)) } );
	else
		m.sort( function(a,b){ return ((a.getAttribute("text")<b.getAttribute("text"))?1:((a.getAttribute("text")==b.getAttribute("text"))?0:-1)) } );

	for (var i=0; i<j; i++){
		m[i].setAttribute("order",orderold);
		node.appendChild(m[i]);
		}

    node.setAttribute("order","none");
}
