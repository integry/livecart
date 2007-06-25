/**
 *  Template editor
 */
Backend.Template = Class.create();
Backend.Template.prototype = 
{
  	treeBrowser: null,
  	
  	urls: new Array(),
	  
	initialize: function(categories)
	{
		this.treeBrowser = new dhtmlXTreeObject("templateBrowser","","", false);
		
		this.treeBrowser.def_img_x = 'auto';
		this.treeBrowser.def_img_y = 'auto';
				
		this.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		this.treeBrowser.setOnClickHandler(this.activateCategory.bind(this));

		this.treeBrowser.showFeedback = 
			function(itemId) 
			{
				if (!this.iconUrls)
				{
					this.iconUrls = new Object();	
				}
				
				this.iconUrls[itemId] = this.getItemImage(itemId, 0, 0);
				this.setItemImage(itemId, '../../../image/indicator.gif');
			}
		
		this.treeBrowser.hideFeedback = 
			function()
			{
				for (var itemId in this.iconUrls)
				{
					this.setItemImage(itemId, this.iconUrls[itemId]);	
				}				
			}
		
    	this.insertTreeBranch(categories, 0);    
    	this.treeBrowser.closeAllItems();
	},
	
	insertTreeBranch: function(treeBranch, rootId)
	{
		for (k in treeBranch)
		{
		  	if('function' != typeof treeBranch[k])
		  	{
				this.treeBrowser.insertNewItem(rootId, treeBranch[k].id, k, null, 0, 0, 0, '');
				
				if (treeBranch[k].subs)
				{
					this.insertTreeBranch(treeBranch[k].subs, treeBranch[k].id);
				}
			}
		}  	
	},    
	
	activateCategory: function(id)
	{
        if (!this.treeBrowser.hasChildren(id))
		{
			this.treeBrowser.showFeedback(id);
			var url = this.urls['edit'].replace('_id_', id);
			var upd = new LiveCart.AjaxUpdater(url, 'templateContent');
			upd.onComplete = this.displayTemplate.bind(this);
			if ($('code'))
			{
				editAreaLoader.delete_instance("code");
			}
		}
	},	
	
	displayTemplate: function(response)
	{
		this.treeBrowser.hideFeedback();
		Event.observe($('cancel'), 'click', this.cancel.bindAsEventListener(this));
		new Backend.TemplateHandler($('templateForm'));
	},

    cancel: function()
    {
		new LiveCart.AjaxUpdater(this.urls['empty'], 'templateContent', 'settingsIndicator');        
    }	
}

/**
 *  Template editor form handler
 */
Backend.TemplateHandler = Class.create();
Backend.TemplateHandler.prototype = 
{
	form: null,
	
	initialize: function(form)
	{
		this.form = form;
		this.form.onsubmit = this.submit.bindAsEventListener(this);
		
		editAreaLoader.init({
			id : "code",		// textarea id
			syntax: "html",			// syntax to be uses for highgliting
			start_highlight: true,		// to display with highlight mode on start-up
			allow_toggle: false,
			allow_resize: true
			}
		);
		
		// set cursor at the first line
		editAreaLoader.setSelectionRange('code', 0, 0);		
	},
	
	submit: function()
	{
		$('code').value = editAreaLoader.getValue('code');
		new LiveCart.AjaxRequest(this.form, null, this.saveComplete.bind(this));
		return false;
	},
	
	saveComplete: function(originalRequest)
	{
		var msgClass = originalRequest.responseText ? 'yellowMessage' : 'redMessage';			 
		var msg = new Backend.SaveConfirmationMessage(document.getElementsByClassName(msgClass)[0]);
		 
		msg.show();
		 
		if (opener)
		{
            opener.location.reload();	            
        }
	}
}

function decode64(inp)
{

var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + //all caps
"abcdefghijklmnopqrstuvwxyz" + //all lowercase
"0123456789+/="; 
 
var out = ""; //This is the output
var chr1, chr2, chr3 = ""; //These are the 3 decoded bytes
var enc1, enc2, enc3, enc4 = ""; //These are the 4 bytes to be decoded
var i = 0; //Position counter

// remove all characters that are not A-Z, a-z, 0-9, +, /, or =
var base64test = /[^A-Za-z0-9\+\/\=]/g;

if (base64test.exec(inp)) { //Do some error checking
alert("There were invalid base64 characters in the input text.\n" +
"Valid base64 characters are A-Z, a-z, 0-9, ?+?, ?/?, and ?=?\n" +
"Expect errors in decoding.");
}
inp = inp.replace(/[^A-Za-z0-9\+\/\=]/g, "");

do { //Here’s the decode loop.

//Grab 4 bytes of encoded content.
enc1 = keyStr.indexOf(inp.charAt(i++));
enc2 = keyStr.indexOf(inp.charAt(i++));
enc3 = keyStr.indexOf(inp.charAt(i++));
enc4 = keyStr.indexOf(inp.charAt(i++));

//Heres the decode part. There’s really only one way to do it.
chr1 = (enc1 << 2) | (enc2 >> 4);
chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
chr3 = ((enc3 & 3) << 6) | enc4;

//Start to output decoded content
out = out + String.fromCharCode(chr1);

if (enc3 != 64) {
out = out + String.fromCharCode(chr2);
}
if (enc4 != 64) {
out = out + String.fromCharCode(chr3);
}

//now clean out the variables used
chr1 = chr2 = chr3 = "";
enc1 = enc2 = enc3 = enc4 = "";

} while (i < inp.length); //finish off the loop

//Now return the decoded values.
//return out;
return _utf8_decode(out);
}

 // private method for UTF-8 decoding  
function _utf8_decode(utftext) {  
     var string = "";  
     var i = 0;  
     var c = c1 = c2 = 0;  

     while ( i < utftext.length ) {  

         c = utftext.charCodeAt(i);  

         if (c < 128) {  
             string += String.fromCharCode(c);  
             i++;  
         }  
         else if((c > 191) && (c < 224)) {  
             c2 = utftext.charCodeAt(i+1);  
             string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));  
             i += 2;  
         }  
         else {  
             c2 = utftext.charCodeAt(i+1);  
             c3 = utftext.charCodeAt(i+2);  
             string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));  
             i += 3;  
         }  

     }  

     return string;  
}  