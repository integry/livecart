/*
Purpose: temporary loading XML item
Warining - this extension is an experimental and not fully compatible
*/




	dhtmlXTreeObject.prototype.setOnLoadingStart_lf=function(func){
        if (typeof(func)=="function") this.onXLS_2=func; else this.onXLS_2=eval(func); };
	dhtmlXTreeObject.prototype.setOnLoadingEnd_lf=function(func){
        if (typeof(func)=="function") this.onXLE_2=func; else this.onXLE_2=eval(func); };

/**
*     @desc: enable/disable loading XMl item
*     @param: text - text of temporary item
*     @edition: Experimental
*     @type: public
*     @topic: 0
*/
 	dhtmlXTreeObject.prototype.enableLoadingItem=function(text) {
            this.setOnLoadingStart(this._showFakeItem);
            this.setOnLoadingEnd(this._hideFakeItem);

            this.setOnLoadingStart=this.setOnLoadingStart_lf;
            this.setOnLoadingEnd=this.setOnLoadingEnd_lf;
            this._tfi_text=text||"Loading...";
    };


 	dhtmlXTreeObject.prototype._showFakeItem=function(tree,id) {
        if (this.onXLS_2) this.onXLS_2(tree,id);
        if ((id===null)||(this._globalIdStorageFind("fake_load_xml_"+id))) return;
        this.insertNewItem(id,"fake_load_xml_"+id,this._tfi_text);
    }
 	dhtmlXTreeObject.prototype._hideFakeItem=function(tree,id) {
        if (this.onXLE_2) this.onXLE_2(tree,id);
        if (id===null) return;
        this.deleteItem("fake_load_xml_"+id);
    }
