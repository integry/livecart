/**
 *	@author Integry Systems
 */
angular
    .module('loadingOnAJAX', [])
    .config(function($httpProvider) {
        var numLoadings = 0;
        var loadingScreen = jQuery('<div style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:10000;background-color:gray;background-color:rgba(70,70,70,0.2);"><img style="position:absolute;top:50%;left:50%;" alt="" src="data:image/gif;base64,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" /></div>')
            .appendTo(jQuery('body')).hide();
        $httpProvider.responseInterceptors.push(function() {
            return function(promise) {
                numLoadings++;
                loadingScreen.show();
                var hide = function(r) { if (!(--numLoadings)) loadingScreen.hide(); return r; };
                return promise.then(hide, hide);
            };
        });
    });

angular
    .module('globalErrors', [])
    .config(function($provide, $httpProvider, $compileProvider) {
        var elementsList = jQuery();

        var showMessage = function(content, cl, time) {
            jQuery('<div/>')
                .addClass('message')
                .addClass(cl)
                .hide()
                .fadeIn('fast')
                .delay(time)
                .fadeOut('fast', function() { jQuery(this).remove(); })
                .appendTo(elementsList)
                .html('<div>' + content + '</div>');
        };

        $httpProvider.responseInterceptors.push(function($timeout, $q) {
            return function(promise) {
                return promise.then(function(successResponse) {
                    if (successResponse.config.method.toUpperCase() != 'GET')
                    {
                        if ((successResponse.data.status == 'success') && successResponse.data.message)
                        {
                        	showMessage(successResponse.data.message, 'yellowMessage', 5000);
						}
	                }
                    return successResponse;

                }, function(errorResponse) {
                    switch (errorResponse.status) {
                        case 401:
                            showMessage('Wrong usename or password', 'errorMessage', 20000);
                            break;
                        case 403:
                            showMessage('You don\'t have the right to do this', 'errorMessage', 20000);
                            break;
                        case 500:
                            showMessage('Server internal error: ' + errorResponse.data, 'errorMessage', 20000);
                            break;
                        default:
                            showMessage('Error ' + errorResponse.status + ': ' + errorResponse.data, 'errorMessage', 20000);
                    }
                    return $q.reject(errorResponse);
                });
            };
        });

        $compileProvider.directive('appMessages', function() {
            var directiveDefinitionObject = {
                link: function(scope, element, attrs) { elementsList.push(jQuery(element)); }
            };
            return directiveDefinitionObject;
        });
    });

var app = angular.module('LiveCart', ['ui.bootstrap', 'ui.tinymce', 'loadingOnAJAX', 'globalErrors', 'tree', 'backendComponents', 'ngGrid', 'ngResource']);

app.config(function($locationProvider)
{
	$locationProvider.html5Mode(false);
});

app.config(function($dialogProvider)
{
    $dialogProvider.options({backdropClick: true, dialogFade: true, backdropFade: true, dialogOpenClass: 'modal-open'});
});

app.run(function($rootScope)
{
	$rootScope.route = function(controller, action, params)
	{
		return Router.createUrl(controller, action, params);
	}
});

app.controller('BackendController', function($scope)
{
    $scope.tinymceOptions = window.tinyMCEOptions;
    $scope.getTinyMceOpts = function()
    {
    	return $scope.tinymceOptions;
	}
});

var Backend =
{
	idCounter: 0,

	setTranslations: function(translations)
	{
		Backend.translations = translations;
	},

	getTranslation: function(key)
	{
		return this.translations[key];
	},

	sendKeepAlivePing: function()
	{
		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.index', 'keepAlive'));
	},

	setUniqueID: function(element)
	{
		element.id = 'generatedId_' + ++this.idCounter;
		return element.id;
	},

	getHash: function()
	{
		return Backend.AjaxNavigationHandler.prototype.getHash();
	},

	onLoad: function()
	{
		Backend.initWidgets();

		// AJAX navigation
		dhtmlHistory.initialize();
		dhtmlHistory.addListener(Backend.ajaxNav.handle);
		dhtmlHistory.handleBookmark();

		setInterval("Backend.sendKeepAlivePing();", 300 * 1000);
		if (!$('confirmations'))
		{
			var el = document.createElement('div');
			el.id = 'confirmations';
			document.body.appendChild(el);
		}
	}
};

// set default locale
Backend.locale = 'en';

Backend.openedContainersStack = [];
Backend.showContainer = function(containerID)
{
	if(Backend.openedContainersStack.length == 0)
	{
		Backend.openedContainersStack[0] = containerID;
	}
	else if(Backend.openedContainersStack[Backend.openedContainersStack.length - 1] != containerID)
	{
		Backend.openedContainersStack[Backend.openedContainersStack.length] = containerID;
		$(Backend.openedContainersStack[Backend.openedContainersStack.length - 2]).hide();
	}

	$(Backend.openedContainersStack[Backend.openedContainersStack.length - 1]).show();
}

Backend.hideContainer = function()
{
	if(Backend.openedContainersStack.length  > 0) $(Backend.openedContainersStack[Backend.openedContainersStack.length - 1]).hide();
	Backend.openedContainersStack.splice(Backend.openedContainersStack.length - 1, 1);

	var lastContainer = $(Backend.openedContainersStack[Backend.openedContainersStack.length - 1]);
	if(lastContainer)
	{
		lastContainer.show();
	}
}

/*************************************************
	Help context handler
**************************************************/
Backend.setHelpContext = function(context)
{
	var help = $('help');
	if (help)
	{
		help.href = 'http://doc.livecart.com/help/' + context;
	}
}

