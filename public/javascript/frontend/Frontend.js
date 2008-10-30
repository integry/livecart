/**
 *	@author Integry Systems
 */

ConfirmationMessage = Class.create();
ConfirmationMessage.prototype =
{
	initialize: function(parent, message)
	{
		var div = document.createElement('div');
		div.className = 'confirmationMessage';
		div.innerHTML = message;

		parent.appendChild(div);
		new Effect.Highlight(div, { duration: 0.4 });
	}
}

/*****************************
	Product related JS
*****************************/
Product = {}

Product.ImageHandler = Class.create();
Product.ImageHandler.prototype =
{
	initialize: function(imageData, imageDescr)
	{
		imageData.each(function(pair)
		{
			if ($('img_' + pair.key))
			{
				new Product.ImageSwitcher(pair.key, pair.value, imageDescr[pair.key]);
			}
		});
	}
}

Product.ImageSwitcher = Class.create();
Product.ImageSwitcher.prototype =
{
	id: 0,

	imageData: null,
	imageDescr: null,

	initialize: function(id, imageData, imageDescr)
	{
		this.id = id;
		this.imageData = imageData;
		this.imageDescr = imageDescr;

		$('img_' + id).onclick = this.switchImage.bind(this);
	},

	switchImage: function()
	{
		$('mainImage').src = this.imageData[3];

		if ($('imageDescr'))
		{
			$('imageDescr').innerHTML = this.imageDescr;
		}

		var lightBox = $('largeImage').down('a');
		lightBox.href = this.imageData[4];
		lightBox.title = this.imageDescr ? this.imageDescr : '';
	}
}

Product.Rating = Class.create();
Product.Rating.prototype =
{
	form: null,

	initialize: function(form)
	{
		this.form = form;
		new LiveCart.AjaxRequest(form, null, this.complete.bind(this));
	},

	complete: function(req)
	{
		var response = req.responseData;
		if(response.status == 'success')
		{
			var parent = this.form.parentNode;
			parent.removeChild(this.form);
			new ConfirmationMessage(parent, response.message);
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.form, response.errors);
		}
	},

	updatePreview: function(e)
	{
		var input = Event.element(e);
		var tr = input.up('tr');
		if (!tr)
		{
			return false;
		}

		var hasError = input.up('tr').down('.hasError');
		if (hasError)
		{
			ActiveForm.prototype.resetErrorMessage(hasError);
		}

		var preview = tr.down('.ratingPreview').down('img');
		preview.src = 'image/rating/' + input.value + '.gif';
		preview.show();
	}
}

Product.ContactForm = Class.create();
Product.ContactForm.prototype =
{
	form: null,

	initialize: function(form)
	{
		this.form = form;
		new LiveCart.AjaxRequest(form, null, this.complete.bind(this));
	},

	complete: function(req)
	{
		var response = req.responseData;
		if(response.status == 'success')
		{
			var parent = this.form.parentNode;
			parent.removeChild(this.form);
			new ConfirmationMessage(parent, response.message);
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.form, response.errors);
		}
	}
}

// lightbox changes
window.setTimeout(function()
{
	if (!window.showLightbox)
	{
		return false;
	}

	var oldShowLightbox = window.showLightbox;
	var oldHideLightbox = window.hideLightbox;

	window.showLightbox = function()
	{
		oldShowLightbox.apply(this, arguments);
		Element.addClassName(document.body, 'lightboxOn');
	}

	window.hideLightbox = function()
	{
		oldHideLightbox.apply(this, arguments);
		Element.removeClassName(document.body, 'lightboxOn');
	}
}, 2000);

/*****************************
	Order related JS
*****************************/
Order = {}

