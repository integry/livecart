/**
 *	@author Integry Systems
 */

(function($) {
	$.fn.maxHeight = function()
	{
		var max = 0;

		this.each(function()
		{
			max = Math.max(max, $(this).height());
		});

		this.height(max);
	};
})(jQuery);

jQuery(function()
{
	jQuery('.subCategories .thumbnail').maxHeight();
	jQuery('.subCategories .subCategoryItem').maxHeight();

	// make product grid items even height
	jQuery('.productGrid').each(function()
	{
		jQuery('.thumbnail', this).maxHeight();
		jQuery('.image', this).maxHeight();
		jQuery('h4', this).maxHeight();
	});

	// pagination elements
	jQuery('ul.pagination li.disabled a').click(function(e) { e.preventDefault(); });

});

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
		jQuery(div).effect('highlight');

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

Product.ChangeRecurringPlanAction = Class.create();
Product.ChangeRecurringPlanAction.prototype =
{
	initialize: function(url, node)
	{
		var
			changedDropdownName = $("recurringBillingPlan"),
			form = changedDropdownName.up("form");
			progressIndicator = $(node).up("div").down("span");

		progressIndicator.addClassName("progressIndicator");
		progressIndicator.show();
		changedDropdownName.value = node.id;
		form.action = url;
		form.submit();
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
		e.preventDefault();
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
	e.preventDefault();
	var el = Event.element(e);
	el.addClassName('progressIndicator');
	new LiveCart.AjaxRequest(el.href, null, function(oR) { Compare.addComplete(oR, el); });
}

Compare.addComplete = function(origReq, el)
{
	el.blur();
	jQuery(el).effect('highlight');
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
		e.preventDefault();
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
	var $ = jQuery;

	return $('#' + containerID)[0] ||
			(function()
			{
				var d = document.createElement('div');
				document.body.appendChild(d);
				d.id = containerID;

				return d;
			}
			)();
}

Frontend.initColorOptions = function(containerID)
{
	var $ = jQuery;

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
	var $ = jQuery;
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
					$(el).html(oR.responseText).find('.modal').modal();

					Frontend.AjaxInit(el);

					/*
					$('img', el).load(function()
					{
						$(el).center();
					});
					*/

					$(el).find('.productPrev, .productNext').click(loadQuickShop);
				});
		}

		loadQuickShop(e);
	});

	document.onkeyup = function(e)
	{
		if (!$('#jquery-lightbox').is(':visible'))
		{
			if (e.keyCode == 37)
			{
				$('.productPrev').click();
			}
			else if (e.keyCode == 39)
			{
				$('.productNext').click();
			}
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
	jQuery(fc).effect('highlight');
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
		jQuery(msgContainer).show({});
		window.setTimeout(function() { new jQuery(msgContainer).hide('fade'); }, 5000);
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
	var $ = jQuery;

	var el = Frontend.getPopup('shoppingCartContainer');
	$(el).html(req.popupCart).find('.modal').modal();

	$('#cart', el).tableScroll({height: 400});

	$('#shoppingCartContainer .continueShopping, .modal-backdrop').click(function(e)
	{
		e.preventDefault();
		$('#shoppingCartContainer').find('.modal').modal('hide');
	});
}

Frontend.Ajax.AddToCart = function(container)
{
	var $ = jQuery;

	$(container).find('a.addToCart').click(
		function(e)
		{
			e.preventDefault();

			new LiveCart.AjaxRequest(this.href, $(this.parentNode).find('.glyphicon')[0], function(oR)
			{
				Frontend.ShowCartPopup(oR.responseData);
			});
		}
	);
}

Frontend.Ajax.AddToCartFromProductPage = function(container)
{
	var $ = jQuery;

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
	jQuery('td.addToWishList, a.addToWishList', container).click(function(e)
	{
		e.preventDefault();
		var a = Event.element(e);
		new LiveCart.AjaxRequest(a.href, a, function () { jQuery(a).effect('highlight'); });
	});
}

Frontend.Ajax.AddToCompare = function(container)
{
	jQuery('a.addToCompare', container).click(Compare.add);
}

Frontend.AjaxInit = function(container)
{
	$H(Frontend.Ajax).each(function(v)
	{
		new Frontend.Ajax[v[0]](container);
	});
}

function evenHeights(selector)
{
	var currentTallest = 0,
		currentRowStart = 0,
		rowDivs = new Array(),
		$el,
		topPosition = 0;

	var $ = jQuery;

	$(selector).addClass('firstPanel');

	$(selector).each(function()
	{
		$el = $(this);
		topPostion = $el.position().top;

		if (currentRowStart != topPostion)
		{
		 // we just came to a new row.  Set all the heights on the completed row
		 for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
		   rowDivs[currentDiv].height(currentTallest);
		 }

		 // set the variables for the new row
		 rowDivs.length = 0; // empty the array
		 currentRowStart = topPostion;
		 currentTallest = $el.height();
		 rowDivs.push($el);

		} else {

		 // another div on the current row.  Add it to the list and check if it's taller
		 rowDivs.push($el);
		 currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
		$el.removeClass('firstPanel');
		}

		// do the last row
		for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++)
		{
			rowDivs[currentDiv].height(currentTallest);
		}
	});
}

(function($) {
	$.fn.progressIndicator = function()
	{
		var el = $(this);

		var classes = ['glyphicon-circle-arrow-right', 'glyphicon-circle-arrow-left', 'glyphicon-circle-arrow-up', 'glyphicon-circle-arrow-down'];

		var switchClass = function()
		{
			var index = el.data('progress-index');
			if (!index)
			{
				index = 0;
			}

			if (classes[index])
			{
				el.removeClass(classes[index]);
			}

			if (!el.hasClass('progressIndicator'))
			{
				console.log(el.attr('class'));
				return;
			}

			index++;
			if (index > classes.length - 1)
			{
				index = 0;
			}
			el.addClass(classes[index]);

			el.data('progress-index', index);
			window.setTimeout(switchClass, 50);
		};

		switchClass();
	};
})(jQuery);