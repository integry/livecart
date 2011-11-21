var jQ = jQuery;

/**
 *	@author Integry Systems
 */

ConfirmationMessage = Class.create();
ConfirmationMessage.prototype =
{
	initialize: function(parent, message)
	{
		new DomMessage(parent, message, 'confirmationMessage');
	}
}

ErrorMessage = Class.create();
ErrorMessage.prototype =
{
	initialize: function(parent, message)
	{
		new DomMessage(parent, message, 'errorMessage');
	}
}

DomMessage = Class.create();
DomMessage.prototype =
{
	initialize: function(parent, message, className)
	{
		var div = document.createElement('div');
		div.className = className;
		div.innerHTML = message;

		parent.appendChild(div);
		new Effect.Highlight(div, { duration: 0.4 });

		return div;
	}
}


/*****************************
	Product related JS
*****************************/
Product = {}

Product.ImageHandler = Class.create();
Product.ImageHandler.prototype =
{
	initialize: function(imageData, imageDescr, imageProducts, enlargeOnMouseOver)
	{
		if (!imageProducts)
		{
			imageProducts = [];
		}

		imageData.each(function(pair)
		{
			var inst = new Product.ImageSwitcher(pair.key, pair.value, imageDescr[pair.key], imageProducts[pair.key], enlargeOnMouseOver);
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

	initialize: function(id, imageData, imageDescr, productID, enlargeOnMouseOver)
	{
		this.id = id;
		this.productID = productID;
		this.imageData = imageData;
		this.imageDescr = imageDescr;

		var thumbnail = $('img_' + id);
		if (thumbnail)
		{
			if(enlargeOnMouseOver)
			{
				thumbnail.onmouseover = this.switchImage.bindAsEventListener(this);
				thumbnail.onclick =
					function(e)
					{
						this.switchImage(e);
						sendEvent($('mainImage'), 'click');
					}.bindAsEventListener(this);
			}
			else
			{
				thumbnail.onclick = this.switchImage.bindAsEventListener(this);
			}
		}
	},

	switchImage: function(e)
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

		if (window.productVariationHandler && e)
		{
			window.productVariationHandler.setVariation(this.productID);
		}
	}
}

Product.Lightbox2Gallery =
{
	start : function(a)
	{
		var
			lightboxATag;

		lightboxATag = $A(document.getElementsByTagName("a")).find(function(a) {
			return  a.rel && a.href == this.a.href;
		}.bind({a:a}));
		if(lightboxATag)
		{
			$(lightboxATag).simulate('click');
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

Product.Share = Class.create();
Product.Share.prototype =
{
	form: null,

	initialize: function(form)
	{
		this.form = $(form);
		var progressIndicator = this.form.down(".pi");
		progressIndicator.addClassName("progressIndicator");
		new LiveCart.AjaxRequest(form, progressIndicator, this.complete.bind(this));
	},

	complete: function(req)
	{
		try {
			var response = req.responseData;
			if (typeof response.message == "undefined")
			{
				new ErrorMessage($("sendToFriendRepsonse"), _error_cannot_send_to_friend);
				return;
			}
			if(response.status == 'success')
			{
				new ConfirmationMessage($("sendToFriendRepsonse"), response.message);
				this.form.reset();
			}
			else
			{
				new ErrorMessage($("sendToFriendRepsonse"), response.message);
			}
		} catch(e)
		{
			new ErrorMessage($("sendToFriendRepsonse"), _error_cannot_send_to_friend);
		}
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

	window.productVariationHandler = this;
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
					if (!nextField.originalOptions)
					{
						nextField.originalOptions = $A(nextField.options);
					}

					var index = nextField.selectedIndex;
					$A(nextField.options).each(function(opt)
					{
						opt.parentNode.removeChild(opt);
					});

					$A(nextField.originalOptions).each(function(opt)
					{
						nextField.appendChild(opt);
					});

					$A(nextField.options).each(function(opt)
					{
						if (!(root[value][opt.value] || !opt.value))
						{
							opt.parentNode.removeChild(opt);
						}
						//opt.style.display = (root[value][opt.value] || !opt.value) ? '' : 'none';
					});

					try
					{
						nextField.selectedIndex = index;
					}
					catch (e)
					{
						nextField.selectedIndex = 0;
					}

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
			(new Product.ImageSwitcher(product.DefaultImage.ID, product.DefaultImage.paths, product.DefaultImage.name_lang, product.ID)).switchImage();
		}
	},

	setVariation: function(productID)
	{
		$H(this.variations.products).each(function(product)
		{
			var ids = product[0].split(/-/);
			var product = product[1];

			if (product.ID == productID)
			{
				for (var k = 0; k < this.selectFields.length; k++)
				{
					this.selectFields[k].value = ids[k];
				}

				this.displayVariationInfo(product);
				this.updateVisibleOptions();
			}
		}.bind(this));
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

Order.previewOptionImage = function(upload, res)
{
	if (res.error)
	{

	}
	else
	{
		var root = upload.up('.productOption').down('.optionFileInfo');
		var titleContainer = root.down('.optionFileName');
		var image = root.down('.optionFileImage').down('img.optionThumb');

		titleContainer.update(res.name);

		if (res.thumb)
		{
			image.src = 'upload/optionImage/' + res.thumb;
			image.show();
		}
		else
		{
			image.hide();
		}

		root.show();
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
			window.setTimeout(function()
			{
				if ($(radioButton.id).checked)
				{
					addressForm.show();
				}
				else
				{
					addressForm.hide();
				}
			}, 100);
		};

		Event.observe(radioButton.form, 'change', onchange);
		Event.observe(radioButton.form, 'click', onchange);

		onchange();
	}

	form = $(form);
	newAddressToggle($('billing_new'));
	newAddressToggle($('shipping_new'));
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

Filter.reset = function()
{
	var
		f = $("multipleChoiceFilterForm");
	$A(f.getElementsByTagName("input")).each(function(node)
	{
		if("checkbox" == node.type.toLowerCase() && node.checked==true)
		{
			node.checked=false;
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

		if (this.stateSelector && (this.stateSelector.length > 0))
		{
			Element.show(this.stateSelector);
			Element.hide(this.stateTextInput);
		}

		if (countrySelector)
		{
			Event.observe(countrySelector, 'change', this.updateStates.bind(this));
		}
	},

	updateStates: function(e)
	{
		var url = this.url + (this.url.indexOf('?') == -1 ? '?' : '&') + 'country=' + this.countrySelector.value;
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
		if (!this.container)
		{
			return;
		}

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

/* Create or get popup container */
Frontend.getPopup = function(containerID)
{
	var $ = jQ;

	return $('#' + containerID)[0] ||
			(function()
			{
				var d = document.createElement('div');
				document.body.appendChild(d);
				d.id = containerID;

				$(d).jqm({modal: true, closeClass: 'cancel'});

				return d;
			}
			)();
}

Frontend.initColorOptions = function(containerID)
{
	var $ = jQ;

	var c = $('#' + containerID);
	c.addClass('colorOptionsWidget');

	c.find('.optionName').each(function(i, el)
	{
		$(el.parentNode).css('backgroundColor', $(el).css('backgroundColor'));
	});

	c.find('input').change(function(e)
	{
		c.find('p').removeClass('selected');
		$(this.parentNode).addClass('selected');
	});

	c.find('.optionPrice').each(function(i, el)
	{
		var price = $.trim(el.innerHTML);
		if (price.match(/^\(.*\)$/))
		{
			price = price.substr(1, price.length - 2);
		}

		el.innerHTML = price;

		$(el).css('color', '#' + contrastingColor($(el).closest('label').css('backgroundColor')));
	});

	c.find('input:checked').change();
}

function invertColor(color)
{
	var color = hexToRGBArray(color);
	return 'rgb(' + (255 - color[0]).toString() + ', ' + (255 - color[1]).toString() + ', ' + (255 - color[2]).toString() + ')';
}

function contrastingColor(color)
{
	function luma(color) // color can be a hx string or an array of RGB values 0-255
	{
		var rgb = (typeof color === 'string') ? hexToRGBArray(color) : color;
		return (0.2126 * rgb[0]) + (0.7152 * rgb[1]) + (0.0722 * rgb[2]); // SMPTE C, Rec. 709 weightings
	}

    return (luma(color) >= 165) ? '000' : 'fff';
}

function hexToRGBArray(color)
{
	if (color.substr(0, 4) == 'rgb(')
	{
		return color.match(/\(([0-9]+), ([0-9]+), ([0-9]+)\)/).slice(1, 4);
	}

	if (color.length === 3)
	{
		color = color.charAt(0) + color.charAt(0) + color.charAt(1) + color.charAt(1) + color.charAt(2) + color.charAt(2);
	}
	else if (color.length !== 6)
		throw('Invalid hex color: ' + color);
	var rgb = [];
	for (var i = 0; i <= 2; i++)
		rgb[i] = parseInt(color.substr(i * 2, 2), 16);
	return rgb;
}

Frontend.initCategory = function()
{
	var $ = jQ;
	$('.quickShopMenu').each(function(index, node)
	{
		$(node.parentNode).mouseenter(function()
		{
			$(this).find('.quickShopMenu').stop(true, true).show('fast');
		});

		$(node.parentNode).mouseleave(function()
		{
			$(this).find('.quickShopMenu').stop(true, true).hide('fast');
		});
	});

	$('.quickShopLink').click(function(e)
	{
		e.preventDefault();

		var el = Frontend.getPopup('quickShopContainer');
		$(el).html('');

		var loadQuickShop = function(e)
		{
			e.preventDefault();

			new LiveCart.AjaxRequest(e.target.href, e.target,
				function(oR)
				{
					$(el).html(oR.responseText)
						 .addClass('jqmWindow')
						 .jqmShow()
						 .center();

					Frontend.AjaxInit(el);

					$('img', el).load(function()
					{
						$(el).center();
					});

					$(el).find('.productPrev, .productNext').click(loadQuickShop);

					$('.jqmOverlay, #quickShopContainer .popupClose').click(function(e)
					{
						e.preventDefault();
						$('#quickShopContainer').jqmHide();
					});
				});
		}

		loadQuickShop(e);
	});

	$(document).keydown(function(e)
	{
		if (e.keyCode == 37)
		{
			$('.productPrev').click();
		}
		else if (e.keyCode == 39)
		{
			$('.productNext').click();
		}
	});
}

Frontend.OnePageCheckout = function(options)
{
	this.nodes = {}
	this.findUsedNodes();
	this.bindEvents();
	this.options = options;

	this.showOverview();

	if (this.options['OPC_SHOW_CART'])
	{
		this.nodes.root.addClassName('showCart');
	}

	var errorMsg = this.nodes.root.down('.errorMsg');
	if (errorMsg)
	{
		errorMsg.scrollTo();
	}
}

Frontend.OnePageCheckout.prototype =
{
	findUsedNodes: function()
	{
		this.nodes.root = $('content');
		this.nodes.login = $('checkout-login');
		this.nodes.shipping = $('checkout-shipping');
		this.nodes.shippingAddress = $('checkout-shipping-address');
		this.nodes.shippingMethod = $('checkout-shipping-method');
		this.nodes.billingAddress = $('checkout-billing');
		this.nodes.payment = $('checkout-payment');
		this.nodes.cart = $('checkout-cart');
		this.nodes.overview = $('checkout-overview');
	},

	bindEvents: function()
	{
		Observer.add('order', this.updateOrderTotals.bind(this));
		Observer.add('order', this.updateOrderStatus.bind(this));
		Observer.add('cart', this.updateCartHTML.bind(this));
		Observer.add('shippingMethods', this.updateShippingMethodsHTML.bind(this));
		Observer.add('user', this.updateShippingOptions.bind(this));
		Observer.add('overview', this.updateOverviewHTML.bind(this));
		Observer.add('payment', this.updatePaymentHTML.bind(this));
		Observer.add('completedSteps', this.updateCompletedSteps.bind(this));
		Observer.add('editableSteps', this.updateEditableSteps.bind(this));

		this.initShippingOptions();
		this.initShippingAddressForm();
		this.initBillingAddressForm();
		this.initCartForm();
		this.initOverview();
		this.initPaymentForm();
	},

	updateShippingOptions: function(e)
	{
		var el = e ? Event.element(e) : null;
		new LiveCart.AjaxRequest(this.nodes.shippingMethod.down('form'), el);
	},

	updateShippingAddress: function(e)
	{
		var el = e ? Event.element(e) : null;
		var form = this.nodes.shippingAddress.down('form');
		var billingForm = this.nodes.billingAddress.down('form');

		if (billingForm)
		{
			form.elements.namedItem('sameAsShipping').value = (billingForm.elements.namedItem('sameAsShipping').checked ? 'on' : '');
		}
		else
		{
			form.elements.namedItem('sameAsShipping').value = 'on';
		}

		new LiveCart.AjaxRequest(form, el, this.handleFormRequest(form));
	},

	updateBillingAddress: function(e)
	{
		var el = e ? Event.element(e) : null;
		var form = this.nodes.billingAddress.down('form');
		new LiveCart.AjaxRequest(form, el, this.handleFormRequest(form));
	},

	updateCart: function(e)
	{
		if (e)
		{
			Event.stop(e);
		}

		var el = e ? Event.element(e) : null;
		var form = this.nodes.cart.down('form');

		// file uploads cannot be handled via AJAX
		var hasFile = false;
		$A(form.getElementsByTagName('input')).each(function(el)
		{
			if (el.getAttribute('type').toLowerCase() == 'file')
			{
				hasFile = true;
			}
		});

		if (!hasFile)
		{
			var onComplete = el == form ? this.showOverview.bind(this) : null;
			new LiveCart.AjaxRequest(form, el, onComplete);
		}
		else
		{
			form.submit();
		}
	},

	setPaymentMethod: function(e)
	{
		this.nodes.noMethodSelectedMsg.addClassName('hidden');
		var el = e ? Event.element(e) : null;
		new LiveCart.AjaxRequest(this.nodes.payment.down('form'), el);
	},

	submitOrder: function(e)
	{
		Event.stop(e);

		var button = Event.element(e);

		if (!validateForm(this.nodes.paymentMethodForm))
		{
			return;
		}

		if (this.nodes.paymentMethodForm.redirect)
		{
			window.location.href = this.nodes.paymentMethodForm.redirect;
			this.submitButtonProgress(button);
			return;
		}

		var form = this.nodes.paymentDetailsForm || $('paymentForm').down('form');
		if ('form' != form.tagName.toLowerCase())
		{
			form = this.nodes.paymentDetailsForm.down('form');
		}

		if (!form)
		{
			this.nodes.noMethodSelectedMsg.removeClassName('hidden');
		}
		else if (validateForm(form))
		{
			form.submit();
			this.submitButtonProgress(button);
		}
	},

	submitButtonProgress: function(button)
	{
		var tag = button.tagName.toLowerCase();

		if ((tag != 'a') && (tag != 'input'))
		{
			button = button.up('a');
		}

		var indicator = button.up().down('.progressIndicator') || button.parentNode;
		indicator.addClassName('progressIndicator');
		indicator.show();
	},

	initShippingOptions: function()
	{
		this.formOnChange(this.nodes.shippingMethod.down('form'), this.updateShippingOptions.bind(this));
	},

	initShippingAddressForm: function()
	{
		var form = this.nodes.shippingAddress.down('form');
		this.formOnChange(form, this.updateShippingAddress.bind(this));
		new Order.AddressSelector(form);
	},

	initBillingAddressForm: function()
	{
		var form = this.nodes.billingAddress.down('form');
		this.formOnChange(form, this.updateBillingAddress.bind(this));
		new Order.AddressSelector(form);
	},

	initCartForm: function()
	{
		if (!this.nodes.cart)
		{
			return;
		}

		var form = this.nodes.cart.down('form');
		this.formOnChange(form, this.updateCart.bind(this));
		Event.observe(form, 'submit', this.updateCart.bindAsEventListener(this));
		Event.observe($('checkout-return-to-overview'), 'click', this.showOverview.bindAsEventListener(this));
	},

	initPaymentForm: function()
	{
		var form = this.nodes.payment.down('form');
		Event.observe(form, 'submit', Event.stop);

		this.nodes.paymentMethodForm = form;
		this.nodes.paymentDetailsForm = $('paymentForm');
		this.nodes.noMethodSelectedMsg = $('no-payment-method-selected');

		this.formOnChange(form, this.setPaymentMethod.bind(this), [$('tos')]);

		var paymentMethods = form.getElementsBySelector('input.radio');
		$A(paymentMethods).each(function(el)
		{
			if ((el.value.substr(0, 1) == '/') || (el.value.substr(0, 4) == 'http'))
			{
				el.onclick =
					function()
					{
						this.nodes.paymentMethodForm.redirect = el.value;
					}.bind(this)
			}
			else
			{
				el.onclick =
					function(noHighlight)
					{
						this.nodes.paymentMethodForm.redirect = '';
						el.blur();
						this.showPaymentDetailsForm(el, noHighlight);
					}.bind(this)

				if (1 == paymentMethods.length)
				{
					el.onclick(true);
					this.nodes.payment.addClassName('singleMethod');
				}
			}

			//el.onclick = function(e) { if (e) { Event.stop(e); } el.onchange() };

			var tr = $(el).up('tr');
			if (tr)
			{
				var logoImg = tr.down('.paymentLogo');
				if (logoImg)
				{
					logoImg.onclick = function() { el.onclick(); }
				}
			}

			if (el.checked)
			{
				this.showPaymentDetailsForm(el, true);
			}
		}.bind(this));

		Event.observe($('submitOrder'), 'click', this.submitOrder.bind(this));
	},

	showPaymentDetailsForm: function(el, noHighlight)
	{
		var form = this.nodes.paymentDetailsForm;
		form.innerHTML = '';
		this.updateElement(form, $('payForm_' + el.value).innerHTML, noHighlight);

		try
		{
			(form.down('input.text') || form.down('textarea') || form.down('select') || form).focus();
		}
		catch (e)
		{ }
	},

	initOverview: function()
	{
		var controls = this.nodes.overview.down('.orderOverviewControls');
		if (!controls)
		{
			return;
		}

		Event.observe(controls.down('a'), 'click', this.showCart.bindAsEventListener(this));
	},

	showCart: function(e)
	{
		if (e)
		{
			Event.stop(e);
		}

		if (this.nodes.cart)
		{
			this.nodes.cart.show();
		}

		if (!this.options['OPC_SHOW_CART'])
		{
			this.nodes.overview.hide();
		}
	},

	showOverview: function(e)
	{
		if (e)
		{
			Event.stop(e);
		}

		if (!this.options['OPC_SHOW_CART'] && this.nodes.cart)
		{
			this.nodes.cart.hide();
		}

		this.nodes.overview.show();
	},

	updateCartHTML: function(params)
	{
		this.updateElement(this.nodes.cart, params);
		this.initCartForm();
	},

	updatePaymentHTML: function(params)
	{
		var backupIds = {}
		var selectedMethod = null;
		$A(['paymentMethodForm', 'paymentDetailsForm']).each(function(cont)
		{
			var form = this.nodes[cont];
			if (form)
			{
				Form.State.backup(form);
				backupIds[cont] = form.backupId;

				// get selected payment method
				if ('paymentMethodForm' == cont)
				{
					$A($(form).getElementsBySelector('input.radio')).each(function(radio)
					{
						if (radio.checked)
						{
							selectedMethod = radio.id;
						}
					}.bind(this));
				}
			}
		}.bind(this));

		this.nodes.payment.innerHTML = '';
		this.updateElement(this.nodes.payment, params);
		this.initPaymentForm();

		// restore payment method selection
		var form = this.nodes['paymentMethodForm'];
		if (form)
		{
			form.backupId = backupIds['paymentMethodForm'];
			Form.State.restore(form, ['payMethod']);

			$A($(form).getElementsBySelector('input.radio')).each(function(radio)
			{
				if (radio.id == selectedMethod)
				{
					radio.checked = true;
					radio.onclick();
				}
			}.bind(this));
		}

		var form = this.nodes['paymentDetailsForm'];
		if (form)
		{
			form.backupId = backupIds['paymentDetailsForm'];
			Form.State.restore(form);
		}
	},

	updateShippingMethodsHTML: function(params)
	{
		this.updateElement(this.nodes.shippingMethod, params);
		this.initShippingOptions();
	},

	updateOverviewHTML: function(params)
	{
		this.updateElement(this.nodes.overview, params);
		this.initOverview();
	},

	updateCompletedSteps: function(steps)
	{
		return this.updateStepStatus(steps, 'step-incomplete');
	},

	updateEditableSteps: function(steps)
	{
		return this.updateStepStatus(steps, 'step-disabled');
	},

	updateStepStatus: function(steps, className)
	{
		$H(steps).each(function(value)
		{
			var step = value[0];
			var status = value[1];
			var node = this.nodes[step];

			if (status)
			{
				node.removeClassName(className);
			}
			else
			{
				node.addClassName(className);
			}
		}.bind(this));
	},

	updateOrderTotals: function(order)
	{
		$A(this.nodes.root.getElementsByClassName('orderTotal')).each(function(el)
		{
			this.updateElement(el, order.formattedTotal);
		}.bind(this));
	},

	updateOrderStatus: function(order)
	{
		this.nodes.root[['addClassName', 'removeClassName'][1 - order.isShippingRequired]]('shippable');
		this.nodes.root[['removeClassName', 'addClassName'][1 - order.isShippingRequired]]('downloadable');
	},

	formOnChange: function(form, func, skippedFields)
	{
		if (!form)
		{
			return;
		}

		var skippedFields = skippedFields || [];

		ActiveForm.prototype.resetErrorMessages(form);

		$A(['input', 'select', 'textarea']).each(function(tag)
		{
			$A(form.getElementsByTagName(tag)).each(function(el)
			{
				if (skippedFields.indexOf(el) > -1)
				{
					return;
				}

				Event.observe(el, 'focus', function() { window.focusedInput = el; });
				Event.observe(el, 'blur', this.fieldBlurCommon(form, el));

				// change event doesn't fire on radio buttons at IE until they're blurred
				if (('radio' == el.getAttribute('type')) || ('checkbox' == el.getAttribute('type')))
				{
					Event.observe(el, 'click', function(e) { Event.stop(e); this.fieldOnChangeCommon(form, func.bindAsEventListener(this))(e);}.bind(this));
				}
				else
				{
					Event.observe(el, 'change', this.fieldOnChangeCommon(form, func.bindAsEventListener(this)));
				}
			}.bind(this));
		}.bind(this));

		if (!form.onchange)
		{
			form.onchange = function() {}
		}
	},

	fieldOnChangeCommon: function(form, func)
	{
		return function(e)
		{
			var el = Event.element(e);

			if ('radio' == el.getAttribute('type'))
			{
				try
				{
					el.blur();
				} catch (e) { }
			}

			if (form.errorList)
			{
				delete form.errorList[el.name];
			}

			ActiveForm.prototype.resetErrorMessage(el);
			func(e);
		}.bind(this);
	},

	fieldBlurCommon: function(form, el)
	{
		return function(e)
		{
			window.focusedInput = null;

			window.setTimeout(
			function()
			{
				if (form.errorList && (!window.focusedInput || (window.focusedInput.up('form') != form)))
				{
					this.showErrorMessages(form);
				}
			}.bind(this), 200);

		}.bind(this);
	},

	handleFormRequest: function(form)
	{
		return function(originalRequest)
		{
			form.errorList = {};

			if (originalRequest.responseData.errorList)
			{
				form.errorList = originalRequest.responseData.errorList;

				if (!window.focusedInput || (window.focusedInput.up('form') != form))
				{
					this.showErrorMessages(form);
				}
			}
		}.bind(this);
	},

	showErrorMessages: function(form)
	{
		ActiveForm.prototype.resetErrorMessages(form);
		ActiveForm.prototype.setErrorMessages(form, form.errorList);
	},

	updateElement: function(element, html, noHighlight)
	{
		if (!element)
		{
			return;
		}

		if (element.innerHTML == html)
		{
			noHighlight = true;
		}

		element.update(html);

		if (!noHighlight)
		{
			new Effect.Highlight(element);
		}
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

Frontend.ShowCartPopup = function(req)
{
	var $ = jQ;

	var el = Frontend.getPopup('shoppingCartContainer');
	$(el).html(req.popupCart)
		 .addClass('jqmWindow')
		 .jqmShow();

	$('#cart', el).tableScroll({height: 400});

	$(el).center();

	$('.jqmOverlay, #shoppingCartContainer .continueShopping').click(function(e)
	{
		e.preventDefault();
		$('#shoppingCartContainer').jqmHide();
	});
}

Frontend.Ajax.AddToCart = function(container)
{
	var $ = jQ;

	$(container).find('a.addToCart').click(
		function(e)
		{
			e.preventDefault();

			new LiveCart.AjaxRequest(this.href, $(this.parentNode).find('.price')[0], function(oR)
			{
				Frontend.ShowCartPopup(oR.responseData);
			});
		}
	);
}

Frontend.Ajax.AddToCartFromProductPage = function(container)
{
	var $ = jQ;

	$('#mainInfo form', container).iframePostForm({complete: function(req)
	{
		if ('<pre>' == req.substr(0, 5))
		{
			req = req.substr(5, req.length - 11);
		}

		req = $.parseJSON($('<div/>').html(req).text());
		Frontend.ShowCartPopup(req);

		Observer.processArray(req);
	}});
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
		button.onclick = Compare.add;
	});
}

Frontend.AjaxInit = function(container)
{
	$H(Frontend.Ajax).each(function(v)
	{
		new Frontend.Ajax[v[0]](container);
	});
}

var FrontendToolbar = Class.create();
FrontendToolbar.prototype = {
	isBackend: false
}

if (window.FooterToolbar)
{
	FrontendToolbar.prototype = Object.extend(FooterToolbar.prototype, FrontendToolbar.prototype);
}
