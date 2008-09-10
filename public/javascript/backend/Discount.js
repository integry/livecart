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
	}
}

Backend.Discount.Editor.inheritsFrom(Backend.MultiInstanceEditor);

Backend.Discount.Condition = function(tree, records, container)
{
	this.condition = tree;
	this.container = container;
	this.records = records;

	if (!this.namespace.prototype.template)
	{
		this.namespace.prototype.template = $('conditionTemplate').down('li')
	}

	if (!this.namespace.prototype.recordTemplate)
	{
		this.namespace.prototype.recordTemplate = $('recordTemplate').down('li')
	}

	if (!this.namespace.prototype.selectRecordTemplate)
	{
		this.namespace.prototype.selectRecordTemplate = $('selectRecordTemplate').down('li')
	}

	this.createNode();
}

Backend.Discount.Condition.prototype =
{
	namespace: Backend.Discount.Condition,

	node: null,

	TYPE_TOTAL: 0,
	TYPE_COUNT: 1,
	TYPE_ITEMS: 2,
	TYPE_USERGROUP: 3,
	TYPE_USER: 4,
	TYPE_DELIVERYZONE: 5,

	findUsedNodes: function()
	{
		this.typeSel = this.node.down('.conditionType');
		this.compSel = this.node.down('.comparisonType');
		this.valueField = this.node.down('.comparisonValue');
		this.subCondition = this.node.down('.subCondition');
		this.subConditionContainer = this.node.down('.conditionContainer');
		this.isAllSubconditions = this.node.down('.isAllSubconditions');
		this.isAnyRecord = this.node.down('.isAnyRecord');
		this.deleteIcon = this.node.down('.conditionDelete');
		this.recordContainer = this.node.down('.recordContainer');
		this.selectRecordContainer = this.node.down('.selectRecordContainer');
		this.productFieldSel = this.node.down('.comparisonField');
	},

	bindEvents: function()
	{
		[this.compSel, this.valueField, this.isAllSubconditions, this.isAnyRecord, this.productFieldSel].each(function(field)
		{
			field.name += '_' + this.condition.ID;
			Event.observe(field, 'change', this.saveFieldChange.bind(this));
		}.bind(this));

		this.isAllSubconditions.id += '_' + this.condition.ID;
		$(this.isAllSubconditions.parentNode).down('label').setAttribute('for', 'isAllSubconditions_' + this.condition.ID);

		this.isAnyRecord.id += '_' + this.condition.ID;
		$(this.isAnyRecord.parentNode).down('label').setAttribute('for', 'isAnyRecord_' + this.condition.ID);

		Event.observe(this.subCondition, 'click', this.createSubCondition.bind(this));
		Event.observe(this.deleteIcon, 'click', this.remove.bind(this));

		Event.observe(this.typeSel, 'change', this.changeType.bind(this));

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

		// value is reversed in user interface
		this.isAnyRecord.checked = this.condition.isAnyRecord == 0;
	},

	createNode: function()
	{
		var el = this.template.cloneNode(true);
		this.container.appendChild(el);
		this.node = el;

		this.findUsedNodes();

		var recordClassName = '';

		// determine condition type
		if (this.condition.count != null && this.condition.recordCount == null)
		{
			this.typeSel.value = this.TYPE_COUNT;
		}
		else if (this.condition.subTotal != null && this.condition.recordCount == null)
		{
			this.typeSel.value = this.TYPE_TOTAL;
		}
		else if (this.condition.recordCount > 0)
		{
			this.condition.records.each(function(record)
			{
				var rec = this.createRecord(record);
				recordClassName = rec.data.className;
			}.bind(this));

			this.typeSel.value = {Category: this.TYPE_ITEMS, Product: this.TYPE_ITEMS, Manufacturer: this.TYPE_ITEMS, User: this.TYPE_USER, UserGroup: this.TYPE_USERGROUP, DeliveryZone: this.TYPE_DELIVERYZONE}[recordClassName];
		}

		this.setValues();
		this.bindEvents();

		if (this.condition.sub)
		{
			this.condition.sub.each(function(sub) { new this.namespace(sub, this.records, this.subConditionContainer); }.bind(this));
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

		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.discount', 'updateConditionField', {type: this.typeSel.value, field: field.name, productField: this.productFieldSel.value, value: value}), null, this.completeUpdateField.bind(this));
	},

	completeUpdateField: function(originalRequest)
	{
		var updated = originalRequest.responseData;
		var field = updated == 'comparisonValue' ? this.valueField : this.compSel;
		if ('isAllSubconditions' == updated)
		{
			field = this.isAllSubconditions;
		}
		if ('isAnyRecord' == updated)
		{
			field = this.isAnyRecord;
		}

		$(field.parentNode).removeClassName('fieldUpdating');
	},

	createSubCondition: function(e)
	{
		var element = Event.element(e);
		Event.stop(e);
		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.discount', 'addCondition', {id: this.condition.ID}), element.parentNode.down('.progressIndicator'), this.completeAdd.bind(this));
	},

	completeAdd: function(originalRequest)
	{
		var cond = new this.namespace(originalRequest.responseData, this.records, this.subConditionContainer);
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
			new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.discount', 'deleteCondition', {id: this.condition.ID}), this.deleteIcon.parentNode.down('.progressIndicator'), this.completeDelete.bind(this));
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

		if (type == this.TYPE_COUNT)
		{
			this.valueField.value = this.condition.count;
		}
		else if (type == this.TYPE_TOTAL)
		{
			this.valueField.value = this.condition.subTotal;
		}

		if (type > this.TYPE_ITEMS)
		{
			this.compSel.hide();
			this.valueField.hide();
			this.recordContainer.show();
		}
		else
		{
			this.compSel.show();
			this.valueField.show();
			this.recordContainer.show();
		}

		if (type == this.TYPE_ITEMS)
		{
			this.recordContainer.show();
		}

		var recordClass = '';
		if (type == this.TYPE_DELIVERYZONE)
		{
			recordClass = 'DeliveryZone';
		}
		else if (type == this.TYPE_USERGROUP)
		{
			recordClass = 'UserGroup';
		}

		if (recordClass)
		{
			this.selectRecordContainer.down('ul').innerHTML = '';
			for (k = 0; k < this.records[recordClass].length; k++)
			{
				var record = this.records[recordClass][k];
				var instance = this.createSelectRecord(recordClass, record);
			}
		}

		this.node.className = 'type_' + type;
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

		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.discount', 'saveSelectRecord', {id: this.condition.ID, class: inp.data.className, recordID: inp.data.ID, state: inp.checked}), null, function (originalRequest) { this.completeSaveSelectRecord(originalRequest, inp); }.bind(this));

		inp.parentNode.addClassName('selectRecordUpdating');
	},

	completeSaveSelectRecord: function(originalRequest, el)
	{
		el.parentNode.removeClassName('selectRecordUpdating');
	},

	addRecord: function(className, id, onComplete)
	{
		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.discount', 'addRecord', {id: this.condition.ID, class: className, recordID: id}), null, function (originalRequest) { this.completeAddRecord(originalRequest, onComplete); }.bind(this));
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
			if (key != 'ID' && key != 'Condition')
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
		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.discount', 'deleteRecord', {id: li.data.ID}), element.parentNode.down('.progressIndicator'), this.completeDeleteRecord.bind(li));
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

	MEASURE_PERCENT: 0,
	MEASURE_AMOUNT: 1,

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
		this.amountMeasure = this.node.down('.actionType');
		this.amount = this.node.down('.comparisonValue');
		this.type = this.node.down('.applyTo');
		this.isEnabled = this.node.down('.isEnabled');
		this.subConditionContainer = this.node.down('.conditionContainer');

		this.percentSign = this.node.down('.percent');
		this.currencySign = this.node.down('.currency');
	},

	bindEvents: function()
	{
		[this.amountMeasure, this.amount, this.type, this.isEnabled].each(function(field)
		{
			field.name += '_' + this.action.ID;
			Event.observe(field, 'change', this.saveFieldChange.bind(this));
		}.bind(this));

		Event.observe(this.addAction, 'click', this.addAction.bind(this));

		this.isEnabled.id = 'isEnabled_' + this.action.ID;
		$(this.isEnabled.parentNode).down('label').setAttribute('for', 'isEnabled_' + this.action.ID);

		Event.observe(this.type, 'change', this.changeType.bind(this));
		Event.observe(this.amountMeasure, 'change', this.changeAmountMeasure.bind(this));
	},

	setValues: function()
	{
		this.amountMeasure.value = this.action.amountMeasure;
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

	changeAmountMeasure: function()
	{
		this.percentSign.hide();
		this.currencySign.hide();

		if (this.amountMeasure.value == this.MEASURE_PERCENT)
		{
			this.percentSign.show();
		}
		else
		{
			this.currencySign.show();
		}
	},

	addAction: function(e, id)
	{
		var element = Event.element(e);
		Event.stop(e);
		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.discount', 'addAction', {id: id}), element.parentNode.down('.progressIndicator'), this.completeAdd.bind(this));
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
		this.changeAmountMeasure();
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

		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.discount', 'updateActionField', {type: this.amountMeasure.value, field: field.name, value: value}), null, this.completeUpdateField.bind(this));
	},

	addCondition: function(condition)
	{
		var instance = new Backend.Discount.Condition(condition, [], this.subConditionContainer);
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

		$(this[field]).parentNode.removeClassName('fieldUpdating');
	}
}