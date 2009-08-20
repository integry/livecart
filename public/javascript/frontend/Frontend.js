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
			var inst = new Product.ImageSwitcher(pair.key, pair.value, imageDescr[pair.key]);
			if (!window.defaultImageHandler)
			{
				window.defaultImageHandler = inst;
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

		var thumbnail = $('img_' + id);
		if (thumbnail)
		{
			thumbnail.onclick = this.switchImage.bind(this);
		}
	},

	switchImage: function()
	{
		if (!$('mainImage'))
		{
			return false;
		}

		$('mainImage').src = this.imageData[3];

		if ($('imageDescr'))
		{
			$('imageDescr').innerHTML = this.imageDescr;
		}

		var lightBox = $('largeImage').down('a');
		if (lightBox)
		{
			lightBox.href = this.imageData[4];
			lightBox.title = this.imageDescr ? this.imageDescr : '';
		}
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

Product.Variations = function(container, variations, options)
{
	this.container = container;
	this.form = container.up('form');
	this.variations = variations;
	this.selectFields = [];
	this.options = options;

	if ($('productPrice'))
	{
		this.priceContainer = $('productPrice').down('.price').down('.realPrice');
		this.defaultPrice = this.priceContainer.innerHTML;
	}

	$H(this.variations.variations).each(function(value)
	{
		var type = value[1];
		var field = this.form.elements.namedItem('variation_' + type['ID']);
		field.variationIndex = value[0];
		this.selectFields.push(field);
		field.disabled = true;

		field.onchange = this.updateVisibleOptions.bind(this);
	}.bind(this));

	this.combinations = {};
	$H(this.variations.products).each(function(value)
	{
		var root = this.combinations;
		value[0].split(/-/).each(function(id)
		{
			if (!root[id])
			{
				root[id] = {};
			}

			root = root[id];
		});
	}.bind(this));

	this.selectFields[0].disabled = false;
	this.updateVisibleOptions();
}

Product.Variations.prototype =
{
	updateVisibleOptions: function(e)
	{
		var disable = false;
		var root = this.combinations;
		var k = -1;
		var selectedVariation = [];

		this.selectFields.each(function(field)
		{
			k++;
			if (disable)
			{
				field.value = '';
				field.disabled = true;
			}
			else
			{
				field.disabled = false;
				var nextField = this.selectFields[k + 1];
				var value = field.value;

				if (value)
				{
					selectedVariation.push(value);
				}

				if (!root[value])
				{
					field.value = '';
				}
				else if (nextField)
				{
					$A(nextField.options).each(function(opt)
					{
						opt.style.display = (root[value][opt.value] || !opt.value) ? '' : 'none';
					});

					root = root[value];
				}
			}

			if (!field.value)
			{
				disable = true;
			}
		}.bind(this));

		if (!disable)
		{
			this.displayVariationInfo(this.variations.products[selectedVariation.join('-')]);
		}
		else
		{
			this.showDefaultInfo();
		}

		/* display variation prices if enough options are selected */
		var variationCount = $A($H(this.variations.variations)).length;
		if (selectedVariation.length == variationCount)
		{
			selectedVariation.pop();
		}

		if (selectedVariation.length == (variationCount - 1))
		{
			$A(this.selectFields[variationCount - 1].options).each(function(opt)
			{
				if (opt.value)
				{
					if (!this.variationOptionTemplate)
					{
						this.variationOptionTemplate = $('variationOptionTemplate').innerHTML;
					}

					if (!opt.originalText)
					{
						opt.originalText = opt.innerHTML;
					}

					var variations = selectedVariation.slice(0);
					variations.push(opt.value);
					var product = this.variations.products[variations.join('-')];

					if (product)
					{
						if (this.isPriceChanged(product))
						{
							var text = this.variationOptionTemplate.replace(/%price/, this.getProductPrice(product)).replace(/%name/, opt.originalText);
						}
						else
						{
							var text = opt.originalText;
						}

						opt.innerHTML = text;
					}
					else
					{
						opt.innerHTML = opt.originalText;
					}
				}
			}.bind(this));
		}
	},

	displayVariationInfo: function(product)
	{
		this.showDefaultInfo();

		if (this.isPriceChanged(product))
		{
			this.updatePrice(this.getProductPrice(product));
		}

		if (product.DefaultImage && product.DefaultImage.paths)
		{
			(new Product.ImageSwitcher(product.DefaultImage.ID, product.DefaultImage.paths, product.DefaultImage.name_lang)).switchImage();
		}
	},

	getProductPrice: function(product)
	{
		return (product.formattedPrice && product.formattedPrice[this.options.currency]) ? product.formattedPrice[this.options.currency] : this.defaultPrice;
	},

	isPriceChanged: function(product)
	{
		return parseInt(product['price_' + this.options.currency]) > 0;
	},

	updatePrice: function(price)
	{
		if (this.priceContainer)
		{
			this.priceContainer.innerHTML = price ? price : this.defaultPrice;
		}
	},

	showDefaultInfo: function()
	{
		this.showDefaultImage();
		this.updatePrice(null);
	},

	showDefaultImage: function()
	{
		if (window.defaultImageHandler)
		{
			window.defaultImageHandler.switchImage();
		}
	}
}

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

		onchange = function()
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

		Event.observe(radioButton.form, 'change', onchange);
		Event.observe(radioButton.form, 'click', onchange);

		onchange();
	}

	form = $(form);
	newAddressToggle(form.down('#billing_new'));
	newAddressToggle(form.down('#shipping_new'));
}

