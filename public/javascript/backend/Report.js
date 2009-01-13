/**
 *	@author Integry Systems
 */

function open_flash_chart_data()
{
	return Object.toJSON(window.report.getData());
}

Backend.Report = {}

Backend.Report.Controller = function()
{
	this.options = {};

	this.findUsedNodes();

	this.periodInstance = new Backend.Report.Period(this, $('reportDateRange'));
	this.selectorInstance = new Backend.Report.TypeSelector(this, $('reportTypeSelector'));

	this.setReportType(this.selectorInstance.getType());
}

Backend.Report.Controller.prototype =
{
	findUsedNodes: function()
	{
		this.loadIndicator = $('reportIndicator');
		this.reportContainer = $('reportContent');
	},

	notifyDateChange: function(period)
	{
		this.period = period;
		this.loadReport();
	},

	setReportType: function(type)
	{
		if (this.type)
		{
			this.type.removeClassName('active');
		}

		this.type = type;
		this.type.addClassName('active');
		this.loadReport();
	},

	loadReport: function()
	{
		if (!this.type || !this.period)
		{
			return;
		}

		var params = 'date=' + this.period + '&options=' + Object.toJSON(this.options);
		new LiveCart.AjaxUpdater(this.type.down('a').href, this.reportContainer, this.loadIndicator, null, this.reportLoaded.bind(this), {parameters: params});
	},

	reportLoaded: function(originalRequest)
	{
		var menu = this.reportContainer.down('.chartMenu');
		if (!menu)
		{
			return;
		}

		var option = menu.id.substr(5);

		// report sub-type selector links
		$A(menu.getElementsByTagName('a')).each(function(a)
		{
			a.onclick = function(e)
			{
				Event.stop(e);
				this.setOption(option, a.id);
			}.bind(this);
		}.bind(this));

		// more report subtypes from drop-down
		var moreTypes = this.reportContainer.down('.moreTypes');
		if (moreTypes)
		{
			moreTypes.onchange = function()
			{
				if (moreTypes.value)
				{
					this.setOption(option, moreTypes.value);
				}
			}.bind(this);
		}

		// report interval selector
		var interval = menu.down('.intervalSelect');
		if (interval)
		{
			interval.value = this.options.interval;
			interval.onchange = function() { this.setInterval(interval.value) }.bind(this);
		}
	},

	setOption: function(option, value)
	{
		this.options[option] = value;
		this.loadReport();
	},

	setInterval: function(value)
	{
		this.setOption('interval', value);
	},

	setData: function(data)
	{
		this.data = data;
	},

	getData: function(par)
	{
		return this.data;
	},

	setActiveMenu: function(menu)
	{
		if ($(menu))
		{
			$(menu).addClassName('active');
		}
		else
		{
			var moreTypes = this.reportContainer.down('.moreTypes');
			if (moreTypes)
			{
				moreTypes.value = menu;
			}
		}
	}
}

Backend.Report.TypeSelector = function(controller, container)
{
	this.controller = controller;
	this.container = container;

	this.findUsedNodes();
	this.bindEvents();

	this.type = this.container.getElementsByTagName('li')[0];
}

Backend.Report.TypeSelector.prototype =
{
	findUsedNodes: function()
	{

	},

	bindEvents: function()
	{
		$A(this.container.getElementsByTagName('li')).each(function(li)
		{
			li.onclick = this.typeChanged.bindAsEventListener(this);
		}.bind(this));
	},

	typeChanged: function(e)
	{
		Event.stop(e);

		var el = Event.element(e);
		if (el.tagName != 'LI')
		{
			el = el.up('li');
		}

		this.type = el;

		this.controller.setReportType(this.type);
	},

	getType: function()
	{
		return this.type;
	}
}

Backend.Report.Period = function(controller, container)
{
	this.controller = controller;
	this.container = container;

	this.findUsedNodes();
	this.bindEvents();

	this.period = this.getPeriod();
}

Backend.Report.Period.prototype =
{
	findUsedNodes: function()
	{
		this.dateSelector = this.container.down('.reportDateSelector');
	},

	bindEvents: function()
	{
		this.dateSelector.onchange = this.periodChanged.bind(this);
	},

	periodChanged: function()
	{
		this.period = this.dateSelector.value;
		this.controller.notifyDateChange(this.period);
	},

	getPeriod: function()
	{
		if (!this.period)
		{
			this.periodChanged();
		}

		return this.period;
	}
}