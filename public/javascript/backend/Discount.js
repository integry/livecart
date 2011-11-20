/**
 *	@author Integry Systems
 */

if (!Backend.Discount)
{
	Backend.Discount = {}
}

Backend.Discount.GridFormatter =
{
	url: '',

	formatValue: function(field, value, id)
	{
		if ('DiscountCondition.name' == field)
		{
			value = '<span><span class="progressIndicator discountIndicator" id="discountIndicator_' + id + '" style="display: none;"></span></span>' +
				'<a href="' + this.url + '#discount_' + id + '" id="discount_' + id + '" onclick="Backend.Discount.Editor.prototype.open(' + id + ', event); return false;">' +
					 value +
				'</a>';
		}

		return value;
	}
}

Backend.Discount.Editor = function()
{
	this.callConstructor(arguments);
}

Backend.Discount.Editor.methods =
{
	namespace: Backend.Discount.Editor,

	getMainContainerId: function()
	{
		return 'discountManagerContainer';
	},

	getAddContainerId: function()
	{
		return 'addDiscountContainer';
	},

	getInstanceContainer: function(id)
	{
		return id ? $("tabUserInfo_" + id + "Content") : $(this.getAddContainerId());
	},

	getListContainer: function()
	{
		return $('discountGrid');
	},

	getNavHashPrefix: function()
	{
		return '#discount_';
	},

	getActiveGrid: function()
	{
		return window.activeGrids["discount_0"];
	},

	reInitAddForm: function()
	{
		$('cancel_discount_add').onclick = function(e)
		{
			this.hideAddForm();
			Event.stop(e);
		}.bind(this);

		$('discountAddForm').onsubmit = this.beforeSaveAdd.bind(this);
	},

	beforeSaveAdd: function(e)
	{
		var instance = this.saveAdd(e);
		instance.oldAfterSubmit = instance.afterSubmitForm.bind(instance);
		instance.afterSubmitForm = function(response)
		{
			this.oldAfterSubmit(response);
			this.cancelForm();
			this.cancelAdd();
			Backend.Discount.Editor.prototype.open(response.condition.ID);
		}
	},

	initDiscountForm: function(cont)
	{
		cont = $(cont);

		var codeContainer = cont.down('.couponCode');
		var typeContainer = cont.down('.couponLimitType');
		var countContainer = cont.down('.couponLimitCount');

		var codeField = codeContainer.down('input');
		var typeField = typeContainer.down('select');

		codeField.onchange = function()
		{
			if (this.value)
			{
				typeContainer.show();
			}
			else
			{
				typeContainer.hide();
			}

			typeField.onchange();
		}

		codeField.onkeyup = codeField.onchange;

		typeField.onchange = function()
		{
			if (this.value != '')
			{
				countContainer.show();
			}
			else
			{
				countContainer.hide();
			}
		}

		codeField.onchange();
	}
}

Backend.Discount.Editor.inheritsFrom(Backend.MultiInstanceEditor);

Backend.Discount.Condition = function(tree, records, serializedValues, container)
{
	this.condition = tree;
	this.container = container;
	this.records = records;
	this.serializedValues = serializedValues;

	['conditionTemplate', 'recordTemplate', 'selectRecordTemplate'].each(function(cl)
	{
		if (!this.namespace.prototype[cl])
		{
			this.namespace.prototype[cl] = $(cl).down('li')
		}
	}.bind(this));

	this.namespace.prototype.template = this.namespace.prototype.conditionTemplate;

	this.createNode();
}

