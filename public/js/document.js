/**
 * Class to work with javascript document model. Etc. to get layers, and other
 */	
function DocumentHelper() {
 	
	 var agt=navigator.userAgent.toLowerCase();
	
    // *** BROWSER VERSION ***
    // Note: On IE5, these return 4, so use is_ie5up to detect IE5.
    var is_major = parseInt(navigator.appVersion);
    var is_minor = parseFloat(navigator.appVersion);

    // Note: Opera and WebTV spoof Navigator.  We do strict client detection.
    // If you want to allow spoofing, take out the tests for opera and webtv.
    this.is_nav  = ((agt.indexOf('mozilla')!=-1) && (agt.indexOf('spoofer')==-1)
                && (agt.indexOf('compatible') == -1) && (agt.indexOf('opera')==-1)
                && (agt.indexOf('webtv')==-1) && (agt.indexOf('hotjava')==-1));
                
    this.is_nav6up = (this.is_nav && (is_major >= 5));
    this.is_gecko = (agt.indexOf('gecko') != -1);

    this.is_ie   = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
    this.is_ie4  = (this.is_ie && (is_major == 4) && (agt.indexOf("msie 4")!=-1) );
    this.is_ie4up= (this.is_ie && (is_major >= 4));
	
	this.is_opera  = (agt.indexOf("opera") != -1);
	this.is_opera7 = (this.is_opera && is_major >= 7) || agt.indexOf("opera 7") != -1;

	// Patch from Harald Fielker
    if (agt.indexOf('konqueror') != -1) {
        
		this.is_nav   = false;
        this.is_nav6up= false;
        this.is_gecko = false;
        this.is_ie    = true;
        this.is_ie4   = true;
        this.is_ie4up = true;
    }
}

/**
 * Gets layer by id.
 * @param string
 */ 
DocumentHelper.prototype.getLayer = function (layerID) {

	if (this.is_ie4) {
	  
		return document.all(layerID);
	} else if (document.getElementById(layerID)) {
	  
		return document.getElementById(layerID);
	} else if (document.all && document.all(layerID)) {
	  
		return document.all(layerID);
	}
}

/** 
 * Deletes layer by id.
 * @param string
 */
DocumentHelper.prototype.deleteLayer = function (layer) {
	
  	if (layer) {
		    
		if (document.all) {
		
			layer.innerHTML='';
			layer.outerHTML='';
		} else {
			
			layer.style.display = 'none';
			layer.innerHTML='';
			delete layer;
		} 
	}
}	


function replaceTags(s) {
	 	 
	s = replace(s, "<", "&lt;");
	s = replace(s, ">", "&gt;");	
	return s;		
}	

function replace(string, text, by) {

    var strLength = string.length, txtLength = text.length;
    if ((strLength == 0) || (txtLength == 0)) return string;

    var i = string.indexOf(text);
    if ((!i) && (text != string.substring(0,txtLength))) return string;
    if (i == -1) return string;

    var newstr = string.substring(0,i) + by;

    if (i+txtLength < strLength)
        newstr += replace(string.substring(i+txtLength,strLength),text,by);

    return newstr;
}