Order.OptionLoader = Class.create();
Order.OptionLoader.prototype =
{
	initialize: function(container)
	{
		container = $(container)
		if (!container)
		{
			return false;
		}

		$A(container.getElementsByClassName('productOptionsMenu')).each(
			function(cont)
			{
				Event.observe(cont.down('a'), 'click', this.loadForm.bind(this));
			}.bind(this)
		);
	},

	loadForm: function(e)
	{
		var a = Event.element(e);
		a.addClassName('ajaxIndicator');
		new LiveCart.AjaxUpdater(a.attributes.getNamedItem('ajax').nodeValue, a.up('.productOptions'));
		Event.stop(e);
	}
}

Order.submitCartForm = function(anchor)
{
	var form = anchor.up('form');

	if (validateForm(form))
	{
		var redirect = document.createElement('input');
		redirect.type = 'hidden';
		redirect.name = 'proceed';
		redirect.value = 'true';
		form.appendChild(redirect);

		form.submit();
	}

	return false;
}

Order.AddressSelector = function(form)
{
	var newAddressToggle = function(radioButton)
	{
		if (!radioButton)
		{
			return;
		}

		var addressForm = $(radioButton).up('tr').down('td.address').down('.address');

		radioButton.form.onchange = function()
		{
			if (radioButton.checked)
			{
				addressForm.show();
			}
			else
			{
				addressForm.hide();
			}
		};

		radioButton.form.onclick = radioButton.form.onchange;

		radioButton.form.onchange();
	}

	form = $(form);
	new newAddressToggle(form.down('#billing_new'));
	new newAddressToggle(form.down('#shipping_new'));
}

/*****************************
	User related JS
*****************************/
User = {}

User.StateSwitcher = Class.create();
User.StateSwitcher.prototype =
{
	countrySelector: null,
	stateSelector: null,
	stateTextInput: null,
	url: '',

	initialize: function(countrySelector, stateSelector, stateTextInput, url)
	{
		this.countrySelector = countrySelector;
		this.stateSelector = stateSelector;
		this.stateTextInput = stateTextInput;
		this.url = url;

		if (this.stateSelector.length > 0)
		{
			Element.show(this.stateSelector);
			Element.hide(this.stateTextInput);
		}

		Event.observe(countrySelector, 'change', this.updateStates.bind(this));
	},

	updateStates: function(e)
	{
		var url = this.url + '/?country=' + this.countrySelector.value;
		new Ajax.Request(url, {onComplete: this.updateStatesComplete.bind(this)});

		var indicator = document.getElementsByClassName('progressIndicator', this.countrySelector.parentNode);
		if (indicator.length > 0)
		{
			this.indicator = indicator[0];
			Element.show(this.indicator);
		}

		this.stateSelector.length = 0;
		this.stateTextInput.value = '';
	},

	updateStatesComplete: function(ajaxRequest)
	{
		eval('var states = ' + ajaxRequest.responseText);

		if (0 == states.length)
		{
			Element.hide(this.stateSelector);
			Element.show(this.stateTextInput);
			this.stateTextInput.focus();
		}
		else
		{
			this.stateSelector.options[this.stateSelector.length] = new Option('', '', true);

			Object.keys(states).each(function(key)
			{
				if (!isNaN(parseInt(key)))
				{
					this.stateSelector.options[this.stateSelector.length] = new Option(states[key], key, false);
				}
			}.bind(this));
			Element.show(this.stateSelector);
			Element.hide(this.stateTextInput);

			this.stateSelector.focus();
		}

		if (this.indicator)
		{
			Element.hide(this.indicator);
		}
	}
}

User.ShippingFormToggler = Class.create();
User.ShippingFormToggler.prototype =
{
	checkbox: null,
	container: null,

	initialize: function(checkbox, container)
	{
		this.checkbox = checkbox;
		this.container = container;

		if (this.checkbox)
		{
			Event.observe(this.checkbox, 'change', this.handleChange.bindAsEventListener(this));
			Event.observe(this.checkbox, 'click', this.handleChange.bindAsEventListener(this));

			this.handleChange(null);
		}
	},

	handleChange: function(e)
	{
		if (this.checkbox.checked)
		{
			Element.hide(this.container);
		}
		else
		{
			Element.show(this.container);
		}
	}
}