Backend.Discount.Condition.prototype =
{
	namespace: Backend.Discount.Condition,

	node: null,

	controller: 'backend.discount',

	findUsedNodes: function()
	{
		this.typeSel = this.node.down('.conditionClass');
		this.compSel = this.node.down('.comparisonType');
		this.valueField = this.node.down('.comparisonValue');
		this.subCondition = this.node.down('.subCondition');
		this.subConditionContainer = this.node.down('.conditionContainer');
		this.isAllSubconditions = this.node.down('.isAllSubconditions');
		this.isAnyRecord = this.node.down('.isAnyRecord');
		this.isReverse = this.node.down('.isReverse');
		this.deleteIcon = this.node.down('.conditionDelete');
		this.recordContainer = this.node.down('.recordContainer');
		this.valueContainer = this.node.down('.valueContainer');
		this.selectRecordContainer = this.node.down('.selectRecordContainer');
		this.fieldsContainer = this.node.down('.ruleFields');
		this.productFieldSel = this.node.down('.comparisonField');
		this.timeRangeSelector = this.node.down('.conditionTime');

 		this.conditionTime = this.timeRangeSelector.down('select');
		this.conditionTimeBefore = this.timeRangeSelector.down('.conditionTimeBefore');
		this.conditionTimeRange = this.timeRangeSelector.down('.conditionTimeRange');
	},

	bindEvents: function()
	{
		[this.compSel, this.valueField, this.isAllSubconditions, this.isAnyRecord, this.isReverse, this.productFieldSel].each(function(field)
		{
			field.name += '_' + this.condition.ID;
			Event.observe(field, 'change', this.saveFieldChange.bind(this));
		}.bind(this));

		['isAllSubconditions', 'isAnyRecord', 'isReverse'].each(function(name)
		{
			this[name].id += '_' + this.condition.ID;
			$(this[name].parentNode).down('label').setAttribute('for', name + '_' + this.condition.ID);
		}.bind(this));

/*
		this.isAnyRecord.id += '_' + this.condition.ID;
		$(this.isAnyRecord.parentNode).down('label').setAttribute('for', 'isAnyRecord_' + this.condition.ID);
*/

		Event.observe(this.subCondition, 'click', this.createSubCondition.bind(this));
		Event.observe(this.deleteIcon, 'click', this.remove.bind(this));

		Event.observe(this.typeSel, 'change', this.changeType.bind(this));
		Event.observe(this.conditionTime, 'change', this.changeDateRangeType.bind(this));

		this.timeRangeSelector.getElementsBySelector('.value', 'input[type=hidden]').each(function(field)
		{
			var c = this.condition.serializedCondition;
			if (c && c.time && c.time[field.name])
			{
				field.value = c.time[field.name];
			}

			field.name = 'time_' + field.name;
			field.onchange = this.saveParamChange.bind(this);
		}.bind(this));

		this.fieldsContainer.getElementsBySelector('.ruleField').each(function(field)
		{
			var c = this.condition.serializedCondition;
			if (c && c[field.name])
			{
				field.value = c[field.name];
				console.log(c[field.name]);
			}

			field.onchange = this.saveParamChange.bind(this);
		}.bind(this));

		// add records
		Event.observe(this.recordContainer.down('.addConditionCategory'), 'click', this.addCategory.bind(this));
		Event.observe(this.recordContainer.down('.addConditionProduct'), 'click', this.addProduct.bind(this));
		Event.observe(this.recordContainer.down('.addConditionManufacturer'), 'click', this.addManufacturer.bind(this));
		Event.observe(this.recordContainer.down('.addConditionUser'), 'click', this.addUser.bind(this));
	},

	setValues: function()
	{
		this.compSel.value = this.condition.comparisonType;
		this.valueField.value = this.condition.count != null ? this.condition.count : this.condition.subTotal;
		this.isAllSubconditions.checked = this.condition.isAllSubconditions == 1;
		this.isReverse.checked = this.condition.isReverse == 1;

		// value is reversed in user interface
		this.isAnyRecord.checked = this.condition.isAnyRecord == 0;
	},

	createNode: function()
	{
		var el = this.template.cloneNode(true);
		this.container.appendChild(el);
		this.node = el;

		this.findUsedNodes();

		this.typeSel.value = this.condition.conditionClass;

		if (this.condition.recordCount > 0)
		{
			this.condition.records.each(function(record)
			{
				var rec = this.createRecord(record);
			}.bind(this));
		}

		this.setValues();
		this.bindEvents();

		if (this.condition.sub)
		{
			this.condition.sub.each(function(sub) { new this.namespace(sub, this.records, this.serializedValues, this.subConditionContainer); }.bind(this));
			this.toggleSubsContainer(true);
		}

		this.changeType(true);
	},

	saveFieldChange: function(e)
	{
		var field = Event.element(e);

		if (field == this.productFieldSel)
		{
			field = this.valueField;
		}

		$(field.parentNode).addClassName('fieldUpdating');

		var value = field.value;
		if ('checkbox' == field.type)
		{
			value = field.checked ? 1 : 0;
		}

		new LiveCart.AjaxRequest(Backend.Router.createUrl(this.controller, 'updateConditionField', {type: this.typeSel.value, field: field.name, productField: this.productFieldSel.value, value: value, comparisonType: this.compSel.value}), null, this.completeUpdateField.bind(this));
	},

	saveParamChange: function(e)
	{
		var field = e instanceof Event ? Event.element(e) : e;

		$(field.parentNode).addClassName('fieldUpdating');

		var value = field.value;
		if ('checkbox' == field.type)
		{
			value = field.checked ? 1 : 0;
		}

		new LiveCart.AjaxRequest(Backend.Router.createUrl(this.controller, 'setSerializedValue', {type: this.typeSel.value, field: field.name, value: value, id: this.condition.ID}), null, function()
		{
			$(field.parentNode).removeClassName('fieldUpdating');
		});
	},

	completeUpdateField: function(originalRequest)
	{
		var updated = originalRequest.responseData;
		var field = updated == 'comparisonValue' ? this.valueField : this.compSel;
		if (this[updated] && this[updated].getAttribute && (this[updated].getAttribute('type') == 'checkbox'))
		{
			field = this[updated];
		}

		$(field.parentNode).removeClassName('fieldUpdating');
	},

	createSubCondition: function(e)
	{
		var element = Event.element(e);
		Event.stop(e);
		new LiveCart.AjaxRequest(Backend.Router.createUrl(this.controller, 'addCondition', {id: this.condition.ID}), element.parentNode.down('.progressIndicator'), this.completeAdd.bind(this));
	},

	completeAdd: function(originalRequest)
	{
		var cond = new this.namespace(originalRequest.responseData, this.records, this.serializedValues, this.subConditionContainer);
		this.toggleSubsContainer(true);
	},

	toggleSubsContainer: function(show)
	{
		if (show)
		{
			this.subConditionContainer.show();
		}
		else
		{
			this.subConditionContainer.hide();
		}
	},

	remove: function()
	{
		if (confirm(Backend.getTranslation('_confirm_condition_delete')))
		{
			$(this.deleteIcon).hide();
			new LiveCart.AjaxRequest(Backend.Router.createUrl(this.controller, 'deleteCondition', {id: this.condition.ID}), this.deleteIcon.parentNode.down('.progressIndicator'), this.completeDelete.bind(this));
		}
	},

	completeDelete: function()
	{
		this.container.removeChild(this.node);
		if (!this.container.getElementsByTagName('li').length)
		{
			this.container.hide();
		}
	},

	changeType: function(noClear)
	{
		if (noClear instanceof Event)
		{
			this.recordContainer.down('.records').innerHTML = '';
		}

		var type = this.typeSel.value;

		if (type == 'RuleConditionOrderItemCount')
		{
			this.valueField.value = this.condition.count;
		}
		else if (type == 'RuleConditionOrderItemCount')
		{
			this.valueField.value = this.condition.subTotal;
		}

		// toggle container visibility depending on type
		if (this.serializedValues[type])
		{
			this.compSel.hide();
			this.valueField.hide();
			this.recordContainer.hide();
			this.valueContainer.show();
		}
		else if (['RuleConditionCustomerIs'].indexOf(type) > -1)
		{
			this.compSel.hide();
			this.valueField.hide();
			this.valueContainer.hide();
			this.recordContainer.show();
		}
		else
		{
			this.compSel.show();
			this.valueField.show();
			this.valueContainer.hide();
			this.recordContainer.show();
		}

		if ('RuleConditionPastOrderContainsProduct' == type)
		{
			this.timeRangeSelector.show();
			this.changeDateRangeType();
		}
		else
		{
			this.timeRangeSelector.hide();
		}

		if (['RuleConditionDeliveryZoneIs', 'RuleConditionUserGroupIs'].indexOf(type) > -1)
		{
			this.compSel.hide();
			this.valueField.hide();
		}

		// add selectable records to list
		var recordClass = '';
		if (type == 'RuleConditionDeliveryZoneIs')
		{
			recordClass = 'DeliveryZone';
		}
		else if (type == 'RuleConditionUserGroupIs')
		{
			recordClass = 'UserGroup';
		}

		if (recordClass)
		{
			this.selectRecordContainer.down('ul').innerHTML = '';
			for (var k = 0; k < this.records[recordClass].length; k++)
			{
				var record = this.records[recordClass][k];
				var instance = this.createSelectRecord(recordClass, record);
			}
		}

		// add selectable values to list
		if (this.serializedValues[type])
		{
			this.valueContainer.down('ul').innerHTML = '';
			$H(this.serializedValues[type]).each(function(val)
			{
				this.createSelectValue(type, val[0], val[1]);
			}.bind(this));
		}

		// custom fields
		jQuery('.classContainer', this.fieldsContainer).hide();
		var fieldContainer = jQuery('.classContainer.' + type, this.fieldsContainer);
		if (fieldContainer.length)
		{
			fieldContainer.show();
			this.compSel.hide();
			this.valueField.hide();
		}

		this.node.className = 'type_' + type;

		if ('RuleConditionPastOrderContainsProduct' == type)
		{
			this.node.className += ' type_RuleConditionContainsProduct';
		}
	},

	changeDateRangeType: function()
	{
		if ('before' == this.conditionTime.value)
		{
			this.conditionTimeBefore.show();
			this.conditionTimeRange.hide();
		}
		else
		{
			this.conditionTimeBefore.hide();
			this.conditionTimeRange.show();
			this.setupCalendar('from');
			this.setupCalendar('to');
		}
	},

	setupCalendar: function(id)
	{
		// set up calendar field
		var time = this.conditionTimeRange.down('#' + id);
		var time_real = this.conditionTimeRange.down('#' + id + '_real');
		var time_button = this.conditionTimeRange.down('#' + id + '_button');

		time.onchange = function() { time_real.onchange(time_real); }

		time_button.realInput = time_real;
		time_button.showInput = time;

		time.realInput = time_real;
		time.showInput = time;

		Event.observe(time,		"keyup",	 Calendar.updateDate );
		Event.observe(time,		"blur",	  Calendar.updateDate );
		Event.observe(time_button, "mousedown", Calendar.updateDate );

		Calendar.setup({
			inputField:	 time,
			inputFieldReal: time_real,
			ifFormat:	   "%d-%b-%Y",
			button:		 time_button,
			align:		  "BR",
			singleClick:	true
		});
	},

	createSelectValue: function(type, value, name)
	{
		var el = $(this.namespace.prototype.selectRecordTemplate.cloneNode(true));
		this.valueContainer.down('ul').appendChild(el);

		var id='serValue_' + Math.random();
		var a = el.down('label');
		a.innerHTML = name;
		a.setAttribute('for', id);

		var inp = el.down('input');
		inp.id = id;
		inp.onchange = this.saveSelectValue.bind(this);
		inp.selectType = type;
		inp.value = value;

		var ser = this.condition.serializedCondition;
		if (ser && ser['values'] && ser['values'][value])
		{
			inp.checked = true;
		}

		return el;
	},

	saveSelectValue: function(e)
	{
		var inp = Event.element(e);
		new LiveCart.AjaxRequest(Backend.Router.createUrl(this.controller, 'saveSelectValue', {id: this.condition.ID, type: inp.selectType, value: inp.value, state: inp.checked}), null, function (originalRequest) { this.completeSaveSelectRecord(originalRequest, inp); }.bind(this));
		inp.parentNode.addClassName('selectRecordUpdating');
	},

	createSelectRecord: function(recordClass, data)
	{
		var el = $(this.namespace.prototype.selectRecordTemplate.cloneNode(true));
		this.selectRecordContainer.down('ul').appendChild(el);

		el.down('a').innerHTML = data['name'];

		var inp = el.down('input');
		inp.onchange = this.saveSelectRecord.bind(this);

		data.className = recordClass;
		inp.data = data;

		var id = data.ID;
		var createUrl = Backend.Router.createUrl.bind(Backend.Router);
		if ('DeliveryZone' == recordClass)
		{
			var url = createUrl('backend.deliveryZone', 'index') + '#zone_' + id;
		}
		else if ('UserGroup' == recordClass)
		{
			var url = createUrl('backend.userGroup', 'index') + '#group_' + id;
		}
		el.down('a').href = url;

		if (this.condition.records)
		{
			for (k = 0; k < this.condition.records.length; k++)
			{
				var rec = this.condition.records[k];
				if (rec[recordClass] && rec[recordClass].ID == data.ID)
				{
					inp.checked = true;
				}
			}
		}

		return el;
	},

	saveSelectRecord: function(e)
	{
		var inp = Event.element(e);

		new LiveCart.AjaxRequest(Backend.Router.createUrl(this.controller, 'saveSelectRecord', {id: this.condition.ID, class: inp.data.className, recordID: inp.data.ID, state: inp.checked, type: this.typeSel.value}), null, function (originalRequest) { this.completeSaveSelectRecord(originalRequest, inp); }.bind(this));

		inp.parentNode.addClassName('selectRecordUpdating');
	},

	completeSaveSelectRecord: function(originalRequest, el)
	{
		el.parentNode.removeClassName('selectRecordUpdating');
	},

	addRecord: function(className, id, onComplete)
	{
		new LiveCart.AjaxRequest(Backend.Router.createUrl(this.controller, 'addRecord', {id: this.condition.ID, class: className, recordID: id, type: this.typeSel ? this.typeSel.value : null}), null, function (originalRequest) { this.completeAddRecord(originalRequest, onComplete); }.bind(this));
	},

	completeAddRecord: function(originalRequest, onComplete)
	{
		var el = this.createRecord(originalRequest.responseData.data);
		new Effect.Highlight(el, { duration: 0.4 });
		if (onComplete)
		{
			onComplete();
		}
	},

	createRecord: function(data)
	{
		var className = '';
		Object.keys(data).each(function(key)
		{
			if ((key != 'ID') && (key != 'Condition') && (key != '__class__'))
			{
				className = key;
			}
		});

		var el = $(this.namespace.prototype.recordTemplate.cloneNode(true));
		el.data = data;
		this.recordContainer.down('.records').appendChild(el);

		el.data.className = className;

		if (('Manufacturer' == className) || ('DeliveryZone' == className))
		{
			var value = data[className]['name'];
		}
		else if ('User' == className)
		{
			var value = data[className].fullName + ' (' + data[className].email + ')';
		}
		else if ('Product' == className)
		{
			var value = data[className].name_lang + ' (' + data[className].sku + ')';
		}
		else
		{
			var value = data[className].name_lang;
		}

		var id = data[className].ID;
		var createUrl = Backend.Router.createUrl.bind(Backend.Router);

		if ('Manufacturer' == className)
		{
			var url = createUrl('backend.manufacturer', 'index') + '#manufacturer_' + id;
		}
		else if ('User' == className)
		{
			var url = createUrl('backend.userGroup', 'index') + '#user_' + id;
		}
		else if ('Product' == className)
		{
			var url = createUrl('backend.category', 'index') + '#product_' + id;
		}
		else if ('Category' == className)
		{
			var url = createUrl('backend.category', 'index') + '#cat_' + id + '#tabProducts__';
		}

		el.down('.recordClass').innerHTML = Backend.getTranslation(className);
		el.down('.recordName').innerHTML = value;
		el.down('a').href = url;

		Event.observe(el.down('.recordDelete'), 'click', this.deleteRecord.bind(this));

		return el;
	},

	deleteRecord: function(e)
	{
		var element = Event.element(e);
		var li = element.up('li');
		new LiveCart.AjaxRequest(Backend.Router.createUrl(this.controller, 'deleteRecord', {id: li.data.ID}), element.parentNode.down('.progressIndicator'), this.completeDeleteRecord.bind(li));
	},

	completeDeleteRecord: function()
	{
		this.parentNode.removeChild(this);
	},

	addCategory: function(e)
	{
		Event.stop(e);
		new Backend.Category.PopupSelector(
			function(categoryID, pathAsText, path)
			{
				this.addRecord('Category', categoryID);
			}.bind(this),
			null,
			null
		);
	},

	addProduct: function(e)
	{
		Event.stop(e);
		var w = new Backend.SelectPopup( Backend.Router.createUrl('backend.category', 'productSelectPopup'), '',
		{
			onObjectSelect: function(id)
			{
				this.addRecord('Product', id, function()
				{
					$(w.popup.document.getElementById('productIndicator_' + id)).hide();
				});
				return true;
			}.bind(this),

			height: 500
		});
	},

	addManufacturer: function(e)
	{
		Event.stop(e);
		var w = new Backend.SelectPopup( Backend.Router.createUrl('backend.manufacturer', 'selectPopup'), '',
		{
			onObjectSelect: function(id)
			{
				this.addRecord('Manufacturer', id, function()
				{
					$(w.popup.document.getElementById('manufacturerIndicator_' + id)).hide();
				});
				return true;
			}.bind(this),

			height: 510
		});
	},

	addUser: function(e)
	{
		Event.stop(e);
		var w = new Backend.SelectPopup( Backend.Router.createUrl('backend.user', 'selectPopup'), '',
		{
			onObjectSelect: function(id)
			{
				this.addRecord('User', id, function()
				{
					$(w.popup.document.getElementById('userIndicator_' + id)).hide();
				});
				return true;
			}.bind(this),

			height: 510
		});
	}
}


