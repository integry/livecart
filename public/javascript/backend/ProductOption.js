/**
 * Backend.ProductOption
 * @author   Integry Systems
 * @namespace Backend.ProductOption
 */
if (Backend == undefined)
{
	var Backend = {}
}

Backend.ProductOption = Class.create();
Backend.ProductOption.prototype =
{
	TYPE_BOOL: 0,
	TYPE_SELECT: 1,
	TYPE_TEXT: 2,
	TYPE_FILE: 3,

	DISPLAYTYPE_COLOR: 2,

	cssPrefix: "productOption_",

	countNewValues: 0,

	callbacks: {
		beforeEdit:	 function(li) {
			Backend.ProductOption.prototype.hideNewProductOptionAction(this.getRecordId(li, 3));

			if(this.isContainerEmpty(li, 'edit')) return Backend.ProductOption.prototype.links.editField + this.getRecordId(li)
			else this.toggleContainer(li, 'edit');
		},
		afterEdit:	  function(li, response) {
			var productOption = eval("(" + response + ")" );
			productOption.rootId = li.id;
			new Backend.ProductOption(productOption, true);
			this.createSortable(true);
			this.toggleContainer(li, 'edit');
		},
		beforeDelete:   function(li) {
			if(confirm(Backend.ProductOption.prototype.msg.removeFieldQuestion))
			return Backend.ProductOption.prototype.links.deleteField + this.getRecordId(li)
		},

		afterDelete:	function(li, response)
		{
			try
			{
				response = eval('(' + response + ')');
			}
			catch(e)
			{
				return false;
			}

			if(response.status == 'success')
			{
				var id = this.getRecordId(li, 3);
				if (id.substr(0, 1) == 'c')
				{
					CategoryTabControl.prototype.resetTabItemsCount(id.substr(1));
				}
				else
				{

				}

				return true;
			}

			return false;
		},

		beforeSort:	 function(li, order)
		{
			return Backend.ProductOption.prototype.links.sortField + "?target=" + this.ul.id + "&" + order
		},

		afterSort:	 function(li, order) {	}
	},

	/**
	 * Constructor
	 *
	 * @param productOptionsJson Spec Field values
	 * @param hash If true the passed productOption is an object. If hash is not passed or false then productOptionJson will be parsed as json string
	 *
	 * @access public
	 *
	 */
	initialize: function(productOptionJson, hash)
	{
		this.productOption = !hash ? eval("(" + productOptionJson + ")" ) : productOptionJson;
		this.cloneForm('productOption_item_blank');

		this.id					= this.productOption.ID;
		this.parentID			= this.productOption.parentID;
		this.rootId				= this.productOption.rootId;

		if (this.parentID.substr && (this.parentID.substr(0, 1) == 'c'))
		{
			this.categoryID = this.parentID.substr(1);
		}

		this.type				= this.productOption.type;
		this.values				= this.productOption.values;
		this.name				= this.productOption.name_lang;
		this.description		= this.productOption.description_lang;
		this.backupName			= this.name;

		['isRequired', 'isDisplayed', 'isDisplayedInList', 'isDisplayedInCart', 'isPriceIncluded'].each(
		function(key)
		{
			this[key] = this.productOption[key] == 1;
		}.bind(this));

		this.loadLanguagesAction();

		this.findUsedNodes();

		this.bindFields();

		this.nodes.type.value = this.productOption.type;
		this.typeWasChangedAction();
	},

	/**
	 * This function destroys the old spec field form, then clones the prototype and then calls constructor once again
	 *
	 * @param productOptions Spec Field values
	 * @param hash If true the passed productOption is an object. If hash is not passed or false then productOptionJson will be parsed as json string
	 *
	 * @access public
	 *
	 */
	recreate: function(productOptionJson, hash)
	{
		var root = ($(this.productOption.rootId).tagName.toLowerCase() == 'li') ? ActiveList.prototype.getInstance("productOption_items_list_" + this.parentID).getContainer($(this.productOption.rootId), 'edit') : $(this.productOption.rootId);
		$A(this.fieldsList.ul.getElementsByTagName('li')).each(function(li)
		{
		   if(!Element.hasClassName(li, 'dom_template'))
		   {
			   this.deleteValueFieldAction(li);
		   }
		}.bind(this));

		this.addField(null, "new" + Backend.ProductOption.prototype.countNewValues, false);
		this.fieldsList.touch(true);

		this.bindDefaultFields();
		this.nodes.type.value = 3;
		this.typeWasChangedAction();

		$('productOption_step_lev1_productOption_step_main_' + this.parentID + '_new').show();
		$('productOption_step_lev1_productOption_step_values_' + this.parentID + '_new').hide();

		Backend.ProductOption.prototype.countNewValues++;

		Form.restore(this.nodes.form, ['type']);
	},


	/**
	 * Instead of sending spec field form we store form prototype which is cloned every time new spec field data is being recieved.
	 *
	 * @param prototypeId Id of root prototype element
	 * @param rootId Id of root element where the copy of prototype will be copied
	 *
	 * @access private
	 */
	cloneForm: function(prototypeId)
	{
		var root = ($(this.productOption.rootId).tagName.toLowerCase() == 'li') ?  ActiveList.prototype.getInstance(this.productOption.rootId).getContainer($(this.productOption.rootId), 'edit') : $(this.productOption.rootId);

		var blankForm = $(prototypeId);
		var copiedForm = blankForm.cloneNode(true);
		Element.removeClassName(copiedForm, 'dom_template');
		copiedForm.id = false;
		root.appendChild(copiedForm);

		new Backend.LanguageForm(copiedForm);
	},


	/**
	 * Find ussed nodes
	 *
	 * @access private
	 *
	 */
	findUsedNodes: function()
	{
		this.nodes = [];
		this.nodes.parent = $(this.rootId);

		this.nodes.form 				= this.nodes.parent.getElementsByTagName("form")[0];
		this.nodes.tabsContainer	   = this.nodes.parent.down('.tabs');
		this.nodes.type = document.getElementsByClassName(this.cssPrefix + "form_type", this.nodes.parent)[0];
		this.nodes.displayType = document.getElementsByClassName(this.cssPrefix + "form_displayType", this.nodes.parent)[0];

		this.nodes.stateLinks 			= document.getElementsByClassName(this.cssPrefix + "change_state", this.nodes.parent);
		this.nodes.stepTranslations 	= document.getElementsByClassName(this.cssPrefix + "step_translations", this.nodes.parent)[0];
		this.nodes.stepMain 			= document.getElementsByClassName(this.cssPrefix + "step_main", this.nodes.parent)[0];
		this.nodes.stepValues	   	= document.getElementsByClassName(this.cssPrefix + "step_values", this.nodes.parent)[0];

		this.nodes.stepLevOne 			= document.getElementsByClassName(this.cssPrefix + "step_lev1", this.nodes.parent);

		for(var i = 0; i < this.nodes.stepLevOne.length; i++)
		{
			if(!this.nodes.stepLevOne[i].id) this.nodes.stepLevOne[i].id = this.nodes.stepLevOne[i].className.replace(/ /, "_") + "_" + this.id;
		}

		var self = this;
		this.nodes.labels = {};
		$A(['type', 'name', 'fileExtensions', 'maxFileSize', 'isRequired', 'isDisplayed', 'isDisplayedInList', 'isDisplayedInCart', 'isPriceIncluded']).each(function(field)
		{
			this.nodes.labels[field] = document.getElementsByClassName(self.cssPrefix + "form_" + field + "_label", this.nodes.parent)[0];
		}.bind(this));

		this.nodes.id 					= document.getElementsByClassName(this.cssPrefix + "form_id", this.nodes.parent)[0];
		this.nodes.parentID 			= document.getElementsByClassName(this.cssPrefix + "form_parentID", this.nodes.parent)[0];

		['isRequired', 'isDisplayed', 'isDisplayedInList', 'isDisplayedInCart', 'isPriceIncluded'].each(
		function(key)
		{
			this.nodes[key] = document.getElementsByClassName(this.cssPrefix + "form_" + key, this.nodes.parent)[0];
		}.bind(this));

		this.nodes.name 				= document.getElementsByClassName(this.cssPrefix + "form_name", this.nodes.parent)[0];
		this.nodes.description			= document.getElementsByClassName(this.cssPrefix + "form_description", this.nodes.parent)[0];
		this.nodes.maxFileSize		= document.getElementsByClassName(this.cssPrefix + "form_maxFileSize", this.nodes.parent)[0];
		this.nodes.fileExtensions		= document.getElementsByClassName(this.cssPrefix + "form_fileExtensions", this.nodes.parent)[0];
		this.nodes.price 				= document.getElementsByClassName(this.cssPrefix + "form_priceDiff", this.nodes.parent)[0];
		this.nodes.optionPriceContainer = document.getElementsByClassName("optionPriceContainer", this.nodes.parent)[0];
		this.nodes.optionSelectMessage = document.getElementsByClassName("optionSelectMessage", this.nodes.parent)[0];
		this.nodes.optionFile = document.getElementsByClassName("optionFile", this.nodes.parent)[0];

		this.nodes.valuesDefaultGroup 	= document.getElementsByClassName(this.cssPrefix + "form_values_group", this.nodes.parent)[0];

		this.nodes.controls 			= this.nodes.parent.down("." + this.cssPrefix + "controls");
		this.nodes.cancel 				= this.nodes.controls.down("." + this.cssPrefix + "cancel");
		this.nodes.save 				= this.nodes.controls.down("." + this.cssPrefix + "save");

		this.nodes.cancelLink		  = $("productOption_item_new_" + this.parentID + "_cancel");

		this.nodes.translationsLinks 	= document.getElementsByClassName(this.cssPrefix + "form_values_translations_language_links", this.nodes.parent)[0];
		this.nodes.valuesAddFieldLink 	= this.nodes.parent.down("." + this.cssPrefix + "add_field");

		this.nodes.valuesTranslationsDiv = this.nodes.stepValues.down("." + this.cssPrefix + "step_values_translations");

		var ul = this.nodes.valuesDefaultGroup.getElementsByTagName('ul')[0];
		ul.id = this.cssPrefix + "form_" + this.id + '_values_' + this.languageCodes[0];

		this.nodes.productOptionValuesTemplate = document.getElementsByClassName(this.cssPrefix + "form_values_value", this.nodes.valuesDefaultGroup)[0];
		this.nodes.productOptionValuesUl	   = this.nodes.valuesDefaultGroup.getElementsByTagName('ul')[0];

		this.nodes.mainTitle 			= document.getElementsByClassName(this.cssPrefix + "title", this.nodes.parent)[0];
	},

	/**
	 * Find all translations fields. This is done every time when new field is being added
	 *
	 * @access private
	 *
	 */
	bindTranslationValues: function()
	{
		this.nodes.translatedValues = document.getElementsByClassName(this.cssPrefix + "form_values_translations", this.nodes.parent);
	},



	/**
	 * Binds fields to some events
	 *
	 * @access private
	 *
	 */
	bindFields: function()
	{
		var self = this;

		for(var i = 0; i < this.nodes.stateLinks.length; i++)
		{
			Event.observe(this.nodes.stateLinks[i], "click", function(e) { self.changeStateAction(e) } );
		}

		Event.observe(this.nodes.valuesAddFieldLink, "click", function(e) { Event.stop(e); self.addValueFieldAction(); } );
		Event.observe(this.nodes.type, "change", function(e) { self.typeWasChangedAction(e) } );
		Event.observe(this.nodes.type, "focus", function(e) { self.fucusType(e) } );
		Event.observe(this.nodes.cancel, "click", function(e) { Event.stop(e); self.cancelAction() } );
		if(this.id.match('new')) Event.observe(this.nodes.cancelLink, "click", function(e) { Event.stop(e); self.cancelAction() } );
		Event.observe(this.nodes.save, "click", function(e) { self.saveAction(e) } );

		// Also some actions must be executed on load. Be aware of the order in which those actions are called
		this.loadProductOptionAction();
		this.loadValueFieldsAction();
		this.bindTranslationValues();
		this.typeWasChangedAction();

		if(!this.id.match(/new$/))
		{
			new Insertion.After(this.nodes.type.up('fieldset'), '<span class="productOption_form_type_static">' + this.nodes.type.options[this.productOption.type].text + '</span>')
			this.nodes.type.up('fieldset').style.display = 'none';
		}

		jQuery(this.nodes.displayType).change(function(e)
		{
			jQuery(self.nodes.stepValues).toggleClass('colorPicker', this.value == self.DISPLAYTYPE_COLOR);
		}).change();

		new Form.EventObserver(this.nodes.form, function() { self.formChanged = true; } );
		Form.backup(this.nodes.form);
	},

	fucusType: function(e)
	{
		if(this.nodes.type.realIndex)
		{
			this.nodes.type.selectedIndex = this.nodes.type.realIndex;
		}
	},

	/**
	 * When the value type changes whe should decide whether show step "Values" (for selectors) or not,
	 * and whether to show translations or not (show for text, hide for numbers)
	 *
	 * @access private
	 *
	 */
	typeWasChangedAction: function()
	{
		this.type = this.nodes.type.value;

		this.nodes.type.selectedOption = this.nodes.type.options[this.nodes.type.selectedIndex];
		this.nodes.type.realIndex	  = this.nodes.type.selectedIndex;

		// if selected type is a selector type then show selector options fields (aka step 2)
		if (this.type == this.TYPE_SELECT)
		{
			this.nodes.tabsContainer.show();
			this.nodes.optionPriceContainer.hide();
			this.nodes.optionSelectMessage.show();
		}
		else
		{
			this.nodes.tabsContainer.hide();
			this.nodes.optionPriceContainer.show();
			this.nodes.optionSelectMessage.hide();
		}

		if (this.type == this.TYPE_FILE)
		{
			this.nodes.optionFile.show();
		}
		else
		{
			this.nodes.optionFile.hide();
		}
	},

	togglemaxFileSizeFields: function(isDisplayed)
	{
		$A(this.nodes.parent.getElementsByClassName('optionmaxFileSize')).each(
			function(node)
			{
				if (isDisplayed)
				{
					node.show();
				}
				else
				{
					node.hide();
				}
			}
		);
	},

	bindOneValue: function(li)
	{
		var self = this;
		var input = li.getElementsByTagName("input")[0];
		if(input.type == 'text')
		{
			Event.observe(input, "keyup", function(e) { self.mainValueFieldChangedAction(e) } );
			Event.observe(input, "keydown", function(e) {
				if(!this.up('li').next() && this.value != '') self.addValueFieldAction();
			});
		}

		var input = jQuery(li).find('.selectColor input')[0];
		input.color = new jscolor.color(input, {adjust: false, required: false, hash: true, caps: false});
	},

	/**
	 * This method binds all default values (those which are field in "Values" step) and create new fields in "Translations"
	 * step where user can fill translations for those values
	 *
	 * @access private
	 *
	 */
	bindDefaultFields: function()
	{
		var self = this;
		this.fieldsList = ActiveList.prototype.getInstance(this.nodes.valuesDefaultGroup.getElementsByTagName("ul")[0], {
			beforeSort: function(li, order)
			{
				return self.links.sortValues + '?target=' + this.ul.id + '&' + order;
			},
			afterSort: function(li, response){	},

			beforeDelete: function(li){
				if(this.getRecordId(li).match(/^new/))
				{
					var emptyFilters = true;
					var inputValues = li.getElementsByTagName("input");
					for(var i = 0; i < inputValues.length; i++)
					{
						if(!Element.hasClassName('dom_template', inputValues[i]) && inputValues[i].style.display != 'none' && inputValues[i].value != '')
						{
							emptyFilters =  false;
						}
					}

					if(emptyFilters || confirm(self.messages.removeFieldQuestion))
					{
						self.deleteValueFieldAction(li);
					}
				}
				else if(confirm(self.messages.removeFieldQuestion))
				{
					return Backend.ProductOption.prototype.links.deleteValue + this.getRecordId(li);
				}
			},
			afterDelete: function(li, response){
				response = eval('(' + response + ')');

				self.deleteValueFieldAction(li)
			}
		}, this.msg.activeListMessages);
	},


	changeMainTitleAction: function(newTitle)
	{
		if(this.nodes.mainTitle)
		{
			if(this.nodes.mainTitle.firstChild)
			{
				this.nodes.mainTitle.firstChild.nodeValue = newTitle;
			}
			else
			{
				this.nodes.mainTitle.appendChild(document.createTextNode(newTitle));
			}
		}
	},


	/**
	 * Here we fill "Main" step field values like name, handle, input type and value type
	 *
	 * @access private
	 *
	 */
	loadProductOptionAction: function()
	{
		var self = this;

		// Default language
		if(this.id) this.nodes.id.value = this.id;
		if(this.parentID) this.nodes.parentID.value = this.parentID;

		this.nodes.name.value = this.productOption.name_lang ? this.productOption.name_lang : '';
		this.nodes.description.value = this.productOption.description_lang ? this.productOption.description_lang : '';
		this.nodes.maxFileSize.value = this.productOption.maxFileSize ? this.productOption.maxFileSize : '';
		this.nodes.fileExtensions.value = this.productOption.fileExtensions ? this.productOption.fileExtensions : '';
		this.nodes.displayType.value = this.productOption.displayType;

		if (this.productOption.DefaultChoice)
		{
			this.nodes.price.value = this.productOption.DefaultChoice.priceDiff ? this.productOption.DefaultChoice.priceDiff : '';
		}

		this.nodes.name.id = this.cssPrefix + this.parentID + "_" + this.id + "_name_" + this.languageCodes[0];

		['isRequired', 'isDisplayed', 'isDisplayedInList', 'isDisplayedInCart', 'isPriceIncluded'].each(
		function(key)
		{
			this.nodes[key].checked = this[key] == 1;
			this.nodes[key].id = this.cssPrefix + this.parentID + "_" + this.id + "_" + key;
		}.bind(this));

		Event.observe(this.nodes.price, "input", function(e) { new NumericFilter(this); }, false);

		$A(['name',
			'isRequired', 'maxFileSize', 'fileExtensions', 'isDisplayed', 'isDisplayedInList', 'isDisplayedInCart', 'isPriceIncluded',
			'type']).each(function(fieldName)
		{
			this.nodes.labels[fieldName].onclick = function() {
				var input = this.nodes[fieldName];

				if(input.down('input'))
				{
					input = input.down('input');
				}
				else if(input.down('select'))
				{
					input = input.down('select');
				}
				else if(input.down('textarea'))
				{
					input = input.down('textarea');
				}

				if('checkbox' == input.type) input.checked = !input.checked;
				else input.focus();
			}.bind(this);
		}.bind(this));

		if(!this.id.match(/new$/))
		{
			this.nodes.type.up('fieldset').style.display = 'none';
		}
		this.changeMainTitleAction(this.nodes.name.value);

		var fields = ['name', 'description'];
		for(var i = 1; i < this.languageCodes.length; i++)
		{
			for(var j = 0; j < fields.length; j++)
			{
				var field = this.nodes.form.elements.namedItem(fields[j] + '_' + this.languageCodes[i]);
				var label = field.up('.languageFormContainer').down('.translation_' + fields[j] + '_label');
				field.id = this.cssPrefix + this.parentID + "_" + this.id + "_" + fields[j] + "_" + this.languageCodes[i];
				label.forID = field.id;

				if(this.productOption[fields[j] + '_' + this.languageCodes[i]]) field.value = this.productOption[fields[j] + '_' + this.languageCodes[i]];
				Event.observe(label, "click", function(e) { $(this.forID).focus(); });
		   }
		}
	},

	/**
	 * When we create form from JSON string we should create and fill in values fields (from "Values" step)
	 * and their translations in "Translations" step if needed
	 *
	 * @access private
	 *
	 */
	loadValueFieldsAction: function()
	{
		var self = this;
		if(this.values)
		{
			$H(this.values).each(function(value) {
				self.addField(value.value, value.key);
			});

			this.bindDefaultFields();
			this.fieldsList.touch(true);
		}
	},

	/**
	 * This method separates language codes from language titles
	 *
	 * @example (lt: Lithuanian, ru: Russian) will create [lt, ru] array
	 *
	 * @access private
	 *
	 */
	loadLanguagesAction: function()
	{
		var self = this;
		this.languageCodes = [];

		$H(this.languages).each(function(language) {
			self.languageCodes[self.languageCodes.length] = language.key;
		});
	},

	/**
	 * When we add new value "Values" step we are also adding it to "Translations" step. Field name
	 * will have new3 (or any other number) in its name. We are not realy creating a field here. Instead
	 * we are calling for addField method to do the job. The only usefull thing we are doing here is
	 * generating an id for new field
	 *
	 * @access private
	 *
	 */
	addValueFieldAction: function()
	{
		this.addField(null, "new" + Backend.ProductOption.prototype.countNewValues, false);
		this.bindDefaultFields();
		Backend.ProductOption.prototype.countNewValues++;
	},


	/**
	 * This one is easy. When we click on delete value from "Values" step we delete the value and it's
	 * translation in "Translations" step
	 *
	 * @param Event e Event
	 *
	 * @access private
	 *
	 */
	deleteValueFieldAction: function(li)
	{
		var activeList = this.fieldsList;

		var splitedHref = li.id.split("_");
		var id = splitedHref.last();
		var isNew = id ? true : false;

		activeList.remove(li);
		if(!isNew && this.categoryID)
		{
			CategoryTabControl.prototype.resetTabItemsCount(this.categoryID);
		}

		for(var i = 1; i < this.languageCodes.length; i++)
		{
			var translatedValue = $(this.cssPrefix + "form_values_" + this.languageCodes[i] + "_" + id);

			// if new or not main language
			if(isNew || i > 0)
			{
				if (translatedValue)
				{
					Element.remove(translatedValue);
				}
			}
			else
			{
				translatedValue.id += '_deleted';
				var input = translatedValue.getElementsByTagName('input')[0];
				input.name = input.name.replace(/\[\w+\]\[([\d]+)\]/, "[deleted][$1]");
				translatedValue.style.display = 'none';
			}
		}
	},

	/**
	 * This callback is executed when user changes the state. When user change the state all other
	 * states are hidden and only current state shown or if the user was so stupid to click on current
	 * state whe whole thing will crash (or the current step will collapse. I don't realy remember)
	 *
	 * @param Event e Event
	 *
	 * @access private
	 *
	 */
	changeStateAction: function(e)
	{
		if(!e.target)
		{
			e.target = e.srcElement;
		}

		Event.stop(e);

		var currentStep = this.cssPrefix + e.target.hash.substring(1);
		this.showState(currentStep);
	},

	showState: function(currentStep)
	{
		for(var i = 0; i < this.nodes.stepLevOne.length; i++)
		{
			this.nodes.stateLinks[i].id = this.cssPrefix + 'change_state' + this.id;

			if(!Element.hasClassName(this.nodes.stepLevOne[i], currentStep))
			{
				this.nodes.stepLevOne[i].style.display = 'none';
				Element.removeClassName(this.nodes.stateLinks[i], this.cssPrefix + "change_state_active");
				Element.removeClassName(this.nodes.stateLinks[i].parentNode, 'active');
			}
			else
			{
				this.nodes.stepLevOne[i].style.display = 'block';
				Element.addClassName(this.nodes.stateLinks[i], this.cssPrefix + "change_state_active");
				Element.addClassName(this.nodes.stateLinks[i].parentNode, 'active');
			}
		}
	},


	/**
	 * When some dumbass creates/modifies value in "Values" step, we are automatically creating
	 * a label for similar field in every language section in "Translations" step.
	 *
	 * @example If we tipe one in "Values" step like so
	 * ___________
	 * |One	   |
	 * ------------
	 *
	 * the programm will change label of similar fields in every translation language like so
	 *
	 * Lithuanian:
	 *		___________
	 * One:   |Vienas	|
	 *		------------
	 *
	 * German:
	 *		___________
	 * One:   |Einz	  |   * I don't realy know how to write one in germat and also tooday i am to lazy to google for it :(
	 *		------------
	 *
	 * @param Event e Event
	 *
	 * @access private
	 */
	mainValueFieldChangedAction: function(e)
	{
		if(!e.target)
		{
			e.target = e.srcElement;
		}

		Event.stop(e);

		var splitedHref = e.target.id.split("_"); /* parentNode. */
		var isNew = splitedHref[splitedHref.length - 2] == 'new' ? true : false;
		var id = (isNew ? 'new' : '') + splitedHref[splitedHref.length - 3];

		for(var i = 1; i < this.languageCodes.length; i++)
		{
			$(this.cssPrefix + "form_values_" +  this.languageCodes[i] + "_" + id).getElementsByTagName("label")[0].innerHTML = e.target.value;
		}
	},

	/**
	 * Here we are adding new field to values list in "Values" step and "Translations" step.
	 *
	 * @param hash value Value of newly created field. The value is a hash array with value for every language {'en': "One", 'lt': "Vienas", 'de': "Einz"}
	 * @param int id Id of a newly created field
	 *
	 * @access private
	 *
	 */
	addField: function(value, id, touch)
	{
		var self = this;
		if(!value) value = {};

		var values_template = this.nodes.productOptionValuesTemplate;
		var ul = this.nodes.productOptionValuesUl;

		if(!this.fieldsList) this.bindDefaultFields();
		var li = this.fieldsList.addRecord(id, values_template, !!touch);
		Element.removeClassName(li, 'dom_template');

		// Name field
		var input = li.down("input." + this.cssPrefix + "valueName");
		input.name = "values[" + id + "]["+this.languageCodes[0]+"]";
		input.value = value.name_lang ? value.name_lang : '' ;
		input.id = this.cssPrefix + "field_" + id + "_value_" + this.languageCodes[0];

		Event.observe(input, "input", function(e) { self.mainValueFieldChangedAction(e) }, false);
		Event.observe(input, "input", function(e) {
			if(!this.up('li').next() && this.value != '') self.addValueFieldAction();
			this.focus();
		});

		// price field
		var priceField = li.down("input." + this.cssPrefix + "valuePrice");
		priceField.name = "prices[" + id + "]";
		priceField.value = value.priceDiff ? value.priceDiff : '';
		priceField.id = this.cssPrefix + "field_" + id + "_price";
		Event.observe(priceField, "input", function(e) { new NumericFilter(this); }, false);

		// color field
		var colorField = li.down("input." + this.cssPrefix + "color");
		colorField.name = "color[" + id + "]";
		colorField.value = value.config ? value.config.color : '';

		// now insert all translation fields
		var nodeValues = this.nodes.parent.down('.productOption_step_values');
		for(var i = 1; i < this.languageCodes.length; i++)
		{
			var translationsUl = nodeValues.down('.languageFormContainer_' + this.languageCodes[i]).down('ul');

			var newValueTranslation = translationsUl.down('.dom_template').cloneNode(true);
			Element.removeClassName(newValueTranslation, "dom_template");
			newValueTranslation.id = newValueTranslation.id + this.languageCodes[i] + "_" + id;
			translationsUl.appendChild(newValueTranslation);

			var inputTranslation = newValueTranslation.getElementsByTagName("input")[0];
			inputTranslation.name = "values[" + id + "][" + this.languageCodes[i] + "]";
			inputTranslation.value = value['name_' + this.languageCodes[i]] ? value['name_' + this.languageCodes[i]] : '';
			var translationLabel = newValueTranslation.down("label");
			translationLabel.update(input.value);

			inputTranslation.id = this.cssPrefix + "field_" + id + "_value_" + this.languageCodes[i];
			translationLabel['for'] = inputTranslation.id;
			translationLabel.onclick = function() { $(this['for']).focus(); }
		}

		this.bindOneValue(li);
	},


	/**
	 * This method is called when user click on cancel link. It resets all fields to its defaults and closes form
	 *
	 * @param Event e Event
	 *
	 * @access public
	 *
	 */
	cancelAction: function()
	{
		// first cancel all modifications if they took place
		if(this.id.match('new'))
		{
			this.recreate(this.productOption, true);
		}
		else if(Form.hasBackup(this.nodes.form) && this.formChanged)
		{
			Form.restore(this.nodes.form);

			this.typeWasChangedAction();
			this.changeMainTitleAction(this.nodes.name.value);
		}

		// Use Active list toggleContainer() method if this productOption is inside Active list
		// Note that if it is inside a list we are showing and hidding form with the same action,
		// butt =] when dealing with new form showing form action is handled by Backend.ProductOption::createNewAction()
		if(this.nodes.parent.tagName.toLowerCase() == 'li')
		{
			ActiveList.prototype.getInstance(this.nodes.parent.parentNode).toggleContainer(this.nodes.parent, 'edit');
		}
		else
		{
			this.hideNewProductOptionAction(this.parentID);
		}

		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
	},

	/**
	 * This method is called when user clicks on save button. It saves form values, and does i don't know what (i guess it should close the form)
	 *
	 * @param Event e Event
	 *
	 * @access public
	 */
	saveAction: function(e)
	{
		if(!e.target)
		{
			e.target = e.srcElement;
		}

		Event.stop(e);

		this.saveProductOption();
	},

	/**
	 * This action is executed when saving specification field. THis method will be executed before ajax request to the server is sent
	 */
	saveProductOption: function()
	{
		// Toggle progress won't work on new form
		try
		{
			ActiveList.prototype.getInstance(this.nodes.parent.parentNode).toggleProgress(this.nodes.parent);
		}
		catch (e)
		{
		}

		ActiveForm.prototype.resetErrorMessages(this.nodes.form);

		this.nodes.form.action = this.id.match(/new/) ? Backend.ProductOption.prototype.links.create : Backend.ProductOption.prototype.links.update;
		new LiveCart.AjaxRequest(
			this.nodes.form,
			false,
			function(param)
			{
				this.afterSaveAction(param.responseText)
			}.bind(this)
		);
	},

	/**
	 * This action is executed after server response with possible errors in entered
	 * spec field fields
	 *
	 */
	afterSaveAction: function(jsonResponseString)
	{
		var self = this;
		var jsonResponse = eval("("+jsonResponseString+")");

		if(jsonResponse.status == 'success')
		{
			// @todo reset product forms
			// Backend.Product.resetEditors();

			if(this.nodes.parent.tagName.toLowerCase() == 'li')
			{
				ActiveForm.prototype.updateNewFields('productOption_update', $H(jsonResponse.newIDs), this.nodes.parent);
				Form.backup(this.nodes.form);
				this.backupName = this.nodes.name.value;

				var activeList = ActiveList.prototype.getInstance(this.nodes.parent.parentNode);

				this.nodes.productOptionValuesUl.childElements().each(function(li)
				{
					if(li.id.match(/new/))
					{
						this.deleteValueFieldAction(li)
					}
				}.bind(this))

				this.nodes.parent.down('.productOption_title').update(this.nodes.name.value);

				activeList.toggleContainer(this.nodes.parent, 'edit', 'yellow');
			}
			else
			{
				var tempElement = document.createElement('div');
				$(tempElement).update('<span class="productOption_title">' + this.nodes.name.value + '</span>');

				var activeRecord = ActiveList.prototype.getInstance("productOption_items_list_" + this.parentID + '_');

				var liElement = activeRecord.addRecord(jsonResponse.id, tempElement);

				this.hideNewProductOptionAction(this.parentID);
				this.recreate(this.productOption, true);

				activeRecord.touch();
			}

			if (this.categoryID)
			{
				CategoryTabControl.prototype.resetTabItemsCount(this.categoryID);
			}
		}
		else if(jsonResponse.errors)
		{
			var firstError; for(firstError in jsonResponse.errors) break;
			this.showState('productOption_step_' + (firstError.match(/^values/) ? 'values' : 'main'));
			ActiveForm.prototype.setErrorMessages(this.nodes.form, jsonResponse.errors);
		}

		try
		{
			ActiveList.prototype.getInstance(this.nodes.parent.parentNode).toggleProgress(this.nodes.parent);
		}
		catch (e)
		{
		}
	},


	/**
	 * All Your Base Are Belong To Us! A mystery function.
	 * Hides new spec field form
	 *
	 * @static
	 */
	hideNewProductOptionAction: function(parentID)
	{
		var form = new ActiveForm.Slide("productOption_menu_" + parentID);
		form.hide("addProductOption", this.cssPrefix + "item_new_" + parentID + "_form", ['type']);
	},


	/**
	 * When the form is created it gets all it's parameters from JSON. However when getting options
	 * list we should create an array of Option objects from JSON.
	 *
	 * @example
	 * var json = {
	 *			  pc:  'Personal Computer',
	 *			  mac: 'PowerPC',
	 *			  sun: 'Sun Server'
	 *		   }
	 *
	 * is converted to
	 *
	 * var options = (
	 *				new Option('Personal Computer', pc),
	 *				new Option('e', mac),
	 *				new Option('Sun Server', sun)
	 *			 )
	 *
	 * @static
	 */
	createTypesOptions: function(types)
	{
	   var typesOptions = {};
	   $H(types).each(function(value) {
		   var options = [];

		   $H(value.value).each(function(option) {
			   options[options.length] = [option.key, option.value];
		   });

		   typesOptions[value.key] = options;
		});

		return typesOptions;
	},

	/**
	 * This method unfolds "Create new Spec Field entry" form. Items from existing spec fields list are unfolded using
	 * ActiveList methods
	 *
	 * @see ActiveList
	 *
	 * @param HTMLElement parent form node (it should have "create new entry" and an empty spec field form inside it)
	 *
	 * @static
	 *
	 */
	createNewAction: function(parentID)
	{
		var form = new ActiveForm.Slide("productOption_menu_" + parentID);
		var fieldFormId = this.cssPrefix + "item_new_"+parentID+"_form";

		form.show("addProductOption", fieldFormId, ['type']);
	},
}