/*****************************
	Product comparison
*****************************/
Compare = {}

Compare.add = function(e)
{
	Event.stop(e);
	var el = Event.element(e);
	el.addClassName('progressIndicator');
	new LiveCart.AjaxRequest(el.href, null, function(oR) { Compare.addComplete(oR, el); });
}

Compare.addComplete = function(origReq, el)
{
	el.blur();
	new Effect.Highlight(el, { duration: 0.4 });
	el.removeClassName('progressIndicator');
	var menu = $('compareMenu');

	if (!menu)
	{
		$('compareMenuContainer').update(origReq.responseText);
		var menu = $('compareMenu');
	}
	else
	{
		menu.down('ul').innerHTML += origReq.responseText;
	}

	new Compare.Menu(menu);
}

Compare.Menu = function(container)
{
	this.container = container;
	this.initEvents();
}

Compare.Menu.prototype =
{
	initEvents: function()
	{
		$A(document.getElementsByClassName('delete', this.container)).each(function(el)
		{
			el.onclick = function(e) { this.removeProduct(e, el) }.bind(this);
		}.bind(this));
	},

	removeProduct: function(e, el)
	{
		Event.stop(e);
		var li = el.up('li');
		el.addClassName('progressIndicator');
		new LiveCart.AjaxRequest(el.href, null, function() { this.removeComplete(li) }.bind(this));
	},

	removeComplete: function(li)
	{
		if (li.parentNode.getElementsByTagName('li').length < 2)
		{
			this.container.parentNode.removeChild(this.container);
		}
		else
		{
			li.parentNode.removeChild(li);
		}
	}
}

/*****************************
	Product filters
*****************************/
Filter = {}