/*************************************************
	AJAX back/forward navigation
**************************************************/
Backend.AjaxNavigationHandler = Class.create();
Backend.AjaxNavigationHandler.prototype =
{
	ignoreNextAdd: false,

	initialize: function()
	{
	},

	/**
	 * The AJAX history consists of clicks on certain elements (traditional history uses URL's)
	 * To register a history event, you only have to pass in an element ID, which was clicked. When
	 * the user navigates backward or forward using the browser navigation, these clicks are simply
	 * repeated by calling the onclick() function for the particular element.
	 *
	 * Sometimes it is necessary to perform more than one "click" to return to previous state. In such case
	 * you can pass in several element ID's delimited with # sign. For example: cat_44#tabImages - would first
	 * emulate a click on cat_44 element and then on tabImages element. This is also useful for bookmarking,
	 * which allows to easily reference certain content on complex pages.
	 *
	 * @param element string Element ID, which would be clicked
	 * @param params Probably obsolete, but perhaps we'll find some use for it
	 */
	add: function(element, params)
	{
		if (true == this.ignoreNextAdd)
		{
			this.ignoreNextAdd = false;
			return false;
		}

		dhtmlHistory.add(element + '__');
		return true;
	},

	getHash: function()
	{
		var hash = document.location.hash;

		// Safari
		hash = hash.replace(/%23/, '#');

		return ("#" == hash[0]) ? ('__' == hash.substring(-2) ? hash.substring(1, hash.length - 2) : hash) : hash.substring(0, hash.length - 1);
	},

	handle: function(element, params)
	{
		if(!params) params = {};
		if(!params.recoverFromIndex) params.recoverFromIndex = 0;

		var elementId = element.substr(0, element.length - 2);

		// Safari
		elementId = elementId.replace(/%23/, '#');

		var hashElements = elementId.split('#');

		for (var hashPart = params.recoverFromIndex; hashPart < hashElements.length; hashPart++)
		{
			if ($(hashElements[hashPart]))
			{
				// only register the click for the last element
				if (hashPart < hashElements.length - 1)
				{
					Backend.ajaxNav.ignoreNext();
				}

				if ($(hashElements[hashPart]).onclick)
				{
					$(hashElements[hashPart]).onclick();
				}
			}

			/*
			// This is in case element is not yet loaded. If so we wait for all requests to finish and the continue.
			else if(Ajax.activeRequestCount > 0)
			{
				setInterval(function()
				{
					if(this.handle)
					{
						this.handle(element, { recoverFromIndex: hashPart });
					}
				}.bind(this), 10);

				return;
			}
			*/

		}
	},


	ignoreNext: function()
	{
		this.ignoreNextAdd = true;
	}
}

Backend.ajaxNav = new Backend.AjaxNavigationHandler();

/*************************************************
	Layout Control
**************************************************/
Backend.LayoutManager = Class.create();

/**
 * Manage 100% heights
 *
 * IE does this pretty good natively (only the main content div height is changed on window resize),
 * however FF won't handle cascading 100% heights unless the page is being rendered in quirks mode.
 *
 * You can specify a block to take 100% height by assigning a "maxHeight" CSS class to it
 * This class also simulates an "extension" of CSS, that allows to add or substract some height
 * in pixels from percentage defined height (for example 100% minus 40px). This will often be needed
 * to compensate for parent elements padding. For example, if the parent element has a top and bottom
 * padding of 10px, you'll have to substract 20px from child block size. This will also be needed when
 * there are other siblings that consume some known height (like TabControl, which contains a
 * tab bar with known height and content div, which must take 100% of the rest of the available height).
 *
 * Example:
 *
 * <code>
 *	  <div class="maxHeight h--50">
 *		  This div will take 100% of available space minus 50 pixels
 *	  </div>
 * </code>
 *
 * @todo automatically substract parent padding
 */
Backend.LayoutManager.prototype =
{
	initialize: function()
	{
		window.onresize = this.onresize.bindAsEventListener(this);
		this.onresize();
	},

	redraw: function()
	{
		document.body.hide();
		document.body.show();
	},

	/**
	 * Set the minimum possible height to all involved elements, so that
	 * their height could be enlarged to necessary size
	 */
	collapseAll: function(cont)
	{
		var el = document.getElementsByClassName("maxHeight", document);

		for (k = 0; k < el.length; k++)
		{
			el[k].style.minHeight = '0px';

			if (document.all)
			{
				el[k].style.height = '0px';
			}
			else
			{
				el[k].style.minHeight = '0px';
			}

		}
	},

	/**
	 * @todo Figure out why IE needs additional 2px offset
	 * @todo Figure out a better way to determine the body height for all browsers
	 */
	onresize: function()
	{
		if(BrowserDetect.browser == 'Explorer' && BrowserDetect.version == 7) return;

		if (document.all)
		{
			$('pageContentContainer').style.height = '0px';
		}

		// calculate content area height
		var ph = new PopupMenuHandler();
		var w = ph.getWindowHeight();
		var h = w - 185 - (document.all ? 1 : 0);
		var cont = $('pageContentContainer');

		if (BrowserDetect.browser == 'Explorer')
		{
			cont.style.height = h + 'px';

			// force re-render for IE
			$('pageContainer').style.display = 'none';
			$('pageContainer').style.display = 'block';
			$('nav').style.display = 'none';
			$('nav').style.display = 'block';
		}
		else // Good browsers
		{
			cont.style.minHeight = h + 'px';

			this.collapseAll(cont);
			this.setMaxHeight(cont);
		}
	},

	setMaxHeight: function(parent)
	{
		var el = document.getElementsByClassName('maxHeight', parent);
		for (k = 0; k < el.length; k++)
		{
			var parentHeight = el[k].parentNode.offsetHeight;

			offset = 0;
			if (el[k].className.indexOf(' h-') > 0)
			{
				offset = el[k].className.substr(el[k].className.indexOf(' h-') + 3, 10);
				if (offset.indexOf(' ') > 0)
				{
					offset = offset.substr(0, offset.indexOf(' '));
				}
			}
			offset = parseInt(offset);
			newHeight = parentHeight + offset;
			el[k].style.minHeight = newHeight + 'px';
		}
	}
}

