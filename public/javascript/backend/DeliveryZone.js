/**
 *	@author Integry Systems
 */

Backend.DeliveryZone = Class.create();
Backend.DeliveryZone.prototype =
{
	Links: {},
	Messages: {},

	treeBrowser: null,

	urls: new Array(),

	initialize: function(zones)
	{

		Backend.DeliveryZone.prototype.treeBrowser = new dhtmlXTreeObject("deliveryZoneBrowser","","", 0);
		Backend.Breadcrumb.setTree(Backend.DeliveryZone.prototype.treeBrowser);

		Backend.DeliveryZone.prototype.treeBrowser.def_img_x = 'auto';
		Backend.DeliveryZone.prototype.treeBrowser.def_img_y = 'auto';

		Backend.DeliveryZone.prototype.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		Backend.DeliveryZone.prototype.treeBrowser.setOnClickHandler(this.activateZone.bind(this));

		Event.observe($("newZoneInputButton"), 'click', function(e){ e.preventDefault(); this.addNewZone(); }.bind(this));

		Backend.DeliveryZone.prototype.treeBrowser.showFeedback =
			function(itemId)
			{
				if (!this.iconUrls)
				{
					this.iconUrls = new Object();
				}

				this.iconUrls[itemId] = this.getItemImage(itemId, 0, 0);
				this.setItemImage(itemId, '../../../image/indicator.gif');
			};

		Backend.DeliveryZone.prototype.treeBrowser.hideFeedback =
			function()
			{
				for (var itemId in this.iconUrls)
				{
					this.setItemImage(itemId, this.iconUrls[itemId]);
				}
			};

		$(Backend.DeliveryZone.prototype.treeBrowser.allTree).addClassName("hidden");
		this.insertTreeBranch(zones, 0);

		if (zones[1]['items'])
		{
			var index, len = zones[1]['items'].length;
			// zones[0] - default, zones[1] - delivery zones, zones[2] - tax zones
			// items for delivery and tax zones should be equal, because any of them can be converted to type 'both zones'.
			for(index=0; index < len; index++)
			{
				this.updateTreeBrowserNodeVisibility(index, zones[1]['items'][index].type, /*fix tree lines only when adding last item */ index+1 == len, true);
			}
		}

		$(Backend.DeliveryZone.prototype.treeBrowser.allTree).removeClassName("hidden");

		if(!Backend.ajaxNav.getHash().match(/zone_-?\d+#\w+/)) window.location.hash = 'zone_-1#tabDeliveryZoneShipping__';
		this.tabControl = TabControl.prototype.getInstance('deliveryZoneManagerContainer', this.craftTabUrl, this.craftContainerId, {});

		this.bindEvents();
	},

	bindEvents: function()
	{
		Event.observe($("deliveryZone_delete"), 'click', function(e)
		{
			e.preventDefault();
			this.deleteZone();
		}.bind(this));
	},

	deleteZone: function()
	{
		if(confirm(Backend.DeliveryZone.prototype.Messages.confirmZoneDelete))
		{
			new LiveCart.AjaxRequest(
				Backend.DeliveryZone.prototype.Links.remove + '/' + Backend.DeliveryZone.prototype.activeZone,
				false,
				function(response)
				{
					response = eval("(" + response.responseText + ")");
					if('success' == response.status)
					{
						var secondId = Backend.DeliveryZone.prototype.getTreeBrowserItemIdInSecondLeaf(Backend.DeliveryZone.prototype.activeZone);
						if (secondId)
						{
							Backend.DeliveryZone.prototype.treeBrowser.deleteItem(secondId, true);
						}
						Backend.DeliveryZone.prototype.treeBrowser.deleteItem(Backend.DeliveryZone.prototype.activeZone, true);
						var firstId = parseInt(Backend.DeliveryZone.prototype.treeBrowser._globalIdStorage[1]);
						if(firstId)
						{
							Backend.DeliveryZone.prototype.treeBrowser.selectItem(firstId, true);
						}
					}
				}.bind(this)
			);
		}
	},

	addNewZone: function()
	{
		new LiveCart.AjaxRequest(
			Backend.DeliveryZone.prototype.Links.create,
			false,
			function(response)
			{
				this.afterNewZoneAdded(eval("(" + response.responseText + ")"));
			}.bind(this)
		);
	},

	afterNewZoneAdded: function(response)
	{
		var id = response.zone.ID;
		var chunks = new String(id).split("_");
		if (chunks.length == 2)
		{
			id = chunks[1];
		}
		Backend.DeliveryZone.prototype.treeBrowser.insertNewItem(-2, id, response.zone.name, 0, 0, 0, 0, 'SELECT');
		Backend.DeliveryZone.prototype.treeBrowser.insertNewItem(-3, id, response.zone.name, 0, 0, 0, 0);

		this.activateZone(id);
	},

	craftTabUrl: function(url)
	{
		var id = Backend.DeliveryZone.prototype.treeBrowser.getSelectedItemId(),
			chunks =  id.split('_');
		if (chunks.length >= 2)
		{
			id = chunks[1];
		}
		return url.replace(/_id_/, id);
	},

	craftContainerId: function(tabId)
	{
		return tabId + '_' +  Backend.DeliveryZone.prototype.treeBrowser.getSelectedItemId() + 'Content';
	},

	insertTreeBranch: function(treeBranch, rootId)
	{
		$A(treeBranch).each(function(node)
		{
			Backend.DeliveryZone.prototype.treeBrowser.insertNewItem(rootId, node.ID, node.name, null, 0, 0, 0, '', 1);
			this.treeBrowser.showItemSign(node.ID, 0);
			var zone = document.getElementsByClassName("standartTreeRow", $("deliveryZoneBrowser")).last();
			zone.id = 'zone_' + node.ID;
			zone.onclick = function()
			{
				Backend.DeliveryZone.prototype.treeBrowser.selectItem(node.ID, true);
			};
			if (node.items && node.items.length)
			{
				this.insertTreeBranch(node.items, node.ID);
			}
		}.bind(this));
	},

	activateZone: function(id)
	{
		var chunks = new String(id).split("_");
		if (chunks.length == 2)
		{
			id = chunks[1]; // if one zone id is added to tree more than once, id has prefix of random number seperated by _
		}

		// disable clicking on delivery, tax zone and any unknown node
		if(id == -2 || id == -3 || isNaN(id))
		{
			Backend.DeliveryZone.prototype.treeBrowser.selectItem(Backend.DeliveryZone.prototype.selectedItemId);
			return;
		}
		Backend.DeliveryZone.prototype.selectedItemId = chunks.join("_");
		// --

		Backend.Breadcrumb.display(id);
		if(id == -1)
		{
			if(Backend.ajaxNav.getHash().match(/tabDeliveryZoneCountry/))
			{
				Backend.ajaxNav.ignoreNextAdd = false;
				Backend.ajaxNav.add('zone_' + id + '#tabDeliveryZoneShipping');
				Backend.ajaxNav.ignoreNextAdd = true;
			}

			var activateTab = $('tabDeliveryZoneShipping');
			$("tabDeliveryZoneCountry").hide();
			$("tabDeliveryZoneTaxes").show();
			$("tabDeliveryZoneShipping").show();
			$("deliveryZone_delete").parentNode.hide();

			$("tabDeliveryZoneShipping").removeClassName("hidden");
			$("tabDeliveryZoneTaxes").removeClassName("hidden");
		}
		else
		{
			var activateTab = $('tabDeliveryZoneCountry');
			$("tabDeliveryZoneCountry").show();
			//$("tabDeliveryZoneTaxes").show();
			$("deliveryZone_delete").parentNode.show();
		}

		if(Backend.DeliveryZone.prototype.activeZone && Backend.DeliveryZone.prototype.activeZone != id)
		{
			Backend.DeliveryZone.prototype.activeZone = id;
			Backend.DeliveryZone.prototype.treeBrowser.showFeedback(id);

			Backend.ajaxNav.add('zone_' + id);

			this.tabControl.activateTab(activateTab, function() {
				Backend.DeliveryZone.prototype.treeBrowser.hideFeedback(id);
			});
		}
		Backend.DeliveryZone.prototype.activeZone = id;
		Backend.DeliveryZone.prototype.updateBreadCrumbText();

		if($("countriesAndStates_"+id))
		{
			Backend.DeliveryZone.CountriesAndStates.prototype.getInstance($("countriesAndStates_"+id)).changeType();
		}
	},

	displayCategory: function(response)
	{
		Backend.DeliveryZone.prototype.treeBrowser.hideFeedback();
		var cancel = document.getElementsByClassName('cancel', $('deliveryZoneContent'))[0];
		Event.observe(cancel, 'click', this.resetForm.bindAsEventListener(this));
	},

	resetForm: function(e)
	{
		var el = Event.element(e);
		while (el.tagName != 'FORM')
		{
			el = el.parentNode;
		}

		el.reset();
	},

	save: function(form)
	{
		var indicator = document.getElementsByClassName('progressIndicator', form)[0];
		new LiveCart.AjaxRequest(form, indicator, this.displaySaveConfirmation.bind(this));
	},

	displaySaveConfirmation: function()
	{
		new Backend.SaveConfirmationMessage(document.getElementsByClassName('yellowMessage')[0]);
	},


	updateTreeBrowserNodeVisibility: function(index, type, fixTreeLines)
	{
		var i, z, taxRow, deliveryRow, visibleItems, lastVisible, img;

		if (typeof fixTreeLines == "undefined")
		{
			fixTreeLines = true;
		}

		z = $(Backend.DeliveryZone.prototype.treeBrowser.allTree).down("tbody").childElements();
		// z[1] == default zone
		// z[2] == delivery zones
		// z[3] == tax zones

		deliveryRow = $($(z[2]).down("tbody").childElements()[index+1]);
		taxRow = $($(z[3]).down("tbody").childElements()[index+1]);

		if (type == 0)
		{
			deliveryRow.removeClassName("hidden");
			$("tabDeliveryZoneShipping").removeClassName("hidden");
			taxRow.removeClassName("hidden");
			$("tabDeliveryZoneTaxes").removeClassName("hidden");
		}
		else if(type == 1)
		{
			deliveryRow.addClassName("hidden");
			$("tabDeliveryZoneShipping").addClassName("hidden");
			taxRow.removeClassName("hidden");
			$("tabDeliveryZoneTaxes").removeClassName("hidden");
		}
		else if(type == 2)
		{
			deliveryRow.removeClassName("hidden");
			$("tabDeliveryZoneShipping").removeClassName("hidden");
			taxRow.addClassName("hidden");
			$("tabDeliveryZoneTaxes").addClassName("hidden");
		}

		if (fixTreeLines == false)
		{
			return;
		}
		// change line images: line3.gif to line2.gif for last visible, and line2.gif to line3.gif for all other
		for(i=2; i<=3; i++)
		{
			try {
				visibleItems = $A($(z[i]).down("tbody").childElements()).inject([], function(r, item) {
					if ($(item).hasClassName("hidden") == false)
					{
						r.push(item);
					}
					return r;
				});
				if (visibleItems.length > 0)
				{
					lastVisible = visibleItems.pop();
					visibleItems.shift(); // ignore first row, it does not belong to any tree item.
					$A(visibleItems).each(function(item){
						img = $(item).down("table").down("td").down("img");
						img.src = img.src.replace("line2.gif", "line3.gif");
					});

					if ($(lastVisible).down("table"))
					{
						img = $(lastVisible).down("table").down("td").down("img");
					}

					if (img)
					{
						img.src = img.src.replace("line3.gif", "line2.gif");
					}
				}
			} catch(e) {}
		}
	},

	getTreeBrowserItemIdInSecondLeaf: function(id)
	{
		try {
			return Backend.DeliveryZone.prototype.treeBrowser.getAllLeafs().match(new RegExp("[0-9]+_"+id)).shift();
		} catch(e){
			return false;
		}
	},

	updateBreadCrumbText: function()
	{
		var parentId = Backend.DeliveryZone.prototype.treeBrowser.getParentId(Backend.DeliveryZone.prototype.selectedItemId);
		if (parentId == -3)
		{
			jQuery("pageTitle a").html(Backend.getTranslation("_tax_zones"));
		}
		else if(parentId == -2)
		{
			jQuery("pageTitle a").html(Backend.getTranslation("_delivery_zones"));
		}
	}
};

Backend.DeliveryZone.CountriesAndStates = Class.create();
Backend.DeliveryZone.CountriesAndStates.prototype =
{
	Links: {},
	Messages: {},

	CallbacksCity: {
		'beforeDelete': function(li)
		{
			if(confirm(Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmAddressDelete))
			{
				return Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteCityMask + "/" + this.getRecordId(li);
			}
		},
		'afterDelete': function(li, response)
		{
			 try
			 {
				 response = eval('(' + response + ')');
			 }
			 catch(e)
			 {
				 return false;
			 }
		},
		'beforeEdit': function(li)
		{
			Backend.DeliveryZone.CountriesAndStates.prototype.toggleMask(li);
		},
		'afterEdit': function(li, response) {}
	},
	CallbacksZip: {
		'beforeDelete': function(li)
		{
			if(confirm(Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmAddressDelete))
			{
				return Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteZipMask + "/" + this.getRecordId(li);
			}
		},
		'afterDelete': function(li, response)
		{
			 try
			 {
				 response = eval('(' + response + ')');
			 }
			 catch(e)
			 {
				 return false;
			 }
		},
		'beforeEdit': function(li)
		{
			Backend.DeliveryZone.CountriesAndStates.prototype.toggleMask(li);
		},
		'afterEdit': function(li, response) {}
	},
	CallbacksAddress: {
		'beforeDelete': function(li)
		{
			if(confirm(Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmAddressDelete))
			{
				return Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteAddressMask + "/" + this.getRecordId(li);
			}
		},
		'afterDelete': function(li, response)
		{
			 try
			 {
				 response = eval('(' + response + ')');
			 }
			 catch(e)
			 {
				 return false;
			 }
		},
		'beforeEdit': function(li)
		{
			Backend.DeliveryZone.CountriesAndStates.prototype.toggleMask(li);
		},
		'afterEdit': function(li, response) {}
	},


	prefix: 'countriesAndStates_',
	instances: {},

	initialize: function(root, zoneID)
	{
		this.zoneID = zoneID;

		this.findNodes(root);
		this.bindEvents();

		// I did't found out if sorting the fields realy matters, but they are slowing things down for sure
//		this.sortSelect(this.nodes.inactiveCountries);
//		this.sortSelect(this.nodes.activeCountries);
//		this.sortSelect(this.nodes.inactiveStates);
//		this.sortSelect(this.nodes.activeStates);

		// trigger type change event to properly initialize things that depends from type (visible tabs, selection in tree browser).
		this.changeType();
	},

	getInstance: function(root, zoneID)
	{
		if(!Backend.DeliveryZone.CountriesAndStates.prototype.instances[$(root).id])
		{
			Backend.DeliveryZone.CountriesAndStates.prototype.instances[$(root).id] = new Backend.DeliveryZone.CountriesAndStates(root, zoneID);
		}
		return Backend.DeliveryZone.CountriesAndStates.prototype.instances[$(root).id];
	},

	findNodes: function(root)
	{
		this.nodes = {};
		this.nodes.root = $(root);

		this.nodes.type = $("type_"+this.zoneID);

		this.nodes.form = this.nodes.root.tagName == 'FORM' ? this.nodes.root : this.nodes.root.down('form');

		this.nodes.name = this.nodes.form.down('.' + this.prefix + 'name');

		this.nodes.addCountryButton		   = this.nodes.root.down('.' + this.prefix + 'addCountry');
		this.nodes.removeCountryButton		= this.nodes.root.down('.' + this.prefix + 'removeCountry');
		this.nodes.addStateButton			 = this.nodes.root.down('.' + this.prefix + 'addState');
		this.nodes.removeStateButton		  = this.nodes.root.down('.' + this.prefix + 'removeState');

		this.nodes.inactiveCountries		= this.nodes.root.down('.' + this.prefix + 'inactiveCountries');
		this.nodes.activeCountries			= this.nodes.root.down('.' + this.prefix + 'activeCountries');
		this.nodes.inactiveStates			= this.nodes.root.down('.' + this.prefix + 'inactiveStates');
		this.nodes.activeStates				= this.nodes.root.down('.' + this.prefix + 'activeStates');
		this.nodes.countrySelector			= this.nodes.root.down('.stateListCountry');

		this.nodes.cityMasks				  = this.nodes.root.down('.' + this.prefix + 'cityMasks');
		this.nodes.cityMasksList			  = this.nodes.cityMasks.down('.' + this.prefix + 'cityMasksList');
		this.nodes.cityMaskNew				= this.nodes.cityMasks.down('.' + this.prefix + 'newMask');
		this.nodes.cityMaskNewButton		  = this.nodes.cityMasks.down('.' + this.prefix + 'newMaskButton');
		this.nodes.cityMaskNewCancelButton	= this.nodes.cityMasks.down('.' + this.prefix + 'cancelNewMask');
		this.nodes.cityMaskNewShowButton	  = this.nodes.cityMasks.down('.' + this.prefix + 'showNewMaskForm');
		this.nodes.cityMaskNewForm			= this.nodes.cityMasks.down('.' + this.prefix + 'maskForm');

		this.nodes.zipMasks				   = this.nodes.root.down('.' + this.prefix + 'zipMasks');
		this.nodes.zipMasksList			   = this.nodes.zipMasks.down('.' + this.prefix + 'zipMasksList');
		this.nodes.zipMaskNew				 = this.nodes.zipMasks.down('.' + this.prefix + 'newMask');
		this.nodes.zipMaskNewButton		   = this.nodes.zipMasks.down('.' + this.prefix + 'newMaskButton');
		this.nodes.zipMaskNewCancelButton	 = this.nodes.zipMasks.down('.' + this.prefix + 'cancelNewMask');
		this.nodes.zipMaskNewShowButton	   = this.nodes.zipMasks.down('.' + this.prefix + 'showNewMaskForm');
		this.nodes.zipMaskNewForm			 = this.nodes.zipMasks.down('.' + this.prefix + 'maskForm');

		this.nodes.addressMasks			   = this.nodes.root.down('.' + this.prefix + 'addressMasks');
		this.nodes.addressMasksList		   = this.nodes.addressMasks.down('.' + this.prefix + 'addressMasksList');
		this.nodes.addressMaskNew			 = this.nodes.addressMasks.down('.' + this.prefix + 'newMask');
		this.nodes.addressMaskNewButton	   = this.nodes.addressMasks.down('.' + this.prefix + 'newMaskButton');
		this.nodes.addressMaskNewCancelButton = this.nodes.addressMasks.down('.' + this.prefix + 'cancelNewMask');
		this.nodes.addressMaskNewShowButton   = this.nodes.addressMasks.down('.' + this.prefix + 'showNewMaskForm');
		this.nodes.addressMaskNewForm		 = this.nodes.addressMasks.down('.' + this.prefix + 'maskForm');

		this.nodes.zonesAndUnions			 = this.nodes.root.down('.' + this.prefix + 'regionsAndUnions').getElementsByTagName('a');

		this.nodes.observedElements = document.getElementsByClassName("observed", this.nodes.form);
	},

	bindExistingMask: function(li)
	{
		li = $(li);
		Event.observe(li.down(".countriesAndStates_saveMaskButton"), "click", function(e)
		{
			e.preventDefault();
			Backend.DeliveryZone.CountriesAndStates.prototype.toggleMask(li)
		}.bind(this));

		Event.observe(li.down(".countriesAndStates_cancelMask"), "click", function(e)
		{
			e.preventDefault();
			Backend.DeliveryZone.CountriesAndStates.prototype.toggleMask(li, true);
		}.bind(this));
	},

	toggleMask: function(li, cancel)
	{
		var self = Backend.DeliveryZone.CountriesAndStates.prototype.getInstance(li.up('form'));
		var input = li.down('input');
		var form  = li.down('.' + Backend.DeliveryZone.CountriesAndStates.prototype.prefix + 'existingMaskForm');
		var title = li.down('.maskTitle');
		if(form.style.display != 'none')
		{
			if(cancel)
			{
				input.value = li.oldValue
			}

			var url = null;
			var list = null;

			if((list = $(input).up('.' + Backend.DeliveryZone.CountriesAndStates.prototype.prefix + 'cityMasksList')))
			{
				url = Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveCityMask;
			}
			else if((list = $(input).up('.' + Backend.DeliveryZone.CountriesAndStates.prototype.prefix + 'zipMasksList')))
			{
				url = Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveZipMask;
			}
			else if((list = $(input).up('.' + Backend.DeliveryZone.CountriesAndStates.prototype.prefix + 'addressMasksList')))
			{
				url = Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveAddressMask;
			}

			var activeList = ActiveList.prototype.getInstance(list);
			if(!cancel)
			{
				new LiveCart.AjaxRequest(
					url + "/" + activeList.getRecordId(li) + '&mask=' + li.down('input').value,
					false,
					function(response) {
						var response = eval('(' + response.responseText + ')');

						if(response.status == 'success')
						{
							form.style.display = 'none';
							title.style.display = 'inline';
							title.update(input.value);
							ActiveForm.prototype.resetErrorMessage(input);
						}
						else
						{
							ActiveForm.prototype.setErrorMessage(input, response.errors.mask, true);
						}
					}.bind(self)
				);
			}
			else
			{
				form.style.display = 'none';
				title.style.display = 'inline';
			}
		}
		else
		{
			li.oldValue = input.value;
			form.style.display = 'inline';
			title.style.display = 'none';
		}
	},

	bindEvents: function()
	{
		Event.observe(this.nodes.type, 'change', function(e) { e.preventDefault(); this.changeType(); }.bind(this));
		Event.observe(this.nodes.addCountryButton, 'click', function(e) { e.preventDefault(); this.addCountry(); }.bind(this));
		Event.observe(this.nodes.removeCountryButton, 'click', function(e) { e.preventDefault(); this.removeCountry(); }.bind(this));
		Event.observe(this.nodes.addStateButton, 'click', function(e) { e.preventDefault(); this.addState(); }.bind(this));
		Event.observe(this.nodes.form, 'submit', function(e) { e.preventDefault(); this.save(); }.bind(this));
		Event.observe(this.nodes.removeStateButton, 'click', function(e) { e.preventDefault(); this.removeState(); }.bind(this));
		Event.observe(this.nodes.cityMaskNewButton, 'click', function(e) { e.preventDefault(); this.addNewCityMask(this.nodes.cityMaskNew); }.bind(this));
		Event.observe(this.nodes.zipMaskNewButton, 'click', function(e) { e.preventDefault(); this.addNewZipMask(this.nodes.zipMaskNew); }.bind(this));
		Event.observe(this.nodes.addressMaskNewButton, 'click', function(e) { e.preventDefault(); this.addNewAddressMask(this.nodes.addressMaskNew); }.bind(this));

		Event.observe(this.nodes.cityMaskNewCancelButton, 'click', function(e) { e.preventDefault(); this.cancelNewMaskForm(this.nodes.cityMaskNewCancelButton.up("fieldset")); }.bind(this));
		Event.observe(this.nodes.zipMaskNewCancelButton, 'click', function(e) { e.preventDefault(); this.cancelNewMaskForm(this.nodes.zipMaskNewCancelButton.up("fieldset")); }.bind(this));
		Event.observe(this.nodes.addressMaskNewCancelButton, 'click', function(e) { e.preventDefault(); this.cancelNewMaskForm(this.nodes.addressMaskNewCancelButton.up("fieldset")); }.bind(this));

		Event.observe(this.nodes.cityMaskNewShowButton, 'click', function(e) { e.preventDefault(); this.showNewMaskForm(this.nodes.cityMaskNewShowButton.up("fieldset")); }.bind(this));
		Event.observe(this.nodes.zipMaskNewShowButton, 'click', function(e) { e.preventDefault(); this.showNewMaskForm(this.nodes.zipMaskNewShowButton.up("fieldset")); }.bind(this));
		Event.observe(this.nodes.addressMaskNewShowButton, 'click', function(e) { e.preventDefault(); this.showNewMaskForm(this.nodes.addressMaskNewShowButton.up("fieldset")); }.bind(this));

		Event.observe(this.nodes.countrySelector, 'change', this.reloadStateList.bind(this));

		$A(this.nodes.zonesAndUnions).each(function(zoneOrUnion) {
			Event.observe(zoneOrUnion, 'click', function(e) { e.preventDefault(); this.selectZoneOrUnion(zoneOrUnion.hash.substring(0,1) == '#' ? zoneOrUnion.hash.substring(1) : zoneOrUnion.hash); }.bind(this));
		}.bind(this));

		$A(this.nodes.observedElements).each(function(element) {
			Event.observe(element, 'change', function(e) { this.save(e) }.bind(this));
		}.bind(this));
		window.onunload = function(e) { this.save(); }.bind(this);
		Event.observe(this.nodes.addressMaskNew, 'keyup', function(e)
		{
		   if(KeyboardEvent.prototype.init(e).isEnter())
		   {
			   if(!e.target) e.target = e.srcElement;
			   this.addNewAddressMask(e.target);
		   }
		}.bind(this));

		Event.observe(this.nodes.zipMaskNew, 'keyup', function(e)
		{
		   if(KeyboardEvent.prototype.init(e).isEnter())
		   {
			   if(!e.target) e.target = e.srcElement;
			   this.addNewZipMask(e.target);
		   }
		}.bind(this));

		Event.observe(this.nodes.cityMaskNew, 'keyup', function(e)
		{
		   if(KeyboardEvent.prototype.init(e).isEnter())
		   {
			   if(!e.target) e.target = e.srcElement;
			   this.addNewCityMask(e.target);
		   }
		}.bind(this));

		document.getElementsByClassName(this.prefix + 'mask').each(function(mask) {
		   this.bindMask(mask);
		}.bind(this));
	},

	bindMask: function(mask)
	{
	   Event.observe(mask, 'keyup', function(e)
	   {
		   if(KeyboardEvent.prototype.init(e).isEnter()) {
			   if(!e.target) e.target = e.srcElement;
			   this.toggleMask(e.target.up('li'));
		   }
	   }.bind(this));
	},

	cancelNewMaskForm: function(maskRoot)
	{
		var input = maskRoot.down("input");
		var form  = maskRoot.down('.' + this.prefix + 'maskForm');
		var show  = maskRoot.down('.' + this.prefix + 'showNewMaskForm');

		input.value = "";
		show.show();
		form.hide();
	},

	showNewMaskForm: function(maskRoot)
	{
		var input = maskRoot.down("input");
		var form  = maskRoot.down('.' + this.prefix + 'maskForm');
		var show  = maskRoot.down('.' + this.prefix + 'showNewMaskForm');

		input.focus();
		show.hide();
		form.show();
	},

	save: function(e) {
		this.saving = true;
		var indicator = $("tabDeliveryZoneCountry").down("span");
		indicator.addClassName("progressIndicator");
		if (e)
		{
			var el = Event.element(e);
			if (el.hasClassName('checkbox'))
			{
				el.hide();
			}
			//indicator = el.parentNode.down('.progressIndicator');
		}
		Backend.DeliveryZone.prototype.treeBrowser.setItemText(Backend.DeliveryZone.prototype.activeZone, this.nodes.name.value);
		var secodId = Backend.DeliveryZone.prototype.getTreeBrowserItemIdInSecondLeaf(Backend.DeliveryZone.prototype.activeZone);
		if (secodId)
		{
			Backend.DeliveryZone.prototype.treeBrowser.setItemText(secodId, this.nodes.name.value)
		}
		var request = new LiveCart.AjaxRequest(this.nodes.form, indicator, function() { if (el) { el.show(); }});
		this.saving = false;
	},

	selectZoneOrUnion: function(regionName)
	{
		includedCountriesCodes = Backend.DeliveryZone.countryGroups[regionName];

		$A(this.nodes.inactiveCountries.options).each(function(option)
		{
			option.selected = includedCountriesCodes.indexOf(option.value) !== -1 ? true : false;
		});
	},

	addNewCityMask: function(mask)
	{
		new LiveCart.AjaxRequest(
			Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveCityMask + "/" + '?zoneID=' + this.zoneID + '&mask=' + mask.value,
			false,
			function(response)
			{
				response = eval('(' + response.responseText + ')');
				if('success' == response.status)
				{
					var activeList = ActiveList.prototype.getInstance(this.nodes.cityMasksList);
					var li = activeList.addRecord(response.ID, '<span class="maskTitle">' + mask.value + '</span><input type="text" value="' + mask.value + '" style="display:none;" />', true);

					mask.value = '';
					this.bindMask(li.down('input'));
					ActiveForm.prototype.resetErrorMessage(mask);

					this.cancelNewMaskForm(this.nodes.cityMaskNewCancelButton.up("fieldset"));
				}
				else
				{
					ActiveForm.prototype.setErrorMessage(mask, response.errors.mask, true);
				}
			}.bind(this)
		);
	},

	addNewZipMask: function(mask)
	{
		new LiveCart.AjaxRequest (
			Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveZipMask + "/" + '?zoneID=' + this.zoneID + '&mask=' + mask.value,
			false,
			function(response)
			{
				response = eval('(' + response.responseText + ')');
				if('success' == response.status)
				{
					var activeList = ActiveList.prototype.getInstance(this.nodes.zipMasksList);
					var li = activeList.addRecord(response.ID, '<span class="maskTitle">' + mask.value + '</span><input type="text" value="' + mask.value + '" style="display:none;" />', true);

					mask.value = '';
					this.bindMask(li.down('input'));
					ActiveForm.prototype.resetErrorMessage(mask);

					this.cancelNewMaskForm(this.nodes.zipMaskNewCancelButton.up("fieldset"));
				}
				else
				{
					ActiveForm.prototype.setErrorMessage(mask, response.errors.mask, true);
				}
			}.bind(this)
		);
	},

	addNewAddressMask: function(mask)
	{
		new LiveCart.AjaxRequest(
			Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveAddressMask + "/" + '?zoneID=' + this.zoneID + '&mask=' + mask.value,
			false,
			function(response)
			{
				response = eval('(' + response.responseText + ')');
				if('success' == response.status)
				{
					var activeList = ActiveList.prototype.getInstance(this.nodes.addressMasksList);
					var li = activeList.addRecord(response.ID, '<span class="maskTitle">' + mask.value + '</span><input type="text" value="' + mask.value + '" style="display:none;" />', true);

					mask.value = '';
					this.bindMask(li.down('input'));
					ActiveForm.prototype.resetErrorMessage(mask);

					this.cancelNewMaskForm(this.nodes.addressMaskNewCancelButton.up("fieldset"));
				}
				else
				{
					ActiveForm.prototype.setErrorMessage(mask, response.errors.mask, true);
				}
			}.bind(this)
		);
	},

	sortSelect: function(select)
	{
		var options = [];
		$A(select.options).each(function(option) { options[options.length] = option; });
		select.options.length = 0;
		optionso = options.sort(function(a, b){ return (b.text < a.text) - (a.text < b.text); });
		$A(options).each(function(option) { select.options[select.options.length] = option; });
	},

	moveSelectedOptions: function(fromSelect, toSelect)
	{
		fromSelectOptions = fromSelect.options;
		toSelectOptions = toSelect.options;

		// Deselect options
		$A(toSelectOptions).each(function(option) { option.selected = false; });

		// move options to another list
		$A(fromSelectOptions).each(function(option)
		{
			if(option.selected)
			{
				toSelectOptions[toSelectOptions.length] = option;
			}
		});

		this.sortSelect(toSelect);
	},

	sendSelectsData: function(activeSelect, inactiveSelect, url)
	{
		var active = "";
		$A(activeSelect.options).each(function(option) {
			active += "active[]=" + option.value + "&";
		});
		active.substr(0, active.length - 1);


		var inactive = "";
		$A(inactiveSelect.options).each(function(option) {
			inactive += "inactive[]=" + option.value + "&";
		});
		inactive.substr(0, active.length - 1);


		new LiveCart.AjaxRequest(url + "/" + this.zoneID, null, null, {parameters: active + inactive});
	},

	addCountry: function()
	{
		this.moveSelectedOptions(this.nodes.inactiveCountries, this.nodes.activeCountries);
		this.sendSelectsData(this.nodes.activeCountries, this.nodes.inactiveCountries, Backend.DeliveryZone.prototype.Links.saveCountries);
	},

	removeCountry: function()
	{
		this.moveSelectedOptions(this.nodes.activeCountries, this.nodes.inactiveCountries);
		this.sendSelectsData(this.nodes.activeCountries, this.nodes.inactiveCountries, Backend.DeliveryZone.prototype.Links.saveCountries);
	},

	addState: function()
	{
		this.moveSelectedOptions(this.nodes.inactiveStates, this.nodes.activeStates);
		this.sendSelectsData(this.nodes.activeStates, this.nodes.inactiveStates, Backend.DeliveryZone.prototype.Links.saveStates);
	},

	removeState: function()
	{
		this.moveSelectedOptions(this.nodes.activeStates, this.nodes.inactiveStates);
		this.sendSelectsData(this.nodes.activeStates, this.nodes.inactiveStates, Backend.DeliveryZone.prototype.Links.saveStates);
	},

	reloadStateList: function()
	{
		var sel = this.nodes.countrySelector;
		var url = Backend.Router.createUrl('backend.deliveryZone', 'loadStates', {id: this.zoneID, country: $F(sel)});
		new LiveCart.AjaxRequest(url, sel.parentNode.down('.progressIndicator'), this.completeReloadStateList.bind(this));
	},

	completeReloadStateList: function(originalRequest)
	{
		var list = this.nodes.inactiveStates;
		list.innerHTML = '';
		$H(originalRequest.responseData).each(function(val)
		{
			list.innerHTML += '<option value="' + val[0] + '">' + val[1] + '</option>';
		});
	},

	changeType: function()
	{
		// fix treeBrowser selection, if selected item is about to be become hidden
		var parentId = Backend.DeliveryZone.prototype.treeBrowser.getParentId(Backend.DeliveryZone.prototype.selectedItemId);

		if (/* selected delivery zones child  && in type dropdown selected tax zone */
			parentId == -2 && this.nodes.type.value == 1)
		{
			Backend.DeliveryZone.prototype.selectedItemId = Backend.DeliveryZone.prototype.getTreeBrowserItemIdInSecondLeaf(Backend.DeliveryZone.prototype.activeZone);
			Backend.DeliveryZone.prototype.treeBrowser.selectItem(Backend.DeliveryZone.prototype.selectedItemId);
		}
		else if (/* selected tax zones child  && in type dropdown selected delivery zone */
				parentId == -3 && this.nodes.type.value == 2)
		{
			Backend.DeliveryZone.prototype.selectedItemId = Backend.DeliveryZone.prototype.selectedItemId.split("_").pop();
			Backend.DeliveryZone.prototype.treeBrowser.selectItem(Backend.DeliveryZone.prototype.selectedItemId);
		}
		Backend.DeliveryZone.prototype.updateTreeBrowserNodeVisibility(
			Backend.DeliveryZone.prototype.treeBrowser.getIndexById(Backend.DeliveryZone.prototype.selectedItemId),
			this.nodes.type.value
		);
		Backend.DeliveryZone.prototype.updateBreadCrumbText();
	}
};

Backend.DeliveryZone.ShippingService = Class.create();
Backend.DeliveryZone.ShippingService.prototype =
{
	Links: {},
	Messages: {},

	Callbacks: {
		'beforeDelete': function(li)
		{
			if(confirm(Backend.DeliveryZone.ShippingService.prototype.Messages.confirmDelete))
			{
				return Backend.DeliveryZone.ShippingService.prototype.Links.remove + "/" + this.getRecordId(li);
			}
		},
		'afterDelete': function(li, response)
		{
			 try
			 {
				 response = eval('(' + response + ')');
			 }
			 catch(e)
			 {
				 return false;
			 }
		},

		beforeEdit:	 function(li)
		{
			if(this.isContainerEmpty(li, 'edit'))
			{
				return Backend.DeliveryZone.ShippingService.prototype.Links.edit + '/' + this.getRecordId(li)
			}
			else
			{
				var newServiceForm = $("shippingService_" + this.getRecordId(li, 2) + '_');
				if(newServiceForm.up().style.display == 'block')
				{
					Backend.DeliveryZone.ShippingService.prototype.getInstance(newServiceForm).hideNewForm();
				}

				Backend.DeliveryZone.ShippingService.prototype.getInstance(li.down('form'));

				this.toggleContainer(li, 'edit');
			}
		},

		afterEdit:	  function(li, response)
		{
			var newServiceForm = $("shippingService_" + this.getRecordId(li, 2) + '_');
			if(newServiceForm.up().style.display == 'block')
			{
				Backend.DeliveryZone.ShippingService.prototype.getInstance(newServiceForm).hideNewForm();
			}
			this.getContainer(li, 'edit').update(response);
			this.toggleContainer(li, 'edit');
		},

		beforeSort:	 function(li, order)
		{
			return Backend.DeliveryZone.ShippingService.prototype.Links.sortServices + '?target=' + "shippingService_servicesList_" + this.getRecordId(li, 2) + "&" + order
		},

		afterSort:	  function(li, response) { }
	},

	instances: {},

	prefix: 'shippingService_',

	initialize: function(root, service)
	{
		this.service = service;
		this.deliveryZoneId = this.service.DeliveryZone ? this.service.DeliveryZone.ID : '';
		this.findUsedNodes(root);
		this.bindEvents();
		this.rangeTypeChanged();
		this.servicesActiveList = ActiveList.prototype.getInstance(this.nodes.servicesList);

		if(this.service.ID)
		{
			Form.State.backup(this.nodes.form);

			this.nodes.root.down('.rangeTypeStatic').show();
			document.getElementsByClassName('rangeType', this.nodes.root).each(function(rangeTypeField) { rangeTypeField.hide(); }.bind(this));
		}
	},

	getInstance: function(rootNode, service)
	{
		var rootId = $(rootNode).id;
		if (!Backend.DeliveryZone.ShippingService.prototype.instances[rootId])
		{
			Backend.DeliveryZone.ShippingService.prototype.instances[rootId] = new Backend.DeliveryZone.ShippingService(rootId, service);
		}

		var instance = Backend.DeliveryZone.ShippingService.prototype.instances[rootId];
		instance.rangeTypeChanged();

		return instance;
	},

	findUsedNodes: function(root)
	{
		this.nodes = {};

		this.nodes.root = $(root);
		this.nodes.form = this.nodes.root;

		this.nodes.controls = this.nodes.root.down('.' + this.prefix + 'controls');
		this.nodes.save = this.nodes.controls.down('.' + this.prefix + 'save');
		this.nodes.cancel = this.nodes.controls.down('.' + this.prefix + 'cancel');

		this.nodes.rangeTypes = document.getElementsByClassName(this.prefix + 'rangeType', this.nodes.root);

		this.nodes.servicesList = $$('.' + this.prefix + 'servicesList_' + this.deliveryZoneId)[0];
		this.nodes.ratesList = $(this.prefix + 'ratesList_' + this.deliveryZoneId + '_' + (this.service.ID ? this.service.ID : ''));
		this.nodes.ratesNewForm = $(this.prefix + 'new_rate_' + this.deliveryZoneId + '_' + (this.service.ID ? this.service.ID : '') + '_form');

		if(!this.service.ID)
		{
			this.nodes.menu = $(this.prefix + "menu_" + this.deliveryZoneId);
			this.nodes.menuCancelLink = $(this.prefix + "new_" + this.deliveryZoneId + "_cancel");
			this.nodes.menuShowLink = $(this.prefix + "new_" + this.deliveryZoneId + "_show");
			this.nodes.menuForm = $(this.prefix + "new_service_" + this.deliveryZoneId + "_form");
		}

		this.nodes.isLocalPickup = $(this.prefix + (this.deliveryZoneId ? this.deliveryZoneId : '') + "_" + (this.service.ID ? this.service.ID : '') + "_isLocalPickup");
		this.nodes.name = this.nodes.root.down('.' + this.prefix + 'name');
	},

	bindEvents: function()
	{
	   Event.observe(this.nodes.save, 'click', function(e) { e.preventDefault(); this.save(); }.bind(this));
	   Event.observe(this.nodes.cancel, 'click', function(e) { e.preventDefault(); this.cancel(); }.bind(this));
	   if(!this.service.ID)
	   {
		   Event.observe(this.nodes.menuCancelLink, 'click', function(e) { e.preventDefault(); this.cancel(); }.bind(this));
	   }

	   $A(this.nodes.rangeTypes).each(function(radio)
	   {
		   Event.observe(radio, 'click', function(e) { this.rangeTypeChanged(e); }.bindAsEventListener(this));
	   }.bind(this));

	   $A(document.getElementsByClassName('shippingService_rateFloatValue', this.nodes.root)).each(function(input)
	   {
		   Event.observe(input, "keyup", function(e){ NumericFilter(this); });
	   }.bind(this));


		Event.observe(this.nodes.form, 'submit', function(e)
		{
			e.preventDefault();
			this.save();
		}.bind(this), false);


		Event.observe(this.nodes.isLocalPickup, "change", this.localPickupChanged.bind(this));
	},

	localPickupChanged: function()
	{
		if (this.nodes.isLocalPickup.checked  == false || !confirm(Backend.getTranslation('_create_catch_all_rate')))
		{
			return;
		}

		try {
			// WeightTable works also with subtotal rates and need to kno type.
			// hack:
			// get dzwt instance for type weight.
			var dzwt  = Backend.DeliveryZone.WeightTable.prototype.getInstance(
					$("ratesTableContainer_" + (this.deliveryZoneId ? this.deliveryZoneId : '') + "_" + (this.service.ID ? this.service.ID : '')), "weight");
			// check if  subtotal row is visible?
			if ($(dzwt.nodes.tbody).getElementsByClassName("subtotalRow")[0].visible())
			{
				// if so, can't use WeightTable instance for weight type
				dzwt  = Backend.DeliveryZone.WeightTable.prototype.getInstance(
					$("ratesTableContainer_" + (this.deliveryZoneId ? this.deliveryZoneId : '') + "_" + (this.service.ID ? this.service.ID : '')), "subtotal");
			}
			dzwt.addCatchAllRate();
			// todo:
			//     split WeightTable in 3 classes - 'AbstractBase', SubtotalTable, WeightTable
			//    remove type from WeightTable constructor (encapsulate type detection in class)
		} catch(e) {}
	},

	rangeTypeChanged: function(e)
	{
		var radio = null, dzwt;
		$A(this.nodes.rangeTypes).each(function(r){ if(r.checked) radio = r; });

		if(radio == 0)
		{
			return;
		}

		$A(this.nodes.root.getElementsByClassName(radio.value == 0 ?"weight" : "subtotal")).each(Element.show);
		// hide and clear hidden input values.
		$A(this.nodes.root.getElementsByClassName(radio.value == 0 ?"subtotal" : "weight")).each(
			function(tr)
			{
				$A($(tr).getElementsByTagName("input")).each(
					function(element)
					{
						// maybe don't clear, because it breaks added catch-all rate!

						//  element.value = "";
					}
				);
				tr.hide();
			}
		);

		dzwt  = Backend.DeliveryZone.WeightTable.prototype.getInstance(
			$("ratesTableContainer_" + (this.deliveryZoneId ? this.deliveryZoneId : '') + "_" + (this.service.ID ? this.service.ID : '')), radio.value == 0 ?"weight" : "subtotal");
		dzwt.updateAllRangeStartValues();
	},

	showNewForm: function()
	{
		var menu = new ActiveForm.Slide(this.nodes.menu);
		menu.show("shippingService_add", this.nodes.menuForm);

		var newForm = Backend.DeliveryZone.ShippingRate.prototype.getInstance(this.nodes.ratesNewForm, Backend.DeliveryZone.ShippingRate.prototype.newRate);
		newForm.showNewForm(true);
	},

	hideNewForm: function()
	{
		var menu = new ActiveForm.Slide(this.nodes.menu);
		menu.hide("shippingService_add", this.nodes.menuForm);

		$A(this.nodes.ratesList.getElementsByTagName('li')).each(function(li) {
		   Element.remove(li);
		});

		$A(this.nodes.root.getElementsByTagName('input')).each(function(input) {
			if(input.type == 'text') input.value = '';
		});
	},

	save: function()
	{
		var lastLi = this.nodes.ratesList.childElements().last();

		// Remove last rate if it is new and empty
		if(lastLi && lastLi.id.match(/new/))
		{
			var emptyValues = true;
			lastLi.getElementsBySelector("input").each(function(input)
			{
				if(parseFloat(input.value))
				{
					emptyValues = false;
					throw $break;
				}
			}.bind(this));

			if(emptyValues)
			{
				Element.remove(lastLi);
			}
		}

		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		this.nodes.form.action = this.service.ID
			? Backend.DeliveryZone.ShippingService.prototype.Links.update
			: Backend.DeliveryZone.ShippingService.prototype.Links.create;

		new LiveCart.AjaxRequest(
			this.nodes.form,
			null,
			function(response)
			{
				var response = eval("(" + response.responseText + ")");
				this.afterSave(response);
			}.bind(this)
		);
	},

	afterSave: function(response)
	{
		if(response.status == 'success')
		{
			ActiveForm.prototype.resetErrorMessages(this.nodes.form);
			if(!this.service.ID)
			{
				ratesCount = this.nodes.root.down("table").down("tr").down("td").next().down("tbody").rows[0].cells.length - 1;
				rangeTypeString = response.service.rangeType == 0 ? Backend.DeliveryZone.prototype.Messages.weightBasedRates : Backend.DeliveryZone.prototype.Messages.subtotalBasedRates;
				var li = this.servicesActiveList.addRecord(response.service.ID, '<span class="' + this.prefix + 'servicesList_title">' + this.nodes.name.value + ' ( <b class="ratesCount">' + ratesCount + '</b>' + rangeTypeString  + ' )</span>');
				this.hideNewForm();
			}
			else
			{
				Form.State.backup(this.nodes.form);
				this.nodes.root.up('li').down('.ratesCount').innerHTML = this.nodes.root.down("table").down("tr").down("td").next().down("tbody").rows[0].cells.length - 1;
				this.servicesActiveList.toggleContainer(this.nodes.root.up('li'), 'edit', 'yellow');

				if($H(response.service.newRates).size() > 0)
				{
					var regexps = {};
					$H(response.service.newRates).each(function(id) { regexps[id.value] = new RegExp(id.key); }.bind(this));

					$A(this.nodes.root.down('.activeList').getElementsByTagName('*')).each(function(elem)
					{
						if(elem.id)
						{
							$H(regexps).each(function(id) {
								if(elem.id.match(id.value))
								{
									if(elem.tagName == 'LI')
									{
										Backend.DeliveryZone.ShippingRate.prototype.instances[elem.id.replace(id.value, id.key)] = Backend.DeliveryZone.ShippingRate.prototype.instances[elem.id]
									}

									elem.id = elem.id.replace(id.value, id.key);
									return;
								}
							}.bind(this));
						}
						if(elem.name)
						{
							$H(regexps).each(function(id) {
								elem.name = elem.name.replace(id.value, id.key);
							}.bind(this));
						}
					}.bind(this));
				}
			}
		}
		else
		{
			//ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors);
			$H(response.errors).each(function(item) {
				try {
					var
						element,
						node;

					item[0] = item[0].replace("weightRangeStart", "weightRangeEnd");
					element = $(document.getElementsByName(item[0])[0]);
					if(element == null)
					{
						return;
					}
					node = document.createElement("p");
					node.className = "errorText";
					node.style.position="absolute";
					element.parentNode.appendChild(node);
					Event.observe(node, "click", function(e) {
						Event.element(e).hide();
					});
					node.innerHTML = item[1];
				} catch(e) {}
			});

		}
	},

	cancel: function()
	{
		if(!this.service.ID)
		{
			this.hideNewForm();
		}
		else
		{
			this.servicesActiveList.toggleContainerOff(this.nodes.root.up('.activeList_editContainer'));
			Form.State.restore(this.nodes.form);
		}
	}
};


Backend.DeliveryZone.ShippingRate = Class.create();
Backend.DeliveryZone.ShippingRate.prototype =
{
	Links: {},
	Messages: {},

	Callbacks: {
		'beforeDelete': function(li)
		{
			Backend.DeliveryZone.ShippingRate.prototype.getInstance(li).remove();
		},
		'afterDelete': function(li)
		{
			 try
			 {
				 response = eval('(' + response + ')');
			 }
			 catch(e)
			 {
				 return false;
			 }
		},
		'beforeEdit': function(li)
		{

		},
		'afterEdit': function(li, response)
		{

		},

		'beforeSort':	 function(li, order)
		{
			return Backend.DeliveryZone.ShippingRate.prototype.Links.sortRates + '?target=' + "shippingService_ratesList&" + order
		},

		'afterSort':	  function(li, response) { }
	},

	instances: {},

	prefix: 'shippingService_',

	newRateLastId: 0,

	initialize: function(root, rate)
	{
		this.rate = rate;
		this.deliveryZoneId = this.rate.ShippingService.DeliveryZone ? this.rate.ShippingService.DeliveryZone.ID : '';
		this.switchMetricsEnd = Backend.UnitConventer.prototype.getInstance("UnitConventer_Root_shippingService_" + (this.deliveryZoneId ? this.deliveryZoneId : '') + "_" + (rate.ShippingService.ID ? rate.ShippingService.ID : '') + "_" + (this.rate.ID ? this.rate.ID : '') + "_weightRangeEnd");
		this.switchMetricsStart = Backend.UnitConventer.prototype.getInstance("UnitConventer_Root_shippingService_" + (this.deliveryZoneId ? this.deliveryZoneId : '') + "_" + (rate.ShippingService.ID ? rate.ShippingService.ID : '') + "_" + (this.rate.ID ? this.rate.ID : '') + "_weightRangeStart");


		this.findUsedNodes(root);
		this.bindEvents();

		this.ratesActiveList = ActiveList.prototype.getInstance(this.nodes.ratesActiveList, Backend.DeliveryZone.ShippingRate.prototype.Callbacks);

		if(this.rate.ID)
		{
			this.nodes.weightRangeStart.name = 'rate_' + this.rate.ID + '_weightRangeStart';
			this.nodes.weightRangeEnd.name = 'rate_' + this.rate.ID + '_weightRangeEnd';
			this.nodes.subtotalRangeStart.name = 'rate_' + this.rate.ID + '_subtotalRangeStart';
			this.nodes.subtotalRangeEnd.name = 'rate_' + this.rate.ID + '_subtotalRangeEnd';
			this.nodes.flatCharge.name = 'rate_' + this.rate.ID + '_flatCharge';
			this.nodes.perItemCharge.name = 'rate_' + this.rate.ID + '_perItemCharge';
			this.nodes.subtotalPercentCharge.name = 'rate_' + this.rate.ID + '_subtotalPercentCharge';
			this.nodes.perKgCharge.name = 'rate_' + this.rate.ID + '_perKgCharge';

			this.nodes.perItemChargeClass.each(function(node)
			{
				var classID = node.name.match(/\[([0-9]+)\]/).pop();
				node.name = 'rate_' + this.rate.ID + '_perItemChargeClass[' + classID + ']';
			}.bind(this));
		}
	},

	getInstance: function(rootNode, rate)
	{
		var rootId = $(rootNode).id;
		if(!Backend.DeliveryZone.ShippingRate.prototype.instances[rootId])
		{
			Backend.DeliveryZone.ShippingRate.prototype.instances[rootId] = new Backend.DeliveryZone.ShippingRate(rootId, rate);
		}

		return Backend.DeliveryZone.ShippingRate.prototype.instances[rootId];
	},

	findUsedNodes: function(root)
	{
		this.nodes = {};

		this.nodes.root = $(root);

		if(!this.rate.ID)
		{
			this.nodes.menuCancelLink   = $(this.prefix + "new_rate_" + this.deliveryZoneId + '_' + this.rate.ShippingService.ID + "_cancel");
			this.nodes.menuShowLink = $(this.prefix + "new_rate_" + this.deliveryZoneId + '_' + this.rate.ShippingService.ID + "_show");
			this.nodes.menu =$(this.prefix + "rate_menu_" + this.deliveryZoneId + '_' + this.rate.ShippingService.ID);
			this.nodes.menuForm = $(this.prefix + "new_rate_" + this.deliveryZoneId + '_' + this.rate.ShippingService.ID + "_form");
		}
		this.nodes.ratesActiveList = $(this.prefix + 'ratesList_' + this.deliveryZoneId + '_' + this.rate.ShippingService.ID);

		this.nodes.weightRangeStart = this.nodes.root.down('.weightRangeStart').down('.UnitConventer_NormalizedWeight');
		this.nodes.weightRangeStartHiValue = this.nodes.root.down('.weightRangeStart').down('.UnitConventer_HiValue');
		this.nodes.weightRangeStartLoValue = this.nodes.root.down('.weightRangeStart').down('.UnitConventer_LoValue');

		this.nodes.weightRangeEnd = this.nodes.root.down('.weightRangeEnd').down('.UnitConventer_NormalizedWeight');
		this.nodes.weightRangeEndHiValue = this.nodes.root.down('.weightRangeEnd').down('.UnitConventer_HiValue');
		this.nodes.weightRangeEndLoValue = this.nodes.root.down('.weightRangeEnd').down('.UnitConventer_LoValue');


		this.nodes.subtotalRangeStart = this.nodes.root.down('.' + this.prefix + 'subtotalRangeStart');
		this.nodes.subtotalRangeEnd = this.nodes.root.down('.' + this.prefix + 'subtotalRangeEnd');
		this.nodes.flatCharge = this.nodes.root.down('.' + this.prefix + 'flatCharge');
		this.nodes.perItemCharge = this.nodes.root.down('.' + this.prefix + 'perItemCharge');
		this.nodes.perItemChargeClass = $A(this.nodes.root.getElementsBySelector('.' + this.prefix + 'perItemChargeClass'));
		this.nodes.subtotalPercentCharge = this.nodes.root.down('.' + this.prefix + 'subtotalPercentCharge');
		this.nodes.perKgCharge = this.nodes.root.down('.' + this.prefix + 'perKgCharge');
	},

	bindEvents: function()
	{
	   if(!this.rate.ID)
	   {
		   $A(this.nodes.root.getElementsByTagName('input')).each(function(input)
		   {
			   Event.observe(input, 'keypress', function(e) {
				  if(e.keyCode == 13)
				  {
					  e.preventDefault();
					  this.save(e);
				  }
			   }.bind(this), false)
		   }.bind(this));

//		   Event.observe(this.nodes.save, 'click', function(e) { e.preventDefault(); this.save(e); }.bind(this));
//		   Event.observe(this.nodes.cancel, 'click', function(e) { e.preventDefault(); this.cancel();}.bind(this));
		   Event.observe(this.nodes.menuCancelLink, 'click', function(e) { e.preventDefault(); this.cancel(); }.bind(this));
	   }

	   Event.observe(this.switchMetricsEnd.nodes.switchUnits, 'click', function(e) { this.switchMetricsStart.switchUnitTypes(); }.bind(this));
	},

	showNewForm: function(noHighLight)
	{
			var rate = {
				'weightRangeStart': this.nodes.weightRangeStart.value,
				'weightRangeEnd': this.nodes.weightRangeEnd.value,
				'subtotalRangeStart': this.nodes.subtotalRangeStart.value,
				'subtotalRangeEnd': this.nodes.subtotalRangeEnd.value,
				'flatCharge': this.nodes.flatCharge.value,
				'perItemCharge': this.nodes.perItemCharge.value,
				'subtotalPercentCharge': this.nodes.subtotalPercentCharge.value,
				'perKgCharge': this.nodes.perKgCharge.value,
				'ShippingService': this.rate.ShippingService,
				'ID': 'new' + Backend.DeliveryZone.ShippingRate.prototype.newRateLastId
			};

			this.afterAdd({validation: 'success'}, rate, noHighLight);
	},

	hideNewForm: function()
	{
		var manu = new ActiveForm.Slide(this.nodes.menu);
		manu.hide("addNewRate", this.nodes.menuForm);
	},

	afterAdd: function(response, rate, noHighLight)
	{
		if(response.validation == 'success')
		{
			var newId = Backend.DeliveryZone.ShippingRate.prototype.newRateLastId;

			var li = this.ratesActiveList.addRecord('new' + newId, this.nodes.root, false);

			var idStart = this.prefix + this.deliveryZoneId + '_' + this.rate.ShippingService.ID + "_";
			var idStartRegexp = new RegExp(idStart);
			$A(document.getElementsByClassName(this.prefix + 'rateFloatValue', li)).each(function(input) {
				Event.observe(input, "keyup", function(e){ NumericFilter(this) });
				input.id = input.id.replace(idStartRegexp, idStart + 'new' + newId);
				Event.observe(input.up('fieldset.error').down('label'), 'click', function(e) { e.preventDefault(); input.focus(); });
			}.bind(this));

			document.getElementsByClassName('UnitConventer_Root', li).each(function(el) {
				el.id = el.id.replace(idStartRegexp, idStart + 'new' + newId);
			}.bind(this));

			var startHi = li.down('.weightRangeStart').down('.UnitConventer_HiValue');
			var startLo = li.down('.weightRangeStart').down('.UnitConventer_LoValue');
			var endHi = li.down('.weightRangeEnd').down('.UnitConventer_HiValue');
			var endLo = li.down('.weightRangeEnd').down('.UnitConventer_LoValue');

			startHi.id = startHi.id.replace(idStartRegexp, idStart + 'new' + newId);
			Event.observe(startHi.up('.shippingService_weightRange').down('label'), 'click', function(e) { e.preventDefault(); startHi.focus(); });
			startLo.id = startLo.id.replace(idStartRegexp, idStart + 'new' + newId);

			endHi.id = endHi.id.replace(idStartRegexp, idStart + 'new' + newId);
			endLo.id = endLo.id.replace(idStartRegexp, idStart + 'new' + newId);

			var newForm = Backend.DeliveryZone.ShippingRate.prototype.getInstance(li, rate);

			this.nodes.weightRangeStart.value = '0';
			this.nodes.weightRangeStartHiValue.value = '0';
			this.nodes.weightRangeStartLoValue.value = '0';

			this.nodes.weightRangeEnd.value = '0';
			this.nodes.weightRangeEndHiValue.value = '0';
			this.nodes.weightRangeEndLoValue.value = '0';

			this.nodes.subtotalRangeStart.value = '0';
			this.nodes.subtotalRangeEnd.value = '0';
			this.nodes.flatCharge.value = '0';
			this.nodes.perItemCharge.value = '0';
			this.nodes.subtotalPercentCharge.value = '0';
			this.nodes.perKgCharge.value = '0';

			this.nodes.perItemChargeClass.each(function(node)
			{
				node.value = '';
			}.bind(this));

			Backend.DeliveryZone.ShippingRate.prototype.newRateLastId++;

			if (!noHighLight)
			{
				this.ratesActiveList.highlight(li);
			}
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.root.up('form'), response.errors);
		}
	},

	cancel: function()
	{
		if(!this.rate.ID)
		{
			this.hideNewForm();
		}
	},

	remove: function()
	{
		if(confirm(Backend.DeliveryZone.ShippingRate.prototype.Messages.confirmDelete))
		{
			if(!this.rate.ID.match(/^new/))
			{
				new LiveCart.AjaxRequest(Backend.DeliveryZone.ShippingService.prototype.Links.deleteRate + '/' + this.rate.ID);
			}

			var rates = this.nodes.root.up(".activeList");
			var service = this.nodes.root.up('li');

			Element.remove(this.nodes.root);

			if (service)
			{
				service.down('.ratesCount').innerHTML = rates.getElementsByTagName('li').length;
			}
		}
	}
};

Backend.DeliveryZone.lookupAddress = function(form, e)
{
	e.preventDefault();
	new LiveCart.AjaxRequest(form, null, function(req)
	{
		var zone = req.responseData;
		var cont = $('zoneLookupResult').down('span');
		if (zone.ID)
		{
			cont.update(zone.name);
		}
		else
		{
			cont.update(Backend.getTranslation('_default_zone'));
		}

		cont.parentNode.show();
		jQuery(cont).effect('highlight');
	});
};

Backend.DeliveryZone.WeightUnitConventer = Class.create();
Backend.DeliveryZone.WeightUnitConventer.prototype = {
	initialize : function(root)
	{
		this.nodes = {};
		this.nodes.root = $(root);
	}
};

Backend.DeliveryZone.WeightTable = Class.create();
Backend.DeliveryZone.WeightTable.prototype = {
	newColumnCount : 0,

	getInstance: function(root, typeName, canChangeTypeName)
	{
		var id = root.id + "_"+typeName;
		if (!Backend.deliveryZoneWeightTableInstances)
		{
			Backend.deliveryZoneWeightTableInstances = [];
		}
		if (!Backend.deliveryZoneWeightTableInstances[id])
		{
			Backend.deliveryZoneWeightTableInstances[id] = new Backend.DeliveryZone.WeightTable(root, typeName, canChangeTypeName);
		}
		return Backend.deliveryZoneWeightTableInstances[id];
	},

	initialize : function(root, typeName, canChangeTypeName)
	{
		var
			m, cn, w;
		this.canChangeTypeName = canChangeTypeName;
		this.nodes = {};
		this.nodes.root = $(root);
		this.nodes.tbody = this.nodes.root.down("tbody");
		this.nodes.unitsTypeField = $A(this.nodes.root.up("table").down("table").getElementsByClassName("UnitConventer_UnitsType")).shift();
		this.nodes.unitsName = $A(this.nodes.root.up("table").down("table").getElementsByClassName("UnitConventer_UnitsName")).shift();
		this.nodes.switchUnits = $A(this.nodes.root.up("table").down("table").getElementsByClassName("UnitConventer_SwitchToUnits")).shift();
		this.nodes.ratesTableContainerScroll = this.nodes.root.up("div");
		window.setTimeout(function() {
			w = $$(".sectionContainer").inject(0,
				function(m, node)
				{
					var w = node.getWidth();
					if(w > m)
					{
						m = w;
					}
					return m;
				}
			);
			if(w > 0)
			{
				this.scroll.style.width = (w-230) + "px";
			}
		}.bind({scroll:this.nodes.ratesTableContainerScroll}), 1000);
		this.labels = {};
		$A($("RateInputTableLabels").getElementsByTagName("span")).each(
			function(node)
			{
				this.labels[node.className] = node.innerHTML;
			}.bind(this)
		);

		Event.observe(this.nodes.root, "change", this.onChange.bindAsEventListener(this));
		Event.observe(this.nodes.root, "keyup", this.addColumnOnKeyUp.bindAsEventListener(this));

		this.setType(typeName);
		this.observeAndInitWeightRow();

		this.attachNumericFilters();
		if (false == $(this.nodes.tbody.getElementsByClassName("weightRow")[0]).visible())
		{
			this.showInWeightUnits();
			Event.observe(this.nodes.switchUnits, "click", this.switchUnitTypes.bindAsEventListener(this));
		}
		this.updateAllRangeStartValues();
	},

	updateAllRangeStartValues: function()
	{
		$A(this.nodes.root.getElementsBySelector("tr." + this.typeName + "Row div.rangeStart")).each(
			function(node)
			{
				this.updateRangeStartValue(node);
			}.bind(this)
		);
	},

	updateRangeStartValue: function(node /* parent of cell where to update start value (!not from where) */)
	{
		node = $(node);
		if (node.hasClassName("rangeStart") == false)
		{
			node = node.getElementsByClassName("rangeStart");
			if (node.length == 0)
			{
				return false; // unsupported node.
			}
			node = node[0];
		}

		var
			inputs, merged, previousTd,
			valueNode,
			nodes = node.getElementsBySelector(".rangeStartValue");
		if (nodes.length != 1)
		{
			return false; // can't process this cell
		}
		valueNode = nodes[0];

		switch (valueNode.tagName.toLowerCase())
		{
			case "input":
				return true; // first range, cant copy anything from previous range
				break;

			case "span":
			try {
				// up to td, previous td, down to merged value
				previousTd = node.up("td").previous();
				if (this.typeName == "weight")
				{
					inputs = this._getWeightCellInputs(previousTd);
					merged = inputs.merged;
				}
				else
				{
					merged = $A(previousTd.getElementsByTagName("input")).find(function(n) {
						// first cell has 2 input tags, get that one who contain (merged) value, unfortunately that input has no special className, but first cells extra input has.
						return !$(n).hasClassName("rangeStartValue");
					});
				}
				valueNode.innerHTML = new String(merged.value);
				break;
			} catch(e) {}
		}

	},

	setType: function(typeName)
	{
		$A(this.nodes.root.getElementsByClassName(typeName == "weight" ? "weight" : "subtotal")).each(Element.show);
		$A(this.nodes.root.getElementsByClassName(typeName == "weight" ? "subtotal" : "weight")).each(Element.hide);

		this.typeName = typeName;
	},

	getType: function()
	{
		if (this.canChangeTypeName) // only for new form
		{
			if ($("shippingService___weight").checked)
			{
				this.typeName = 'weight';
			}
			if ($("shippingService___subtotal").checked)
			{
				this.typeName = 'subtotal';
			}
		}

		return this.typeName;
	},

	addColumnOnKeyUp : function(event) // add new column
	{
		var
			node,
			lastRowIndex,
			i,
			input, inputs;
		node = Event.element(event);
		if(null == node || node.tagName.toLowerCase() != "input")
		{
			return;
		}
		lastRowIndex = this.nodes.tbody.rows[0].cells.length - 1;
		for(i=0; i < this.nodes.tbody.rows.length; i++)
		{
			inputs = $A($(this.nodes.tbody.rows[i].cells[lastRowIndex]).getElementsByTagName("input"));
			while(input = inputs.shift())
			{
				if(input == null)
				{
					continue;
				}

				if ($(input.parentNode).hasClassName("rangeStart"))
				{
					prefix = "rangeStart_";
				}
				else
				{
					prefix = "";
				}
				if(
					input.value != ""
					&&
					(
						// it is focused hi or lo value field
						input.hasClassName(prefix + "focusOnHiOrLo")
							||
						// or it is not hi or lo field (hidden hi or lo value fields can have value '0', forcing to add new column.
						(false == input.hasClassName("UnitConventer_LoValue") && false == input.hasClassName("UnitConventer_HiValue"))
					)
				)
				{
					this.addColumn();
					return;
				}
			}
		}
	},

	onBlurHiOrLo: function(event)
	{
		var
			prefix = "",
			node;
		node = Event.element(event);
		if (null == node || node.tagName.toLowerCase() != "input")
		{
			return;
		}
		if ($(node.parentNode).hasClassName("rangeStart"))
		{
			prefix = "rangeStart_";
		}
		node.removeClassName(prefix+"focusOnHiOrLo");

		// cant be sure about order for fireing blur and focus events, therefore use timer.
		window.setTimeout(function() {
			if($A($(this.node).up("td").getElementsByClassName(this.prefix +"focusOnHiOrLo")).length == 0)
			{
				var
					inputs = this.instance._getWeightCellInputs(this.node);
				inputs[prefix + "hi"].hide();
				inputs[prefix + "hiAbbr"].hide();
				inputs[prefix + "lo"].hide();
				inputs[prefix + "loAbbr"].hide();
				inputs[prefix + "merged"].show();
			}
		}.bind({node:node, instance:this, prefix: prefix}), 250);
	},

	_getWeightCellInputs: function(node)
	{
		node = $(node);
		if (!node)
		{
			return null;
		}
		if(node.tagName.toLowerCase() != "td")
		{
			node = node.up("td");
		}
		r = $A(node.getElementsByTagName("input")).concat($A(node.getElementsByTagName("span"))).inject({},
			function(result, node)
			{
				var prefix = "";
				node = $(node);
				if ($(node.parentNode).hasClassName("rangeStart"))
				{
					prefix = "rangeStart_";
				}
				if (node.hasClassName('rangeStartValue'))
				{
					result['rangeStartValue'] = node;
					if (node.tagName.toLowerCase() == "input") // can be span, if not editable range start.
					{
						result['rangeStart_normalized'] = node;
					}
				} else if (node.hasClassName('UnitConventer_NormalizedWeight')) {
					result[prefix + 'normalized'] = node;
				} else if (node.hasClassName('UnitConventer_HiValue')) {
					result[prefix + 'hi'] = node;
				} else if (node.hasClassName('UnitConventer_HiValueAbbr')) {
					result[prefix + 'hiAbbr'] = node;
				} else if (node.hasClassName('UnitConventer_LoValue')) {
					result[prefix + 'lo'] = node;
				} else if (node.hasClassName('UnitConventer_LoValueAbbr')) {
					result[prefix + 'loAbbr'] = node;
				} else if (node.hasClassName('UnitConventer_MergedValue')) {
					result[prefix + 'merged'] = node;
				}
				return result;
			}
		);
		return r;
	},

	onMergedWeightFocus : function(event)
	{
		var
			node,
			inputs,
			prefix="";
		node = Event.element(event);
		if (null == node || node.tagName.toLowerCase() != "input")
		{
			return;
		}
		if ($(node.parentNode).hasClassName("rangeStart"))
		{
			prefix = "rangeStart_";
		}
		// split to 2 inputs
		inputs = this._getWeightCellInputs(node);

		inputs[prefix + "hi"].show();
		inputs[prefix + "hiAbbr"].show();
		inputs[prefix + "hi"].focus();
		inputs[prefix + "lo"].show();
		inputs[prefix + "loAbbr"].show();

		// hints
		var unitName = this.nodes.unitsTypeField.value=="METRIC" ? "Metric" : "English";
		inputs[prefix + "hi"].title=this.labels["UnitConventer_"+unitName+"HiUnit"];
		inputs[prefix + "lo"].title=this.labels["UnitConventer_"+unitName+"LoUnit"];
		inputs[prefix + "hiAbbr"].innerHTML=this.labels["UnitConventer_"+unitName+"HiUnitAbbr"];
		inputs[prefix + "loAbbr"].innerHTML=this.labels["UnitConventer_"+unitName+"LoUnitAbbr"];

		inputs[prefix + "merged"].hide();
	},

	registerFocusOnHiOrLo : function(event)
	{
		var
			node, prefix="";
		node = Event.element(event);
		if (null == node || node.tagName.toLowerCase() != "input")
		{
			return;
		}
		if ($(node.parentNode).hasClassName("rangeStart"))
		{
			prefix = "rangeStart_";
		}
		$(node).addClassName(prefix + "focusOnHiOrLo");
	},

	onChange : function(event)
	{
		var
			c, r,
			node,
			i,j,
			input,
			ec;
		node = Event.element(event);

		if(null == node || node.tagName.toLowerCase() != "input")
		{
			return;
		}

		// weight related
		if(node.hasClassName("UnitConventer_HiValue") || node.hasClassName("UnitConventer_LoValue"))
		{
			this.updateNormalizedWeightFieldValue(node.up("td"));
			this.updateMergedWeightFieldValue(node.up("td"));
		}

		// remove empty columns
		for (j=0; j < this.nodes.tbody.rows[0].cells.length - 1; j++)
		{
			ec = [];
			for(i=0; i < this.nodes.tbody.rows.length; i++)
			{
				input = $(this.nodes.tbody.rows[i].cells[j]).getElementsByTagName("input");

				// has no input with value.
				if(!$A(input).find(function(n) { return n.value != ""; }))
				{
					ec.push(input[0]);
				}
			}

			if (ec.length == this.nodes.tbody.rows.length)
			{
				while(input = ec.shift())
				{
					z = input.up("td");
					z.parentNode.removeChild(z);
				}
			}
		}

		// sort columns
		data = $A();
		sortRow = null;

		// read
		for (c = 0; c < this.nodes.tbody.rows[0].cells.length - 1; c++)
		{
			data[c] = [];
			for (r = 0; r < this.nodes.tbody.rows.length; r++)
			{
				value = null;
				inputsValues = null;
				cell = $(this.nodes.tbody.rows[r].cells[c]);
				if (r == 0 && this.getType() == 'weight')
				{
					inputs = this._getWeightCellInputs(cell);
					inputsValues = {
						normalized:inputs.normalized.value,
						merged:inputs.merged.value,
						hi:inputs.hi.value,
						lo:inputs.lo.value
					};
					value = inputs.normalized.value;
					sortRow = 0;
				}
				else if (r == 1 && this.getType() == 'subtotal')
				{
					nodes = $A(cell.getElementsByTagName("input"));
					value = nodes.pop().value; // last one, because can contain also rangeStart input.
					sortRow = 1;
				}
				else // price
				{
					value = cell.down("input").value;
				}
				// data[c][r] = value;
				data[c][r] = {value:value, inputsValues:inputsValues};
			}
		}
		// sort
		data.sort(function(sortRow, a, b) {
			try {
				return a[sortRow].value - b[sortRow].value;
			} catch(e)
			{
				return 0;
			}
		}.bind(this, sortRow));

		// write back
		data.each(function(col, c) {
			var mapping, q, cell, nodes, cellInputs;

			$A(col).each(function(c, item, r)
			{
				cell = this.nodes.tbody.rows[r].cells[c];
				cellInputs = this._getWeightCellInputs(cell);
				if (r == 0 && this.getType() == 'weight')
				{
					mapping = ["normalized", "merged", "lo", "hi"];
					for (q = 0; q < mapping.length; q++)
					{
						cellInputs[mapping[q]].value=item.inputsValues[mapping[q]];
					}
				}
				else if (r == 1 && this.getType() == 'subtotal')
				{
					// ??
					nodes = $A(cell.getElementsByTagName("input"));
					nodes.pop().value = item.value;
				}
				else
				{
					cell.down("input").value = item.value;
				}
			}.bind(this, c))
		}.bind(this));
		this.updateAllRangeStartValues(); // can't know if after sorting current node was moved forward or backward.
	},

	addColumn: function()
	{
		// todo: use templates.
		var
			rowCount = this.nodes.tbody.rows.length,
			i,
			node, node2, node3,
			name,
			m,
			pair;

		this.newColumnCount++;
		for(i=0; i<rowCount; i++)
		{
			node = document.createElement('td');
			node2 = document.createElement('input');
			this.nodes.tbody.rows[i].appendChild(node);
			node = $(node);
			if (node.up("tr").hasClassName("weightRow") || node.up("tr").hasClassName("subtotalRow")) // header rows
			{
				// ~ structure for range start value
				node.innerHTML='<div class="rangeStart"><span class="rangeStartValue"></span> -</div>';
				// ~
			}
			node.appendChild(node2);
			node2 = $(node2);
			node2.addClassName("number");
			if(node2.up("tr").hasClassName("weightRow"))
			{
				node2.hide();
				// weight input needs 4  input fields - one for sending to server, and 3 for input
				node2.addClassName("UnitConventer_NormalizedWeight");
				m = [
						// [<tag name>, <class name>]
						["input", "number UnitConventer_HiValue"],
						["span", "UnitConventer_HiValueAbbr"],
						["input", "number UnitConventer_LoValue"],
						["span", "UnitConventer_LoValueAbbr"],
						["input", "number UnitConventer_MergedValue"]
					];
				while(pair = m.shift())
				{
					node3 = document.createElement(pair[0]);
					node.appendChild(node3);
					node3.hide();
					node3.addClassName(pair[1]);
				}
			}

			name = $A($(node).previous().getElementsByTagName("input")).find( // don't use inputs from "rangeStart" container for copying input name.
				function(input)
				{
					return $(input.parentNode).hasClassName("rangeStart") == false;
				}
			).name;

			chunks = name.split("_");
			chunks[1] = "new"+this.newColumnCount;
			node2.name = chunks.join("_");
		}
		this.observeAndInitWeightRow();
		this.attachNumericFilters();
	},

	observeAndInitWeightRow : function()
	{
		$A(this.nodes.root.getElementsByClassName("UnitConventer_NormalizedWeight")).each(
			function(node)
			{
				var prefix = "";
				node = $(node);
				if(false == node.hasClassName("observed"))
				{
					var inputs;
					if(node.up("tr").hasClassName("weightRow") == false)
					{
						return; // continue
					}
					inputs = this._getWeightCellInputs(node);
					if (node.hasClassName("rangeStartValue"))
					{
						prefix = "rangeStart_";
					}
					if (inputs[prefix+"hi"] == null || inputs[prefix+"lo"] == null || inputs[prefix+"merged"] == null)
					{
						return;
					}

					Event.observe(inputs[prefix + "merged"], "focus", this.onMergedWeightFocus.bindAsEventListener(this));
					Event.observe(inputs[prefix + "hi"], "blur", this.onBlurHiOrLo.bindAsEventListener(this));
					Event.observe(inputs[prefix + "lo"], "blur", this.onBlurHiOrLo.bindAsEventListener(this));
					Event.observe(inputs[prefix + "hi"], "focus", this.registerFocusOnHiOrLo.bindAsEventListener(this));
					Event.observe(inputs[prefix + "lo"], "focus", this.registerFocusOnHiOrLo.bindAsEventListener(this));
					node.addClassName("observed");
					node.hide();
					inputs[prefix + "lo"].hide();
					if (inputs[prefix + "loAbbr"])
					{
						inputs[prefix + "loAbbr"].hide();
					}
					inputs[prefix + "hi"].hide();
					if (inputs[prefix + "hiAbbr"])
					{
						inputs[prefix + "hiAbbr"].hide();
					}
					inputs[prefix + "merged"].show();
				}
			}.bind(this)
		);
	},

	attachNumericFilters : function()
	{
		$A(this.nodes.root.getElementsByClassName("number")).each(
			function(node)
			{
				node = $(node);
				if(node.hasClassName("hasNumericFilter") == false)
				{
					Event.observe(node, "keyup", function(e){ NumericFilter(this) });
					node.addClassName("hasNumericFilter");
				}
			});
	},

	_convertToHiAndLoWeightUnits : function(normalizedValue)
	{
		var
			multipliers = this.getWeightMultipliers(this.nodes.unitsTypeField.value),
			hiValue,
			loValue,
			precision = 'ENGLISH' == this.nodes.unitsTypeField.value ? 10 : 1;

		hiValue = Math.floor(normalizedValue / multipliers[0]);
		loValue = (normalizedValue - (hiValue * multipliers[0])) / multipliers[1];
		// allow to enter one decimal number for ounces
		loValue = Math.round(loValue * precision) / precision;
		if ('ENGLISH' == this.nodes.unitsTypeField.value)
		{
			loValue = loValue.toFixed(0);
		}
		return [hiValue, loValue];
	},

	updateMergedWeightFieldValue: function(cell)
	{
		var
			inputs = this._getWeightCellInputs(cell),
			values,
			multipliers = this.getWeightMultipliers(this.nodes.unitsTypeField.value);

		value = Math.round((inputs.normalized.value / multipliers[0]) * 1000) / 1000;
		if(this.nodes.unitsTypeField.value == "METRIC")
		{
			inputs.merged.value = value.toFixed(3);
		}
		else
		{
			inputs.merged.value = value;
		}

		if (inputs.rangeStart_normalized)
		{
			value = Math.round((inputs.rangeStart_normalized.value / multipliers[0]) * 1000) / 1000;
			if(this.nodes.unitsTypeField.value == "METRIC")
			{
				inputs.rangeStart_merged.value = value.toFixed(3);
			}
			else
			{
				inputs.rangeStart_merged.value = value;
			}
		}
	},

	updateNormalizedWeightFieldValue : function(cell)
	{
		var
			inputs = this._getWeightCellInputs(cell),
			multipliers = this.getWeightMultipliers(this.nodes.unitsTypeField.value);
		inputs.normalized.value = (inputs.hi.value * multipliers[0]) + (inputs.lo.value * multipliers[1]);

		if (inputs.rangeStart_normalized)
		{
			inputs.rangeStart_normalized.value = (inputs.rangeStart_hi.value * multipliers[0]) + (inputs.rangeStart_lo.value * multipliers[1]);
		}
	},

	switchUnitTypes : function()
	{
		this.nodes.unitsTypeField.value = this.nodes.unitsTypeField.value == "METRIC" ? "ENGLISH" : "METRIC";
		this.showInWeightUnits();
		this.updateAllRangeStartValues();
	},

	showInWeightUnits : function()
	{
		if(this.nodes.unitsTypeField.value == "METRIC")
		{
			this.nodes.unitsName.innerHTML = this.labels.UnitConventer_MetricHiUnit;
			this.nodes.switchUnits.innerHTML = this.labels.UnitConventer_SwitchToEnglishTitle;
		}
		else if(this.nodes.unitsTypeField.value == "ENGLISH")
		{
			this.nodes.unitsName.innerHTML = this.labels.UnitConventer_EnglishHiUnit;
			this.nodes.switchUnits.innerHTML = this.labels.UnitConventer_SwitchToMetricTitle;
		}
		var multipliers = this.getWeightMultipliers(this.nodes.unitsTypeField.value);

		var i, input, cell;
		for(i = 0; i<this.nodes.tbody.rows[0].cells.length - 1; i++)
		{
			cell = $(this.nodes.tbody.rows[0].cells[i]);

			try {
				var inputs = this._getWeightCellInputs(cell);
				values = this._convertToHiAndLoWeightUnits(inputs.normalized.value);
				inputs.hi.value = values[0];
				inputs.lo.value = values[1];

				if (inputs.rangeStart_normalized)
				{
					values = this._convertToHiAndLoWeightUnits(inputs.rangeStart_normalized.value);
					inputs.rangeStart_hi.value = values[0];
					inputs.rangeStart_lo.value = values[1];
				}
				this.updateMergedWeightFieldValue(cell);

			} catch(e) { }

		}
	},

	getWeightMultipliers: function(type)
	{
		switch(type)
		{
			case 'ENGLISH':
				return [0.45359237, 0.0283495231];

			case 'METRIC':
			default:
				return [1, 0.001];
		}
	},

	addCatchAllRate: function()
	{
		// clear?;
		$A(this.nodes.tbody.rows).each(
			function(row)
			{
				var
					node,
					cell,
					inputs;
				row = $(row);
				while (row.cells.length > 1)
				{
					$(row.cells[1]).remove();
				}
				cell = $(row.cells[0]);
				if (row.hasClassName("weightRow"))
				{
					inputs = this._getWeightCellInputs(cell);
					inputs.rangeStart_normalized.value = 0;
					inputs.normalized.value = 999999;
					this.updateMergedWeightFieldValue(cell);
				}
				else if(row.hasClassName("subtotalRow"))
				{
					node = cell.down(".rangeStartValue");
					node.value = 0;
					document.getElementsByName(node.name.replace("RangeStart", "RangeEnd"))[0].value = 999999;
				}
				else
				{
					row.cells[0].down("input").value = 0;
				}
			}.bind(this)
		);
		this.addColumn();
		this.updateAllRangeStartValues();
		if (this.getType() == "weight")
		{

			this.showInWeightUnits();
		}
	}
};