Backend.Discount.Action = function(action, container)
{
	this.action = action;
	this.container = container;

	if (!this.namespace.prototype.template)
	{
		this.namespace.prototype.template = $('actionTemplate').down('li')
	}

	this.createNode();
}

Backend.Discount.Action.prototype =
{
	namespace: Backend.Discount.Action,

	node: null,

	TYPE_ORDER_DISCOUNT: 0,
	TYPE_ITEM_DISCOUNT: 1,
	TYPE_CUSTOM_DISCOUNT: 5,

	controller: 'backend.discount',

	createAction: function(action)
	{
		return new Backend.Discount.Action(action, $('actionContainer_' + action.Condition.ID));
	},

	initializeList: function()
	{
		return ActiveList.prototype.getInstance($('actionContainer_' + this.action.Condition.ID), {
			 beforeSort:	 function(li, order)
			 {
				return Backend.Router.createUrl('backend.discount', 'sortActions', {draggedId: this.getRecordId(li), conditionId: this.getRecordId(li.parentNode) }) + '&' + order;
			   },
			 beforeDelete:   function(li)
			 {
				if(confirm(Backend.getTranslation('_confirm_action_delete')))
				{
					return Backend.Router.createUrl('backend.discount', 'deleteAction', {id:  this.getRecordId(li) });
				}
			 },
			 afterSort:	  function(li, response) {  },
			 afterDelete:	function(li, response) { }
		 }, null);
	},

	findUsedNodes: function()
	{
		this.actionClass = this.node.down('.actionClass');
		this.amount = this.node.down('.comparisonValue');
		this.discountStep = this.node.down('.discountStep');
		this.discountLimit = this.node.down('.discountLimit');
		this.type = this.node.down('select.applyTo');
		this.isEnabled = this.node.down('.isEnabled');
		this.isOrderLevel = this.node.down('.isOrderLevel');
		this.subConditionContainer = this.node.down('.conditionContainer');

		this.percentSign = this.node.down('.percent');
		this.currencySign = this.node.down('.currency');
		this.amountFields = this.node.down('.amountFields');
		this.classFields = $A(this.node.getElementsByClassName('classContainer'));
		this.applyTo = this.node.down('.applyTo');
	},

	bindEvents: function()
	{
		[this.actionClass, this.amount, this.discountStep, this.discountLimit, this.type, this.isEnabled, this.isOrderLevel].each(function(field)
		{
			field.name += '_' + this.action.ID;
			Event.observe(field, 'change', this.saveFieldChange.bind(this));
		}.bind(this));

		Event.observe(this.addAction, 'click', this.addAction.bind(this));

		['isOrderLevel', 'isEnabled'].each(function(field)
		{
			this[field].id = field + '_' + this.action.ID;
			$(this[field].parentNode).down('label').setAttribute('for', field + '_' + this.action.ID);
		}.bind(this));

		// custom fields
		$A(this.node.down('.actionFields').getElementsByClassName('actionField')).each(function(field)
		{
			var serialized = this.action.serializedData;
			if (serialized && (undefined != serialized[field.name]))
			{
				field.value = serialized[field.name];
			}

			field.name += '_' + this.action.ID;
			field.id = field.name;
			Event.observe(field, 'change', this.saveFieldChange.bind(this));
		}.bind(this));

		Event.observe(this.type, 'change', this.changeType.bind(this));
		Event.observe(this.actionClass, 'change', this.changediscountType.bind(this));
	},

	setValues: function()
	{
		this.actionClass.value = this.action.actionClass;
		this.amount.value = this.action.amount;

		if (this.action.ActionCondition)
		{
			this.type.value = (this.action.ActionCondition.ID == this.action.Condition.ID) ? this.TYPE_ITEM_DISCOUNT : this.TYPE_CUSTOM_DISCOUNT;

			if (this.type.value == this.TYPE_CUSTOM_DISCOUNT)
			{
				this.addCondition(this.action.ActionCondition);
			}
		}
		else
		{
			this.type.value = this.TYPE_ORDER_DISCOUNT;
		}

		this.isEnabled.checked = this.action.isEnabled == 1;
		this.isOrderLevel.checked = this.action.isOrderLevel == 1;
	},

	changeType: function()
	{
		if (this.type.value != this.TYPE_CUSTOM_DISCOUNT)
		{
			this.subConditionContainer.down('ul.conditionContainer').innerHTML = '';
			this.subConditionContainer.hide();
		}
		else
		{
			this.subConditionContainer.show();
		}
	},

	changediscountType: function()
	{
		this.classFields.invoke('hide');
		this.percentSign.hide();
		this.currencySign.hide();
		this.applyTo.hide();

		if (this.isPercent() || this.isAmount())
		{
			this.amountFields.show();
		}
		else
		{
			this.amountFields.hide();
		}

		if (this.isPercent())
		{
			this.percentSign.show();
		}
		else
		{
			this.currencySign.show();
		}

		this.classFields.each(function(container)
		{
			if (container.hasClassName(this.actionClass.value))
			{
				container.show();
			}
		}.bind(this));

		if (Backend.Discount.Action.prototype.itemActions[this.actionClass.value])
		{
			this.applyTo.show();
		}
	},

	isPercent: function()
	{
		return ['RuleActionPercentageDiscount', 'RuleActionPercentageSurcharge'].indexOf(this.actionClass.value) > -1;
	},

	isAmount: function()
	{
		return ['RuleActionFixedDiscount', 'RuleActionFixedSurcharge'].indexOf(this.actionClass.value) > -1;
	},

	addAction: function(e, id)
	{
		var element = Event.element(e);
		Event.stop(e);
		new LiveCart.AjaxRequest(Backend.Router.createUrl(this.controller, 'addAction', {id: id}), element.parentNode.down('.progressIndicator'), this.completeAdd.bind(this));
	},

	completeAdd: function(originalRequest)
	{
		var instance = new Backend.Discount.Action(originalRequest.responseData, $('actionContainer_' + originalRequest.responseData.Condition.ID));
		var list = instance.initializeList();
		list.decorateItems();
		list.createSortable(true);
	},

	createNode: function()
	{
		var el = this.template.cloneNode(true);
		el.id = 'discountAction_' + this.action.ID;
		this.container.appendChild(el);
		this.node = el;

		this.findUsedNodes();

		var recordClassName = '';

		this.setValues();
		this.bindEvents();
		this.changeType(true);
		this.changediscountType();
	},

	saveFieldChange: function(e)
	{
		var field = Event.element(e);

		if (field == this.productFieldSel)
		{
			field = this.amount;
		}

		$(field.parentNode).addClassName('fieldUpdating');

		var value = field.value;
		if ('checkbox' == field.type)
		{
			value = field.checked ? 1 : 0;
		}

		new LiveCart.AjaxRequest(Backend.Router.createUrl(this.controller, 'updateActionField', {type: this.actionClass.value, field: field.name, value: value, isParam: field.hasClassName('actionField')}), null, this.completeUpdateField.bind(this));
	},

	addCondition: function(condition)
	{
		var instance = new Backend.Discount.Condition(condition, [], [], this.subConditionContainer);
		if (instance.typeSel.value != instance.TYPE_ITEMS)
		{
			instance.typeSel.value = instance.TYPE_ITEMS;
			instance.changeType();
		}
		//this.subConditionContainer.down('ul.menu').down('a').onclick = instance.createSubCondition.bind(instance);
	},

	completeUpdateField: function(originalRequest)
	{
		if (typeof originalRequest.responseData == 'object')
		{
			var field = originalRequest.responseData.field;
			this.addCondition(originalRequest.responseData.condition);
		}
		else
		{
			var field = originalRequest.responseData;
		}

		field = this[field] ? this[field] : field;

		$(field).parentNode.removeClassName('fieldUpdating');
	}
}