/*************************************************
	Breadcrumb navigation
**************************************************/
Backend.Breadcrumb =
{
	loadedIDs: $A([]),
	selectedItemId: 0,
	pageTitle: $("pageTitle"),
	template: $("breadcrumb_template"),

	display: function(id, additional)
	{
		if (!Backend.Breadcrumb.pageTitle)
		{
			Backend.Breadcrumb.pageTitle = $("pageTitle");
		}

		Backend.Breadcrumb.template = $("breadcrumb_template");
		Backend.Breadcrumb.createPath(id, additional);
	},

	getTreeInst: function()
	{
		return this.treeBrowser.jstree('get_parent', '');
	},

	createPath: function(id, additional)
	{
		var parentId = id;

		if (!Backend.Breadcrumb.treeBrowser || !this.treeBrowser.jstree)
		{
			return false;
		}

		var selected = this.treeBrowser.jstree('get_selected');
		Backend.Breadcrumb.selectedItemId = selected.attr('id');

		if (!Backend.Breadcrumb.pageTitle)
		{
			return false;
		}

		Backend.Breadcrumb.pageTitle.innerHTML = "";

		if(typeof(id) != 'object')
		{
			var names = this.getTreeInst().get_path(selected);
			var ids = this.getTreeInst().get_path(selected, true);

			for (var k = names.length - 1; k >= 0; k--)
			{
				Backend.Breadcrumb.addCrumb(names[k], ids[k], true);
			}
		}
		else
		{
			$A(id).each(function(node) {
				Backend.Breadcrumb.addCrumb(node.name, node.ID);
			});
		}

		if(additional)
		{
			if(typeof(additional) != "object")
			{
				new Insertion.Bottom(Backend.Breadcrumb.pageTitle, "<span class=\"breadcrumb\">" + Backend.Breadcrumb.template.innerHTML + "</span>");
				$$("#pageTitle a").last().innerHTML = additional;
			}
			else
			{
				$A(additional).each(function(crumb)
				{
					if(typeof(crumb) == "string")
					{
						Backend.Breadcrumb.addCrumb(crumb, false)
					}
					else
					{
					   Backend.Breadcrumb.addCrumb(crumb[0], crumb[1])
					}
				});
			}
		}

		jQuery("#pageTitle .breadcrumb").last().find('.breadcrumb_separator').hide();

		var lastLink = jQuery("#pageTitle .breadcrumb").last().find('a');
		if(lastLink.length)
		{
			Backend.Breadcrumb.convertLinkToText(lastLink[0]);
		}
	},

	addCrumb: function(nodeStr, parentId, reverse) {
		var template = "<span class=\"breadcrumb\">" + Backend.Breadcrumb.template.innerHTML + "</span>";
		var link = null;

		if(reverse)
		{
			new Insertion.Top(Backend.Breadcrumb.pageTitle, template);
			link = Backend.Breadcrumb.pageTitle.childElements().first().down('a');
		}
		else
		{
			new Insertion.Bottom(Backend.Breadcrumb.pageTitle, template);
			link = Backend.Breadcrumb.pageTitle.childElements().last().down('a');
		}


		link.innerHTML = nodeStr;

		if(typeof(parentId) == "function")
		{
			Event.observe(link, "click", parentId);
		}
		else if(parentId !== false)
		{
			link.catId = parentId;
			link.href = "#cat_" + parentId;
			Event.observe(link, "click", function(e) {
				e.preventDefault();
				Backend.hideContainer();

				if(!Backend.Category.getNode(this.catId).length)
				{
					var catId = this.catId;
					Backend.Category.loadBranch(this.catId, function()
					{
						Backend.Category.selectItem(catId);
					});
				}
				else
				{
					Backend.Category.selectItem(this.catId);
				}
			});
		}
	},

	setTree: function(treeBrowser) {
		Backend.Breadcrumb.treeBrowser = treeBrowser;
	},

	convertLinkToText: function(link) {
		new Insertion.After(link, link.innerHTML);
		Element.remove(link);
	}
}

/*************************************************
	Backend menu
**************************************************/

var MenuController = function($scope) {

	$scope.items = window.menuArray;

	$scope.setDescription = function(description)
	{
		$scope.description = description;
	};

	$scope.menuRoute = function(item)
	{
		if (item.controller)
		{
			return $scope.route(item.controller, item.action, item.params);
		}
	}
};

app.factory('breadCrumbService', function()
{
	var items = [];
	var service =
	{
		getItems: function()
		{
			return items;
		},

		clear: function()
		{
			items = [];
		},

		push: function(title, url)
		{
			items.push({title: title, url: url});
		}
	};

	return service;
});

var BreadCrumbController = function($scope, breadCrumbService)
{
	$scope.items = breadCrumbService.getItems();
};

Backend.ThemePreview = Class.create();
Backend.ThemePreview.prototype =
{
	initialize: function(container, select)
	{
		var img = document.createElement('img');
		img.addClassName('themePreview');
		container.appendChild(img);
		select.image = img;

		var change =
			function()
			{
				var img = this.image;

				if (!this.value || 0 == this.value)
				{
					img.hide();
					return;
				}
				else
				{
					img.show();
				}

				img.href = 'theme' + (this.value != 'barebone' ? '/' + this.value : '') + '/preview.png';
				img.title = this.value;
				img.onclick =
					function()
					{
						img.rel = 'lightbox';
						Lightbox.prototype.getInstance().start(img.href);
					}

				img.onload =
					function()
					{
						this.show();
					}

				img.onerror =
					function()
					{
						this.hide();
					}

				img.src = 'theme' + (this.value != 'barebone' ? '/' + this.value : '') + '/preview_small.png';
			}

		change.bind(select)();

		Event.observe(select, 'change', change);
	}
}

/*************************************************
	Language switch menu
*************************************************/
function showLangMenu(display) {
	menu = $('langMenuContainer');
	if (display)
	{
		menu.style.display = 'block';
		new Ajax.Updater('langMenuContainer', langMenuUrl);

		setTimeout("Event.observe(document, 'click', hideLangMenu, true);", 500);
	}
	else
	{
		menu.style.display = 'none';
		Event.stopObserving(document, 'click', hideLangMenu, true);
	}
}

function hideLangMenu()
{
	showLangMenu(false);
}

/*************************************************
	Popup Menu Handler
*************************************************/
/**
 * Popup menu (absolutely positioned DIV's) position handling
 * This class calculates the optimal menu position, so that the
 * menu would always be within visible window boundaries
 **/
PopupMenuHandler = Class.create();
PopupMenuHandler.prototype =
{
	x: 0,
	y: 0,

	initialize: function(xPos, yPos, width, height)
	{
		scrollX = this.getScrollX();
		scrollY = this.getScrollY();

		if ((xPos + width) > (scrollX + this.getWindowWidth()))
		{
			xPos = scrollX + this.getWindowWidth() - width - 40;
		}

		if (xPos < scrollX)
		{
			xPos = scrollX + 1;
		}

		if ((yPos + height) > (scrollY + this.getWindowHeight()))
		{
			yPos = scrollY + this.getWindowHeight() - height - 40;
		}

		if (yPos < scrollY)
		{
			yPos = scrollY + 1;
		}

		this.x = xPos;
		this.y = yPos;
	},

	getByElement: function(element, x, y)
	{
		var inst = new PopupMenuHandler(x, y, element.offsetWidth, element.offsetHeight);
		element.style.left = inst.x + 'px';
		element.style.top = inst.y + 'px';
	},

	getScrollX: function()
	{
		var scrOfX = 0;
		if( typeof( window.pageYOffset ) == 'number' ) {
			//Netscape compliant
			scrOfX = window.pageXOffset;
		}
		else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) )
		{
			//DOM compliant
			scrOfX = document.body.scrollLeft;
		} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) )
		{
			//IE6 standards compliant mode
			scrOfX = document.documentElement.scrollLeft;
		}
		return scrOfX;
	},

	getScrollY: function()
	{
		var scrOfY = 0;
		if( typeof( window.pageYOffset ) == 'number' ) {
			//Netscape compliant
			scrOfY = window.pageYOffset;
		}
		else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) )
		{
			//DOM compliant
			scrOfY = document.body.scrollTop;
		} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) )
		{
			//IE6 standards compliant mode
			scrOfY = document.documentElement.scrollTop;
		}
		return scrOfY;
	},

	getWindowWidth: function()
	{
		var myWidth = 0;
		if( typeof( window.innerWidth ) == 'number' )
		{
			//Non-IE
			myWidth = window.innerWidth;
		}
		else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) )
		{
			//IE 6+ in 'standards compliant mode'
			myWidth = document.documentElement.clientWidth;
		}
		else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) )
		{
			//IE 4 compatible
			myWidth = document.body.clientWidth;
		}
		return myWidth;
	},

	getWindowHeight: function()
	{
		var myHeight = 0;
		if( typeof( window.innerWidth ) == 'number' )
		{
			//Non-IE
			myHeight = window.innerHeight;
		}
		else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) )
		{
			//IE 6+ in 'standards compliant mode'
			myHeight = document.documentElement.clientHeight;
		}
		else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) )
		{
			//IE 4 compatible
			myHeight = document.body.clientHeight;
		}
		return myHeight;
	}
}