Filter.SelectorMenu = function(container, isPageReload)
{
	$A(container.getElementsByTagName('select')).each(function(el)
	{
		el.onchange = function()
		{
			if (!isPageReload)
			{
				new LiveCart.AjaxUpdater(this.value, container, null, null,
					function()
					{
						container.parentNode.replaceChild(container.down('div'), container);
					});
			}
			else
			{
				window.location.href = this.value;
			}
		}
	});
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

Frontend = {}

Frontend.PopupCart =
{

}

Frontend.AddToCartPopup =
{

}

Frontend.OnePageCheckout = function(url)
{
	this.data = {};
	this.load(url);
}

Frontend.OnePageCheckout.prototype =
{
	load: function()
	{
		new LiveCart.AjaxRequest(url, null, this.loaded.bind(this));
	},

	loaded: function(originalRequest)
	{
		console.log(originalRequest);
		this.show(originalRequest.responseData.html);
	},

	show: function(html)
	{
		var container = this.getContainer();
		container.update(html);
		container.show();
	},

	showNavigation: function()
	{

	},

	hideNavigation: function()
	{

	},

	loadStep: function()
	{

	},

	switchStep: function()
	{

	},

	close: function()
	{
		$('onePageCheckout').hide();
	},

	complete: function()
	{

	},

	getContainer: function()
	{
		var container = $('onePageCheckout');
		if (!container)
		{
			container = document.createElement('div');
			document.body.appendChild(container);
		}

		return container;
	}
}

Frontend.SmallCart = function(value, params)
{
	var container = $(params);
	var basketCount = container.down('.menu_cartItemCount');
	if (basketCount)
	{
		(basketCount.down('strong') || basketCount.down('span')).update(value.basketCount);
		if (value.basketCount > 0)
		{
			basketCount.show();
		}
		else
		{
			basketCount.hide();
		}
	}

	var isOrderable = container.down('.menu_isOrderable');
	if (isOrderable)
	{
		if (value.isOrderable)
		{
			isOrderable.show();
		}
		else
		{
			isOrderable.hide();
		}
	}
}

Frontend.MiniCart = function(value, params)
{
	$(params).update(value);
	var fc = $(params).down('#miniCart');
	$(params).parentNode.replaceChild(fc, $(params));
	new Effect.Highlight(fc);
}

Frontend.Message = function(value, params)
{
	var container = $(params);

}

Frontend.Message.root = document.body;

Frontend.Ajax = {}

Frontend.Ajax.Message = function(container)
{
	var showMessage = function(value, container)
	{
		container.update(value);
		new Effect.Appear(msgContainer);
		window.setTimeout(function() { new Effect.Fade(msgContainer); }, 5000);
	}

	var msgContainer = $(document.createElement('div'));
	msgContainer.hide();
	msgContainer.id = 'ajaxMessage';
	msgContainer.className = 'confirmationMessage';
	$('container').appendChild(msgContainer);
	Observer.add('successMessage', showMessage, msgContainer);
}

Frontend.Ajax.AddToCart = function(container)
{
	var handleClick = function(e)
	{
		Event.stop(e);
		var button = Event.element(e);
		var a = document.createElement('a');
		button.parentNode.appendChild(a);
		new LiveCart.AjaxRequest(button.href, a, function () { a.parentNode.removeChild(a); new Effect.Highlight(button); });
	}

	$A($(container).getElementsBySelector('a.addToCart')).each(function(button)
	{
		Event.observe(button, 'click', handleClick);
	});
}

Frontend.Ajax.AddToWishList = function(container)
{
	var handleClick = function(e)
	{
		Event.stop(e);
		var a = Event.element(e);
		new LiveCart.AjaxRequest(a.href, a, function () { new Effect.Highlight(a); });
	}

	$A($(container).getElementsBySelector('a.addToWishList')).each(function(button)
	{
		Event.observe(button, 'click', handleClick);
	});

	$A($(container).getElementsBySelector('td.addToWishList a')).each(function(button)
	{
		Event.observe(button, 'click', handleClick);
	});
}

Frontend.Ajax.AddToCompare = function(container)
{
	$A($(container).getElementsBySelector('a.addToCompare')).each(function(button)
	{
		Event.observe(button, 'click', Compare.add);
	});
}

Frontend.AjaxInit = function(container)
{
	$H(Frontend.Ajax).each(function(v)
	{
		new Frontend.Ajax[v[0]](container);
	});
}