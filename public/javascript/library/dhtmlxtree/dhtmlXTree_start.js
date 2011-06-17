function dhtmlXTreeFromHTML(obj){
			if (typeof(obj)!="object")
				obj=document.getElementById(obj);

            var n=obj;
			var id=n.id;
			for (var j=0; j<obj.childNodes.length; j++)
				if (obj.childNodes[j].nodeType=="1"){
					if (obj.childNodes[j].tagName=="XMP")
						cont=obj.childNodes[j].firstChild.data;
					else if (obj.childNodes[j].tagName.toLowerCase()=="ul")
						cont=dhx_li2trees(obj.childNodes[j],new Array(),0);
					break;
					}
			obj.innerHTML="";

			var t=new dhtmlXTreeObject(obj,"100%","100%",0);
			var z_all=new Array();
			for ( b in t )
				z_all[b.toLowerCase()]=b;

			var atr=obj.attributes;
			for (var a=0; a<atr.length; a++)
				if ((atr[a].name.indexOf("set")==0)||(atr[a].name.indexOf("enable")==0)){
					var an=atr[a].name;
                    if (!t[an])
						an=z_all[atr[a].name];
					t[an].apply(t,atr[a].value.split(","));
					}

			if (typeof(cont)=="object"){
			    t.XMLloadingWarning=1;
				for (var i=0; i<cont.length; i++)
					t.insertNewItem(cont[i][0],(i+1),cont[i][1]);
				t.XMLloadingWarning=0;
				t.lastLoadedXMLId=0;
				t._redrawFrom(t);
			}
			else
			t.loadXMLString("<tree id='0'>"+cont+"</tree>");
            window[id]=t;
			return t;
}

function dhx_init_trees(){
    var z=document.getElementsByTagName("div");
    for (var i=0; i<z.length; i++)
        if (z[i].className=="dhtmlxTree")
			dhtmlXTreeFromHTML(z[i])
}

function dhx_li2trees(tag,data,ind){
	for (var i=0; i<tag.childNodes.length; i++){
        var z=tag.childNodes[i];
		if ((z.nodeType==1)&&(z.tagName.toLowerCase()=="li")){
			var c=""; var ul=null;
				for (var j=0; j<z.childNodes.length; j++){
				var zc=z.childNodes[j];
				if (zc.nodeType==3) c+=zc.data;
				else if (zc.tagName.toLowerCase()!="ul")  c+=zc.outerHTML?zc.outerHTML:zc.innerHTML;
					 else ul=zc;
			}

			data[data.length]=[ind,c];
			if (ul)
				data=dhx_li2trees(ul,data,data.length);
		}
	}
	return data;
}


if (window.addEventListener) window.addEventListener("load",dhx_init_trees,false);
else    if (window.attachEvent) window.attachEvent("onload",dhx_init_trees);