/*************************************************
	Browser detector
*************************************************/

/**
 * Browser detector
 * @link http://www.quirksmode.org/js/detect.html
 */
var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++) {
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{   string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{	   // for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{	   // for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};

BrowserDetect.init();

/*************************************************
	Save confirmation message animation
*************************************************/
Backend.SaveConfirmationMessage = Class.create();
Backend.SaveConfirmationMessage.prototype =
{
	counter: 0,
	timers: {},
	options: {},

	initialize: function(element, options)
	{
		this.element = $(element);

		if(!this.element.id)
		{
			this.element.id = this.getGeneratedId();
		}

		if(!Backend.SaveConfirmationMessage.prototype.timers[this.element.id])
		{
			Backend.SaveConfirmationMessage.prototype.timers[this.element.id] = {};
		}

		if(!this.element.down('div')) this.element.appendChild(document.createElement('div'));
		this.innerElement = this.element.down('div');

		if(options && options.type)
		{
			Element.addClassName(this.element, options.type + 'Message')
		}

		if(options && options.message)
		{
			if(this.innerElement.firstChild) this.innerElement.firstChild.value = options.message;
			else this.innerElement.appendChild(document.createTextNode(options.message));
		}

		var closeButton = this.element.down('.closeMessage');
		if(closeButton)
		{
			this.hideCloseButton(closeButton);

			Event.observe(closeButton, 'mouseover', function(e) { this.showCloseButton(closeButton) }.bind(this) )
			Event.observe(closeButton, 'mouseout', function(e) { this.hideCloseButton(closeButton) }.bind(this) )
			Event.observe(closeButton, 'click', function(e) { this.hide() }.bind(this) )
		}

		this.options = options;

		this.show();
	},

	showCloseButton: function(closeButton)
	{
		try {
			closeButton.setOpacity(1);
		} catch(e) {
			closeButton.style.visibility = 'visible';
		}
	},

	hideCloseButton: function(closeButton)
	{
		try {
			closeButton.setOpacity(0.5);
		} catch(e) {
			closeButton.style.visibility = 'hidden';
		}
	},

	show: function()
	{
		this.stopTimers();
		this.element.hide();

		this.displaying = true;

		//Backend.SaveConfirmationMessage.prototype.timers[this.element.id].scrollEffect = new Effect.ScrollTo(this.element, {offset: -24});
		Backend.SaveConfirmationMessage.prototype.timers[this.element.id].appearEffect = jQuery(this.element).show({complete: this.highlight.bind(this)});
	},

	highlight: function()
	{
		//this.innerElement.focus();
		Backend.SaveConfirmationMessage.prototype.timers[this.element.id].effectHighlight = jQuery(this.innerElement).effect('highlight');

		// do not hide error or permanent confirmation messages
		if (!this.element.hasClassName('redMessage') && !this.element.hasClassName('bugMessage') && !this.element.hasClassName('stick'))
		{
			Backend.SaveConfirmationMessage.prototype.timers[this.element.id].hideTimeout = setTimeout(function() { this.hide() }.bind(this), 4000);
		}
	},

	hide: function()
	{
		Backend.SaveConfirmationMessage.prototype.timers[this.element.id].fadeEffect = jQuery(this.element).hide({});
		Backend.SaveConfirmationMessage.prototype.timers[this.element.id].fadeTimeout = setTimeout(function() { this.displaying = false; }.bind(this), 4000);

		if (this.options && this.options.del/* && this.options.delete !KONQUEROR */)
		{
			this.element.parentNode.removeChild(this.element);
		}
	},

	stopTimers: function()
	{
		if(Backend.SaveConfirmationMessage.prototype.timers[this.element.id].hideTimeout) clearTimeout(Backend.SaveConfirmationMessage.prototype.timers[this.element.id].hideTimeout);
		if(Backend.SaveConfirmationMessage.prototype.timers[this.element.id].fadeTimeout) clearTimeout(Backend.SaveConfirmationMessage.prototype.timers[this.element.id].fadeTimeout);
		if(Backend.SaveConfirmationMessage.prototype.timers[this.element.id].appearEffect) Backend.SaveConfirmationMessage.prototype.timers[this.element.id].appearEffect.cancel();
		if(Backend.SaveConfirmationMessage.prototype.timers[this.element.id].fadeEffect) Backend.SaveConfirmationMessage.prototype.timers[this.element.id].fadeEffect.cancel();
		if(Backend.SaveConfirmationMessage.prototype.timers[this.element.id].effectHighlight) Backend.SaveConfirmationMessage.prototype.timers[this.element.id].effectHighlight.cancel();
	},

	getGeneratedId: function()
	{
		return 'saveConfirmationMessage_' + (Backend.SaveConfirmationMessage.prototype.counter++);
	},

	showMessage: function(message, type)
	{
		if (!type)
		{
			type = 'yellow';
		}

		var el = document.createElement('div');
		el.className = type + 'Message';
		var close = document.createElement('img');
		close.className = 'closeMessage';
		close.src = 'image/silk/cancel.png';

		var confirmations = $('confirmations');
		if (!confirmations)
		{
			confirmations = document.createElement('div');
			confirmations.id = 'confirmations';
			document.body.appendChild(confirmations);
		}

		confirmations.appendChild(el);
		el.appendChild(close);
		new Backend.SaveConfirmationMessage(el, {del: true, message: message});
	}
}

/**
 * Converts between metric and English units
 */
Backend.UnitConventer = Class.create();
Backend.UnitConventer.prototype =
{
	Instances: {},

	initialize: function(root)
	{
		// Get all nodes
		this.nodes = {};
		this.nodes.root = $(root);
		this.nodes.normalizedWeightField = this.nodes.root.down(".UnitConventer_NormalizedWeight");
		this.nodes.unitsTypeField = this.nodes.root.down(".UnitConventer_UnitsType");
		this.nodes.hiValue = this.nodes.root.down('.UnitConventer_HiValue');
		this.nodes.loValue = this.nodes.root.down('.UnitConventer_LoValue');
		this.nodes.switchUnits = this.nodes.root.down('.UnitConventer_SwitchUnits');

		// Add units after fields
		if(!this.nodes.root.down('.UnitConventer_HiUnit'))
		{
		   new Insertion.After(this.nodes.hiValue, '<span class="UnitConventer_HiUnit"> </span>');
		}

		if(!this.nodes.root.down('.UnitConventer_LoUnit'))
		{
			new Insertion.After(this.nodes.loValue, '<span class="UnitConventer_LoUnit"> </span>');
		}

		this.reset();

		// Bind events
		Event.observe(this.nodes.hiValue, "keyup", function(e){ NumericFilter(this); });
		Event.observe(this.nodes.loValue, "keyup", function(e){ NumericFilter(this); });

		Event.observe(this.nodes.hiValue, 'keyup', function(e) { this.updateShippingWeight() }.bind(this));
		Event.observe(this.nodes.loValue, 'keyup', function(e) { this.updateShippingWeight() }.bind(this));
		Event.observe(this.nodes.switchUnits, 'click', function(e) { e.preventDefault(); this.switchUnitTypes() }.bind(this));

		this.switchUnitTypes();
		this.switchUnitTypes();
	},

	reset: function()
	{
		this.nodes.switchUnits.update(this.nodes.root.down('.UnitConventer_SwitcgTo' + (this.nodes.unitsTypeField.value == 'ENGLISH' ? 'METRIC' : 'ENGLISH').capitalize() + 'Title').innerHTML);
		this.nodes.root.down('.UnitConventer_HiUnit').innerHTML = this.nodes.root.down('.UnitConventer_'  + this.nodes.unitsTypeField.value.capitalize() + 'HiUnit').innerHTML;
		this.nodes.root.down('.UnitConventer_LoUnit').innerHTML = this.nodes.root.down('.UnitConventer_'  + this.nodes.unitsTypeField.value.capitalize() + 'LoUnit').innerHTML;

		this.nodes.hiValue.value = 0;
		this.nodes.loValue.value = 0;
	},

	getInstance: function(root)
	{
		if (!$(root))
		{
			return false;
		}

		if(!Backend.UnitConventer.prototype.Instances[$(root).id])
		{
			Backend.UnitConventer.prototype.Instances[$(root).id] = new Backend.UnitConventer(root);
		}

		return Backend.UnitConventer.prototype.Instances[$(root).id];
	},

	switchUnitTypes: function()
	{
		this.nodes.switchUnits.update(this.nodes.root.down('.UnitConventer_SwitcgTo' + this.nodes.unitsTypeField.value.capitalize() + 'Title').innerHTML);

		this.nodes.unitsTypeField.value = (this.nodes.unitsTypeField.value == 'ENGLISH') ? 'METRIC' : 'ENGLISH';

		// Change captions
		this.nodes.root.down('.UnitConventer_HiUnit').innerHTML = this.nodes.root.down('.UnitConventer_'  + this.nodes.unitsTypeField.value.capitalize() + 'HiUnit').innerHTML;
		this.nodes.root.down('.UnitConventer_LoUnit').innerHTML = this.nodes.root.down('.UnitConventer_'  + this.nodes.unitsTypeField.value.capitalize() + 'LoUnit').innerHTML;

		var multipliers = this.getWeightMultipliers();

		var hiValue = Math.floor(this.nodes.normalizedWeightField.value / multipliers[0]);
		var loValue = (this.nodes.normalizedWeightField.value - (hiValue * multipliers[0])) / multipliers[1];

		// allow to enter one decimal number for ounces
		var precision = 'ENGLISH' == this.nodes.unitsTypeField.value ? 10 : 1;

		loValue = Math.round(loValue * precision) / precision;

		if ('english' == this.nodes.unitsTypeField.value)
		{
			loValue = loValue.toFixed(0);
		}

		this.nodes.hiValue.value = hiValue;
		this.nodes.loValue.value = loValue;
	},

	getWeightMultipliers: function()
	{
		switch(this.nodes.unitsTypeField.value)
		{
			case 'ENGLISH':
				return [0.45359237, 0.0283495231];

			case 'METRIC':
			default:
				return [1, 0.001]
		}
	},

	updateShippingWeight: function(field)
	{
		var multipliers = this.getWeightMultipliers();
		this.nodes.normalizedWeightField.value = (this.nodes.hiValue.value * multipliers[0]) + (this.nodes.loValue.value * multipliers[1]);
	}
}

/*************************************************
	...
*************************************************/

function slideForm(id)
{
	var container = $(id);

	var title = jQuery(container).find('legend').html();

	jQuery(container).data('originalParent', container.parentNode).dialog(
		{
			autoOpen: false,
			modal: true,
			title: title,
			resizable: false,
			width: 'auto',
			autoResize: true,
			beforeClose: function(event, ui){
				//jQuery(container).html('').data('originalParent').appendChild(container);
		   }.bind(this),
		}).dialog('open');
}

function hideForm(id)
{
	var container = $(id);

	if (jQuery(container).data('dialog'))
	{
		jQuery(container).dialog('close');
	}
}

function restoreMenu(blockId, menuId)
{
	Element.hide($(blockId));
	Element.show($(menuId));
}

/***************************************************
 * MVC
 **************************************************/

MVC = {}
MVC.Model = function() {}
MVC.Model.prototype =
{
	controller: null,

	setController: function(controller)
	{
		this.controller = controller;
		this.notifyAllData(this.controller);
	},

	store: function(name, value)
	{
		if(arguments.length == 1)
		{
			this._data = name;
			if (this.controller)
			{
				this.notifyAllData(this.controller);
			}
		}
		else
		{
			this._data[name] = value;
			if (this.controller)
			{
				this.controller.notifyDataChange(name, value);
			}
		}
	},

	/**
	 *	Notify observers (controllers) that all data has been changed (usually on initial state)
	 */
	notifyAllData: function(recipient, data, prefix)
	{
		if (!data)
		{
			data = this._data;
		}

		if (!prefix)
		{
			prefix = '';
		}

		$H(data).each(function(val)
		{
			if (val[1] instanceof Object)
			{
				this.notifyAllData(recipient, val[1], prefix + val[0] + '.');
			}
			recipient.notifyDataChange(prefix + val[0], val[1]);
		}.bind(this));
	},

	save: function(form, onSaveResponse)
	{
		if(true == this.saving) return;
		this.saving = true;
		this.serverError = false;

		var self = this;

		new LiveCart.AjaxRequest(
			form,
			false,
			function(response)
			{
				var responseHash = {};
				try
				{
					responseHash = eval("(" + response.responseText + ")");
				}
				catch(e)
				{
					responseHash['status'] = 'serverError';
					responseHash['responseText'] = response.responseText;
				}

				this.afterSave(responseHash, onSaveResponse);
			}.bind(this)
		);
	},

	afterSave: function(response, onSaveResponse)
	{
		switch(response.status)
		{
			case 'success':
				this.store('ID', response.ID);
				if (response.data)
				{
					this.store(response.data);
				}
				break;
			case 'failure':
				this.errors = response.errors;
				break;
			case 'serverError':
				this.serverError = response.responseText;
				break;
		}

		onSaveResponse.call(this, response.status);
		this.saving = false;
	}
}

MVC.View = function() {}
MVC.View.prototype =
{
	form: null,

	boundVariables: {},

	assign: function(name, value)
	{
		if(arguments.length == 1)
		{
			this._data = name;
		}
		else
		{
			this._data[name] = value;
		}
	},

	bindForm: function(form)
	{
		this.form = form;
	},

	bindVariable: function(element, variableName)
	{
		if (!element)
		{
			return false;
		}

		this.boundVariables[variableName] = element;
	},

	notifyDataChange: function(name, value)
	{
		if (this.form)
		{
			var element = this.form.elements.namedItem(name);

			if (element)
			{
				element.value = value;
			}
		}

		if (this.boundVariables[name])
		{
			var element = this.boundVariables[name];

			// set value to form elements
			if (element instanceof HTMLInputElement ||
				element instanceof HTMLTextAreaElement ||
				element instanceof HTMLSelectElement ||
				element instanceof HTMLButtonElement)
			{
				element.value = value;
			}

			// innerHTML to others
			else
			{
				element.innerHTML = value;
			}
		}
	}
}

Backend.RegisterMVC = function(MVC)
{
	MVC.Messages = {};
	MVC.Links = {};

	MVC.Model.prototype.defaultLanguage = false;

	MVC.Controller.prototype.notifyDataChange = function(name, value)
	{
		if(this.view)
		{
			this.view.notifyDataChange(name, value);
		}
	}

	MVC.Model.prototype.clear = MVC.View.prototype.clear = function()
	{
		this._data = {};
	}

	MVC.Model.prototype.get = MVC.View.prototype.get = function(name, defaultValue)
	{
		var keys = name.split('.');
		var destination = this._data;
		var found = true;

		try
		{
			$A(keys).each(function(key)
			{
				if(destination[key] === undefined) throw new Error('not found');
				destination = destination[key];
			});
		}
		catch(e)
		{
			found = false;
		}

		return found ? destination : defaultValue;
	}
}

/********************************************************************
 * Select popup
 ********************************************************************/
Backend.SelectPopup = Class.create();
Backend.SelectPopup.prototype = {
	height: 520,
	width:  1000,
	location:  0,
	toolbar:  0,
	onObjectSelect: function() {},

	initialize: function(link, title, options)
	{
		this.link = link;
		this.title = title;

		if(options.onObjectSelect) this.onObjectSelect = options.onObjectSelect;
		this.height = options.height || this.height;
		this.width = options.width || this.width;
		this.location = options.location || this.location;
		this.toolbar = options.toolbar || this.toolbar;

		this.createPopup();
	},

	createPopup: function()
	{
		var createWindow = true;

		try
		{
			if(window.selectPopupWindow && this.link == window.selectPopupWindow.location.pathname)
			{
			   window.selectPopupWindow.focus();
			   createWindow = false;
			}
		}
		catch(e) { }

		if(createWindow)
		{
			Backend.SelectPopup.prototype.popup = window.open(this.link, this.title, 'resizable=1,toolbar=' + this.toolbar + ',location=' + this.location + ',width=' + this.width + ',height=' + this.height);

			Event.observe(window, 'unload', function()
			{
				if(window.selectPopupWindow)
				{
					window.selectPopupWindow.close();
				}
			}, false);

			Backend.SelectPopup.prototype.popup.focus();

			var interval = setInterval(function()
			{
				if(!Backend.SelectPopup.prototype.popup || !Backend.SelectPopup.prototype.popup.Event || !Backend.SelectPopup.prototype.popup.Event.observe)
				{
					return;
				}
				else
				{
					clearInterval(interval)
				}

				Backend.SelectPopup.prototype.popup.Event.observe(Backend.SelectPopup.prototype.popup, "unload", function()
				{
					window.selectPopupWindow = null;
				}.bind(this));

				window.selectPopupWindow = Backend.SelectPopup.prototype.popup;
				window.selectProductPopup = this;


			}.bind(this), 500);
		}
	},

	getSelectedObject: function(objectID, downloadable, indicator)
	{
		this.objectID = objectID;
		this.downloadable = downloadable;
		this.onObjectSelect.call(this, objectID, downloadable, indicator);
	}
}

/********************************************************************
 * Router / Url manipulator
 ********************************************************************/
Backend.Router = Router;

/********************************************************************
 * Progress bar
 ********************************************************************/
Backend.ProgressBar = Class.create();
Backend.ProgressBar.prototype =
{
	container: null,
	counter: null,
	total: null,
	progressBar: null,
	progressBarIndicator: null,

	initialize: function(container)
	{
		this.container = container;

		if (!container.down('.progressCount'))
		{
			this.createHTML();
		}

		this.counter = container.down('.progressCount');
		this.total = container.down('.progressTotal');
		this.progressBar = container.down('.progressBar');
		this.progressBarIndicator = container.down('.progressBarIndicator');
		this.update(0, 0);
	},

	createHTML: function()
	{
		this.container.innerHTML = '<div class="progressBarIndicator"></div><div class="progressBar"><span class="progressCount"></span><span class="progressSeparator"> / </span><span class="progressTotal"></span></div>';
	},

	update: function(progress, total)
	{
		if (progress < 0)
		{
			progress = 0;
		}

		this.counter.update(progress);
		this.total.update(total);

		if (parseFloat(total))
		{
			var progressWidth = (parseFloat(progress) / parseFloat(total)) * this.progressBar.clientWidth;
		}
		else
		{
			var progressWidth = 0;
		}

		this.progressBarIndicator.style.width = progressWidth + 'px';
	},

	getProgress: function()
	{
		return this.counter.innerHTML;
	},

	getTotal: function()
	{
		return this.total.innerHTML;
	},

	rewind: function(progress, total, step, onComplete)
	{
		if (progress > 0)
		{
			progress -= step;
			this.update(progress, total);
			setTimeout(function() { this.rewind(progress, total, step, onComplete) }.bind(this), 40);
		}
		else
		{
			if (onComplete)
			{
				onComplete();
			}
		}
	}
}

/*****************************************
	Multi-instance editor
******************************************/

Backend.MultiInstanceEditor = function(id, owner)
{
	this.id = id ? id : '';
	this.owner = owner;

	this.findUsedNodes();
	this.bindEvents();

	Form.State.backup(this.nodes.form, false, false);
}

Backend.MultiInstanceEditor.prototype =
{
	Links: {},
	Messages: {},
	Instances: {},
	CurrentId: null,

	namespace: null,

	hasInstance: function(id)
	{
		return this.Instances[id] ? true : false;
	},

	getCurrentId: function()
	{
		return this.namespace.prototype.CurrentId;
	},

	setCurrentId: function(id)
	{
		this.namespace.prototype.CurrentId = id;
	},

	craftTabUrl: function(url)
	{
		return url.replace(/_id_/, this.namespace.prototype.getCurrentId());
	},

	craftContentId: function(tabId)
	{
		return tabId + '_' +  this.namespace.prototype.getCurrentId() + 'Content'
	},

	getInstance: function(id, doInit, owner)
	{
		var root = this.namespace.prototype;

		if(!root.Instances[id])
		{
			root.Instances[id] = new this.namespace(id, owner);
		}

		if(doInit !== false)
		{
			root.Instances[id].init();
		}

		root.setCurrentId(id);

		return root.Instances[id];
	},

	getAddInstance: function()
	{
		return new this.namespace('');
	},

	getInstanceContainer: function(id)
	{
		throw 'Implement me';
	},

	getMainContainerId: function()
	{
		throw 'Implement me';
	},

	getAddContainerId: function()
	{
		throw 'Implement me';
	},

	getNavHashPrefix: function()
	{
		throw 'Implement me';
	},

	getListContainer: function()
	{
		throw 'Implement me';
	},

	getActiveGrid: function()
	{
		return false;
	},

	getNavHash: function(id)
	{
		var prefix = this.getNavHashPrefix();
		if (prefix)
		{
			return prefix + id;
		}
	},

	findUsedNodes: function()
	{
		this.nodes = {};
		this.nodes.parent = this.getInstanceContainer(this.id);
		this.nodes.form = this.nodes.parent.down("form");
		this.nodes.submit = this.nodes.form.down('input.submit');
	},

	bindEvents: function(args)
	{

	},

	init: function(args)
	{
		this.namespace.prototype.setCurrentId(this.id);

		this.showContainer($(this.getMainContainerId()));
		//Backend.showContainer(this.getMainContainerId());
		//this.getListContainer(this.owner).hide();

		this.tabControl = TabControl.prototype.getInstance(this.getMainContainerId(), false);

		this.setPath();
	},

	showContainer: function(container)
	{
		if (jQuery(container).data('dialog'))
		{
			jQuery(container).dialog('close');
		}

		jQuery(container).dialog(
		{
			autoOpen: false,
			title: this.getTitle(container),
			resizable: false,
			width: 'auto',
			autoResize: true,
			modal: true
		}).dialog('open');

		this.initContainer(container);
	},

	initContainer: function(container)
	{

	},

	setPath: function()
	{

	},

	cancelForm: function()
	{
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		//Form.restore(this.nodes.form, false, false);

		var container = jQuery($(this.getMainContainerId()));
		if (container.data('dialog'))
		{
			container.dialog('close');
		}

		//Backend.hideContainer(this.getMainContainerId());
		//this.getListContainer(this.owner).show();

		this.namespace.prototype.setCurrentId(0);
	},

	submitForm: function()
	{
		new LiveCart.AjaxRequest(
			this.nodes.form,
			false,
			function(responseJSON) {
				ActiveForm.prototype.resetErrorMessages(this.nodes.form);
				var responseObject = eval("(" + responseJSON.responseText + ")");
				this.afterSubmitForm(responseObject);
		   }.bind(this)
		);
	},

	afterSubmitForm: function(response)
	{
		if(response.status == 'success')
		{
			var grid = this.getActiveGrid();
			if (grid)
			{
				grid.reloadGrid();
			}

			Form.State.backup(this.nodes.form, false, false);
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}
	},

	open: function(id, e, onComplete, owner)
	{
		if (e)
		{
			e.preventDefault();

			if(!e.target)
			{
				e.target = e.srcElement
			}

			var progressIndicator = jQuery(e.target).closest('td').find('.progressIndicator');

			progressIndicator.show();
		}

		if (window.opener && window.opener.selectProductPopup)
		{
			window.opener.selectProductPopup.getSelectedObject(id);
			return;
		}

		var root = this.namespace.prototype;

		root.setCurrentId(id);

		var tabControl = TabControl.prototype.getInstance(
			root.getMainContainerId(owner),
			root.craftTabUrl.bind(this),
			root.craftContentId.bind(this)
		);

		tabControl.activateTab(null,
								   function(response)
								   {
										root.getInstance(id, true, owner);
										var navHash = root.getNavHash(id);
										if (navHash)
										{
											Backend.ajaxNav.add(navHash);
										}

										if (progressIndicator)
										{
											progressIndicator.hide();
										}
								   });

		if(root.hasInstance(id))
		{
			root.getInstance(id);
		}
	},

	resetEditors: function()
	{
		this.namespace.prototype.Instances = {};
		this.namespace.prototype.CurrentId = null;

		$(this.getMainContainerId(this.ownerID)).down('.sectionContainer').innerHTML = '';

		TabControl.prototype.__instances__ = {};
	},

	showAddForm: function(caller)
	{
		var container = $(this.getAddContainerId());

		// product form has already been downloaded
		if (this.formTabCopy)
		{
			container.update('');
			container.appendChild(this.formTabCopy);
			this.initAddForm();
		}

		// retrieve product form
		else
		{
			var url = this.Links.add;
			new LiveCart.AjaxUpdater(url, container, caller.up('.menu').down('.progressIndicator'), null, this.initAddForm.bind(this));
		}
	},

	hideAddForm: function()
	{
		if ($(this.getAddContainerId()))
		{
			jQuery($(this.getAddContainerId())).dialog('close');
		}
	},

	cancelAdd: function(noHide)
	{
		container = $(this.getAddContainerId());

		if (!noHide)
		{
			jQuery(container).dialog('close');
		}

		ActiveForm.prototype.destroyTinyMceFields(container);
		this.formTabCopy = container.down('form');
	},

	resetAddForm: function(form)
	{
		ActiveForm.prototype.resetTinyMceFields(form);
	},

	initAddForm: function()
	{
		container = $(this.getAddContainerId());

		jQuery(container).dialog('close');
		jQuery(container).dialog(
			{
				autoOpen: false,
				title: this.getTitle(container),
				resizable: false,
				width: 'auto',
				autoResize: true,
			}).dialog('open');

		this.initContainer(container);

		if (window.tinyMCE)
		{
			tinyMCE.idCounter = 0;
		}

		ActiveForm.prototype.initTinyMceFields(container);

		this.reInitAddForm(container);

		ActiveForm.prototype.resetErrorMessages(container.down('form'));

		var cancel = container.down('a.cancel');
		if (cancel)
		{
			Event.observe(cancel, 'click', function(e) { this.cancelAdd(); e.preventDefault(); }.bind(this));
		}
	},

	getTitle: function(container)
	{
		if (!jQuery(container).data('title'))
		{
			var title = jQuery(container).find('h2');
			jQuery(container).data('title', title.html());
		}

		jQuery(container).find('h2').hide();

		return jQuery(container).data('title');
	},

	saveAdd: function(e)
	{
		e.preventDefault();
		var instance = this.getAddInstance();
		instance.submitForm();
		return instance;
	},

	reInitAddForm: function(container)
	{
	}
}

var TabCustomize = Class.create();
TabCustomize.prototype =
{
	tabList: null,

	moreTabs: null,

	moreTabsMenu: null,

	saveUrl: null,

	initialize: function(tabList)
	{
		this.tabList = tabList;

		if (!(this.tabList instanceof HTMLUListElement))
		{
			this.tabList = this.tabList.down('ul');
		}

		this.moreTabs = tabList.parentNode.down('.moreTabs');

		if (this.moreTabs)
		{
			this.moreTabsMenu = this.moreTabs.down('.moreTabsMenu');
			Event.observe(this.moreTabs, 'click', this.toggleMenu.bindAsEventListener(this));

			// set background for moreTabs
			var el = this.moreTabs.parentNode;
			while (el && el.getStyle('background-color') == 'transparent')
			{
				el = el.parentNode;
			}

			if (el)
			{
				this.moreTabs.style.backgroundColor = el.getStyle('background-color');
			}
		}

		this.setPrefsSaveUrl(Backend.Router.createUrl('backend.index', 'setUserPreference'));
	},

	setPrefsSaveUrl: function(url)
	{
		this.saveUrl = url;
	},

	toggleMenu: function(e)
	{
		e.preventDefault();

		this.moreTabsMenu.innerHTML = '';
		var cloned = this.tabList.cloneNode(true);
		this.moreTabsMenu.appendChild(cloned);
		cloned.className = 'tabList tabs';

		$A(cloned.getElementsBySelector('li.hidden')).reverse().each(function(el)
		{
			el.parentNode.insertBefore(el, el.parentNode.firstChild);
		});

		$A(cloned.getElementsBySelector('li')).each(function(el)
		{
			var anchor = el.down('.tabName');
			if (anchor)
			{
				anchor.parentNode.replaceChild(document.createTextNode(anchor.firstChild.data), anchor);
			}

			el.id = 'toggle_' + el.id;
			Event.observe(el, 'click', this.toggleVisibility.bindAsEventListener(this));

			if (el.hasClassName('languageFormCaption'))
			{
				el.parentNode.removeChild(el);
			}
		}.bind(this));

		this.moreTabsMenu.show();

		Event.observe(document, 'click', this.hideMenu.bindAsEventListener(this), true);
	},

	toggleVisibility: function(e)
	{
		e.preventDefault();

		var li = Event.element(e);
		if ('LI' != li.tagName)
		{
			li = li.up('li');
		}

		var id = li.id.substr(7);

		var tab = this.tabList.down('#' + id);

		if (li.hasClassName('hidden'))
		{
			this.setVisible(tab, e);
		}
		else
		{
			this.setHidden(tab);
		}

		this.setPreference(tab);
		this.hideMenu();
	},

	setVisible: function(tab, e)
	{
		tab.removeClassName('hidden');
		tab.onclick(tab);
	},

	setHidden: function(tab)
	{
		tab.addClassName('hidden');
	},

	hideMenu: function()
	{
		this.moreTabsMenu.hide();
		this.moreTabsMenu.innerHTML = '';

		Event.stopObserving(document, 'click', this.hideMenu.bindAsEventListener(this), true);
	},

	setPreference: function(li)
	{
		var id = li.id.split(/__/).pop();

		var url = Backend.Router.setUrlQueryParam(this.saveUrl, 'key', 'tab_' + id);
		var url = Backend.Router.setUrlQueryParam(url, 'value', !li.hasClassName('hidden'));
		new LiveCart.AjaxRequest(url);
	}
}

Backend.initWidgets = function()
{
	jQuery(document).on('mouseover', '.fg-button', function() { jQuery(this).data('isActive', jQuery(this).hasClass('ui-state-active')).addClass('ui-state-hover').removeClass('ui-state-active'); })
	jQuery(document).on('mouseout', '.fg-button', function()
		{
			jQuery(this).removeClass('ui-state-hover');
			if (jQuery(this).data('isActive'))
			{
				jQuery(this).addClass('ui-state-active');
			}
		});
}

/************* Tooltips **************/

var tooltip=function(){
 var id = 'tt';
 var top = 3;
 var left = 3;
 var maxw = 350;
 var speed = 10;
 var timer = 20;
 var endalpha = 95;
 var alpha = 0;
 var tt,t,c,b,h;
 var ie = document.all ? true : false;
 return{
  show:function(v,w){
   if(tt == null){
    tt = document.createElement('div');
    tt.setAttribute('id',id);
    t = document.createElement('div');
    t.setAttribute('id',id + 'top');
    c = document.createElement('div');
    c.setAttribute('id',id + 'cont');
    b = document.createElement('div');
    b.setAttribute('id',id + 'bot');
    tt.appendChild(t);
    tt.appendChild(c);
    tt.appendChild(b);
    document.body.appendChild(tt);
    tt.style.opacity = 0;
    tt.style.filter = 'alpha(opacity=0)';
    document.onmousemove = this.pos;
   }
   tt.style.display = 'block';
   c.innerHTML = v;
   tt.style.width = w ? w + 'px' : 'auto';
   if(!w && ie){
    t.style.display = 'none';
    b.style.display = 'none';
    tt.style.width = tt.offsetWidth;
    t.style.display = 'block';
    b.style.display = 'block';
   }
  if(tt.offsetWidth > maxw){tt.style.width = maxw + 'px'}
  h = parseInt(tt.offsetHeight) + top;
  clearInterval(tt.timer);
  tt.timer = setInterval(function(){tooltip.fade(1)},timer);
  },
  pos:function(e){
   var u = ie ? event.clientY + document.documentElement.scrollTop : e.pageY;
   var l = ie ? event.clientX + document.documentElement.scrollLeft : e.pageX;
   var y = u - h;
   var x = l + left;

   if (y < 0)
   {
	   y = 0;
   }

   if (x < 0)
   {
	   x = 0;
   }

   tt.style.top = y + 'px';
   tt.style.left = x + 'px';
  },
  fade:function(d){
   var a = alpha;
   if((a != endalpha && d == 1) || (a != 0 && d == -1)){
    var i = speed;
   if(endalpha - a < speed && d == 1){
    i = endalpha - a;
   }else if(alpha < speed && d == -1){
     i = a;
   }
   alpha = a + (i * d);
   tt.style.opacity = alpha * .01;
   tt.style.filter = 'alpha(opacity=' + alpha + ')';
  }else{
    clearInterval(tt.timer);
     if(d == -1){tt.style.display = 'none'}
  }
 },
 hide:function(){
  clearInterval(tt.timer);
   tt.timer = setInterval(function(){tooltip.fade(-1)},timer);
  }
 };
}();
