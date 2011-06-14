
//-------------------- rico.js
var Rico = {
  Version: 'current_build-54'
}

Rico.Effect = {};

Rico.URL = Class.create();
Rico.URL.prototype = {
	initialize : function(url){ 
	  pair = url.split('?')
	  this.basePath =  pair[0];
	  this.params = this.extractParams(pair[1]);
	},
	extractParams: function (paramString) {
	  if (!paramString) return {};
	  return paramString.split('&').map(function(p){return p.split('amp;').last()});
	},
	getParamValue: function (name) {
	  var matchName = name
	  var param = $A(this.params).find(function(p){return matchName==p.split('=')[0]});
	  return param ? param.split('=')[1] : null;
	},
	addParam: function(name, value){
	  this.params.push(name +"="+ value)
	},
	setParam: function(name, value){
	  var matchName = name
	  this.params = $A(this.params).reject(function(p){return matchName==p.split('=')[0]});		
	  this.addParam(name,value);
	},
	toS: function(){
	  var paramString = this.params.join('&');
	  return this.basePath + ((paramString != "") ? ("?" + paramString) : "");
	}	
}



//Rico.layout = {
//  makeYClipping: function(element) {
//	element = $(element);
//	if (element._overflowY) return;
//	element._overflow = element.style.overflow;
//	if ((Element.getStyle(element, 'yoverflow') || 'visible') != 'hidden')
//	 ;
//	  element.style.overflow-y = 'hidden';
//  },
//  undoYClipping: function(element) {
//	element = $(element);
//	if (element._overflowY) return;
//	element.style.overflow = element._overflowY;
//	element._overflowY = undefined;
//  }
//}


var RicoUtil = {

   getElementsComputedStyle: function ( htmlElement, cssProperty, mozillaEquivalentCSS) {
	  if ( arguments.length == 2 )
		 mozillaEquivalentCSS = cssProperty;

	  var el = $(htmlElement);
	  if ( el.currentStyle )
		 return el.currentStyle[cssProperty];
	  else
		 return document.defaultView.getComputedStyle(el, null).getPropertyValue(mozillaEquivalentCSS);
   },
   createXmlDocument: function() {
	  if (document.implementation && document.implementation.createDocument) {
		 var doc = document.implementation.createDocument("", "", null);

		 if (doc.readyState == null) {
			doc.readyState = 1;
			doc.addEventListener("load", function () {
			   doc.readyState = 4;
			   if (typeof doc.onreadystatechange == "function")
				  doc.onreadystatechange();
			}, false);
		 }
		 return doc;
	  }
	  if (window.ActiveXObject)
		  return Try.these(
			function() { return new ActiveXObject('MSXML2.DomDocument')   },
			function() { return new ActiveXObject('Microsoft.DomDocument')},
			function() { return new ActiveXObject('MSXML.DomDocument')	},
			function() { return new ActiveXObject('MSXML3.DomDocument')   }
		  ) || false;

	  return null;
   },

   getContentAsString: function( parentNode ) {
	  return parentNode.xml != undefined ? 
		 this._getContentAsStringIE(parentNode) :
		 this._getContentAsStringMozilla(parentNode);
   },

  _getContentAsStringIE: function(parentNode) {
	 var contentStr = "";
	 for ( var i = 0 ; i < parentNode.childNodes.length ; i++ ) {
		 var n = parentNode.childNodes[i];
		 if (n.nodeType == 4) {
			 contentStr += n.nodeValue;
		 }
		 else {
		   contentStr += n.xml;
	   }
	 }
	 return contentStr;
  },

  _getContentAsStringMozilla: function(parentNode) {
	 var xmlSerializer = new XMLSerializer();
	 var contentStr = "";
	 for ( var i = 0 ; i < parentNode.childNodes.length ; i++ ) {
		  var n = parentNode.childNodes[i];
		  if (n.nodeType == 4) { // CDATA node
			  contentStr += n.nodeValue;
		  }
		  else {
			contentStr += xmlSerializer.serializeToString(n);
		}
	 }
	 return contentStr;
  },

   toViewportPosition: function(element) {
	  return this._toAbsolute(element,true);
   },

   toDocumentPosition: function(element) {
	  return this._toAbsolute(element,false);
   }
}

//-------------------- ricoAjaxEngine.js
Rico.AjaxEngine = Class.create();

Rico.AjaxEngine.prototype = {

   initialize: function() {
	  this.ajaxElements = new Array();
	  this.ajaxObjects  = new Array();
	  this.requestURLS  = new Array();
	  this.options = {};
   },

   registerAjaxElement: function( anId, anElement ) {
	  if ( !anElement )
		 anElement = $(anId);
	  this.ajaxElements[anId] = anElement;
   },

   registerAjaxObject: function( anId, anObject ) {
	  this.ajaxObjects[anId] = anObject;
   },

   registerRequest: function (requestLogicalName, requestURL) {
	  this.requestURLS[requestLogicalName] = requestURL;
   },

   sendRequest: function(requestName, options) {
	  // Allow for backwards Compatibility
	  if ( arguments.length >= 2 )
	   if (typeof arguments[1] == 'string')
		 options = {parameters: this._createQueryString(arguments, 1)};
	  this.sendRequestWithData(requestName, null, options);
   },

   sendRequestWithData: function(requestName, xmlDocument, options) {
	  var requestURL = this.requestURLS[requestName];
	  if ( requestURL == null )
		 return;

	  // Allow for backwards Compatibility
	  if ( arguments.length >= 3 )
		if (typeof arguments[2] == 'string')
		  options.parameters = this._createQueryString(arguments, 2);

	  new Ajax.Request(requestURL, this._requestOptions(options,xmlDocument));
   },

   sendRequestAndUpdate: function(requestName,container,options) {
	  // Allow for backwards Compatibility
	  if ( arguments.length >= 3 )
		if (typeof arguments[2] == 'string')
		  options.parameters = this._createQueryString(arguments, 2);

	  this.sendRequestWithDataAndUpdate(requestName, null, container, options);
   },

   sendRequestWithDataAndUpdate: function(requestName,xmlDocument,container,options) {
	  var requestURL = this.requestURLS[requestName];
	  if ( requestURL == null )
		 return;

	  // Allow for backwards Compatibility
	  if ( arguments.length >= 4 )
		if (typeof arguments[3] == 'string')
		  options.parameters = this._createQueryString(arguments, 3);

	  var updaterOptions = this._requestOptions(options,xmlDocument);

	  new Ajax.Updater(container, requestURL, updaterOptions);
   },

   // Private -- not part of intended engine API --------------------------------------------------------------------

   _requestOptions: function(options,xmlDoc) {
	  var requestHeaders = ['X-Rico-Version', Rico.Version ];
	  var sendMethod = 'post';
	  if ( xmlDoc == null )
		if (Rico.prototypeVersion < 1.4)
		requestHeaders.push( 'Content-type', 'text/xml' );
	  else
		  sendMethod = 'get';
	  (!options) ? options = {} : '';

	  if (!options._RicoOptionsProcessed){
	  // Check and keep any user onComplete functions
		if (options.onComplete)
			 options.onRicoComplete = options.onComplete;
		// Fix onComplete
		if (options.overrideOnComplete)
		  options.onComplete = options.overrideOnComplete;
		else
		  options.onComplete = this._onRequestComplete.bind(this);
		options._RicoOptionsProcessed = true;
	  }

	 // Set the default options and extend with any user options
	 this.options = {
					 requestHeaders: requestHeaders,
					 parameters:	 options.parameters,
					 postBody:	   xmlDoc,
					 method:		 sendMethod,
					 onComplete:	 options.onComplete
					};
	 // Set any user options:
	 Object.extend(this.options, options);
	 return this.options;
   },

   _createQueryString: function( theArgs, offset ) {
	  var queryString = ""
	  for ( var i = offset ; i < theArgs.length ; i++ ) {
		  if ( i != offset )
			queryString += "&";

		  var anArg = theArgs[i];

		  if ( anArg.name != undefined && anArg.value != undefined ) {
			queryString += anArg.name +  "=" + escape(anArg.value);
		  }
		  else {
			 var ePos  = anArg.indexOf('=');
			 var argName  = anArg.substring( 0, ePos );
			 var argValue = anArg.substring( ePos + 1 );
			 queryString += argName + "=" + escape(argValue);
		  }
	  }
	  return queryString;
   },

   _onRequestComplete : function(request) {

	  if(!request)
		  return;

	  // User can set an onFailure option - which will be called by prototype
	  if (request.status != 200)
		return;

//	  var response = request.responseXML.getElementsByTagName("ajax-response");
	  var response = eval('(' + request.responseText + ')');
	  if (response == null)
	  {
		 return;		
	  }
	  this._processAjaxResponse( response);
	  
	  // Check if user has set a onComplete function
	  var onRicoComplete = this.options.onRicoComplete;
	  if (onRicoComplete != null)
		  onRicoComplete();
   },

   _processAjaxResponse: function( xmlResponseElements ) {

	  for ( var i = 0 ; i < xmlResponseElements.length ; i++ ) {
		 var responseElement = xmlResponseElements[i];

		 // only process nodes of type element.....
		 if ( responseElement.nodeType != 1 )
			continue;

		 var responseType = responseElement.getAttribute("type");
		 var responseId   = responseElement.getAttribute("id");

		 if ( responseType == "object" )
			this._processAjaxObjectUpdate( this.ajaxObjects[ responseId ], responseElement );
		 else if ( responseType == "element" )
			this._processAjaxElementUpdate( this.ajaxElements[ responseId ], responseElement );
		 else
			alert('unrecognized AjaxResponse type : ' + responseType );
	  }
   },

   _processAjaxObjectUpdate: function( ajaxObject, responseElement ) {
	  ajaxObject.ajaxUpdate( responseElement );
   },

   _processAjaxElementUpdate: function( ajaxElement, responseElement ) {
	  ajaxElement.innerHTML = RicoUtil.getContentAsString(responseElement);
   }

}

var ajaxEngine = new Rico.AjaxEngine();


