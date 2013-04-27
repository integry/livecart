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
		this.nodes.checkoutOptions = $('checkout-options');
		this.nodes.login = $('checkout-login');
		this.nodes.shipping = $('checkout-shipping');
		this.nodes.shippingAddress = $('checkout-shipping-address');
		this.nodes.shippingMethod = $('checkout-shipping-method');
		this.nodes.billingAddress = $('checkout-billing');
		this.nodes.payment = $('checkout-payment');
		this.nodes.cart = $('checkout-cart');
		this.nodes.overview = $('checkout-overview');

		this.steps = [this.nodes.login, this.nodes.billingAddress, this.nodes.shippingAddress, this.nodes.shippingMethod, this.nodes.payment];
	},

	bindEvents: function()
	{
		Observer.add('order', this.updateOrderTotals.bind(this));
		Observer.add('order', this.updateOrderStatus.bind(this));
		Observer.add('cart', this.updateCartHTML.bind(this));
		Observer.add('billingAddress', this.updateBillingAddressHTML.bind(this));
		Observer.add('shippingAddress', this.updateShippingAddressHTML.bind(this));
		Observer.add('shippingMethods', this.updateShippingMethodsHTML.bind(this));
		Observer.add('user', this.updateShippingOptions.bind(this));
		Observer.add('overview', this.updateOverviewHTML.bind(this));
		Observer.add('payment', this.updatePaymentHTML.bind(this));
		Observer.add('editableSteps', this.updateEditableSteps.bind(this));
		Observer.add('completedSteps', this.updateCompletedSteps.bind(this));

		this.initCheckoutOptions();
		this.initShippingOptions();
		this.initShippingAddressForm();
		this.initBillingAddressForm();
		this.initCartForm();
		this.initOverview();
		this.initPaymentForm();

		this.bindModifyLinks();

		jQuery('#checkout-right-inner').css({width: jQuery('#checkout-right-inner').width() + 'px'}).sticky({ topSpacing: 10 });
	},

	bindModifyLinks: function()
	{
		var self = this;
		jQuery('.modifyStep a, .stepTitle a').click(function(e)
		{
			e.preventDefault();
			var step = jQuery(this).closest('div.step');
			if (!step.hasClass('step-disabled'))
			{
				self.reopenStep(step);
			}
		});
	},

	updateCheckoutOptions: function(e)
	{
		var el = e ? Event.element(e) : null;
		new LiveCart.AjaxRequest(this.nodes.checkoutOptions, el);
	},

	updateShippingOptions: function(e)
	{
		var form = this.nodes.shippingMethod.down('form');
		if (!validateForm(form))
		{
			return;
		}

		var el = e ? Event.element(e) : null;
		new LiveCart.AjaxRequest(form, el);
	},

	updateShippingAddress: function(e)
	{
		var form = this.nodes.shippingAddress.down('form');
		if (!form)
		{
			return;
		}

		this.submitAddressForm(form, e);
	},

	updateBillingAddress: function(e)
	{
		var form = this.nodes.billingAddress.down('form');
		this.submitAddressForm(form, e);
	},

	submitAddressForm: function(form, e)
	{
		if (!validateForm(form))
		{
			return;
		}

		var el = e ? Event.element(e) : form.down('button[type=submit]');
		new LiveCart.AjaxRequest(form, el, this.handleFormRequest(form));
	},

	updateCart: function(e)
	{
		if (e)
		{
			e.preventDefault();
		}

		var el = e ? Event.element(e) : null;
		var form = this.nodes.cart.down('form');

		var onComplete = el == form ? this.showOverview.bind(this) : null;
		new LiveCart.AjaxRequest(form, el, onComplete);
	},

	setPaymentMethod: function(e)
	{
		this.nodes.noMethodSelectedMsg.addClassName('hidden');
		var el = e ? Event.element(e) : null;
		new LiveCart.AjaxRequest(this.nodes.payment.down('form'), el);
	},

	submitOrder: function(e)
	{
		e.preventDefault();

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

		if ((tag != 'a') && (tag != 'button'))
		{
			button = button.up('a');
		}

		var indicator = button.up().down('.progressIndicator') || button.parentNode;

		indicator.addClassName('progressIndicator');
		indicator.show();
	},

	initCheckoutOptions: function()
	{
		this.formOnChange(this.nodes.checkoutOptions, this.updateCheckoutOptions.bind(this));
	},

	initShippingOptions: function()
	{
		jQuery('dl', this.nodes.shippingMethod).not(':has(dt)').hide();
		this.formOnChange(this.nodes.shippingMethod.down('form'), this.updateShippingOptions.bind(this));
	},

	initShippingAddressForm: function()
	{
		jQuery('#sameAsBilling').change(function()
		{
			jQuery('#shippingAddressForm').toggle(jQuery(this).attr('checked') != 'checked');
		});

		jQuery('#sameAsBilling').change();

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
		jQuery(form).submit(function(e) { e.preventDefault(); });

		this.nodes.paymentMethodForm = form;
		this.nodes.paymentDetailsForm = $('paymentForm');
		this.nodes.noMethodSelectedMsg = $('no-payment-method-selected');

		this.formOnChange(form, this.setPaymentMethod.bind(this), [$('tos')]);

		var paymentMethods = jQuery('input[type=radio]',form);
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
			e.preventDefault();
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
		if (e && e.preventDefault)
		{
			e.preventDefault();
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

	updateBillingAddressHTML: function(params)
	{
		this.updateElement(this.nodes.billingAddress, params);
		this.initBillingAddressForm();
	},

	updateShippingAddressHTML: function(params)
	{
		this.updateElement(this.nodes.shippingAddress, params);
		this.initShippingAddressForm();
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

			if (node)
			{
				if (status)
				{
					node.removeClassName(className);
				}
				else
				{
					node.addClassName(className);
					this.disableFurtherSteps(node);
				}
			}
		}.bind(this));

		this.updateStepControls();
	},

	updateOrderTotals: function(order)
	{
		$A(this.nodes.root.getElementsByClassName('orderTotal')).each(function(el)
		{
			this.updateElement(el, order.formattedTotal);
		}.bind(this));
	},

	updateStepControls: function()
	{
		var self = this;
		var opened = false;

		jQuery(this.nodes.root).find('div.step').each(function()
		{
			var step = jQuery(this);
			if (!step.hasClass('step-incomplete') || opened)
			{
				self.closeStep(step);
			}
			else
			{
				self.openStep(step, true);
				opened = true;
			}
		});
	},

	closeStep: function(step)
	{
		step = jQuery(step);

		if (step.hasClass('closed'))
		{
			return;
		}

		var content = step.find('.accordion-inner');
		jQuery(content).height(jQuery(content).height());

		step.removeClass('opened');
		step.addClass('closed');

		content.slideUp('slow', function()
		{
			step.removeClass('opened');
			step.addClass('closed');
		});
	},

	openStep: function(step, noEffect)
	{
		step = jQuery(step);

		var duration = noEffect ? 0 : 'slow';
		var content = step.find('.accordion-inner');
		content.slideDown(duration, function()
		{
			step.addClass('opened');
			step.removeClass('closed');
			jQuery(content).height('auto');
			window.setTimeout(function()
			{
				scrollToElement(step, 100);
			}, 20);
		});
	},

	reopenStep: function(step)
	{
		if (step.hasClass('opened'))
		{
			return;
		}

		var self = this;
		jQuery(this.nodes.root).find('div.step').each(function()
		{
			self.closeStep(this);
		});

		this.openStep(step);
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

		jQuery(form).submit(function(e)
		{
			e.preventDefault();
			func();
		});
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

					this.disableFurtherSteps(form);
				}
			}

			this.bindModifyLinks();
		}.bind(this);
	},

	showErrorMessages: function(form)
	{
		ActiveForm.prototype.resetErrorMessages(form);
		ActiveForm.prototype.setErrorMessages(form, form.errorList);
	},

	disableFurtherSteps: function(formOrContainer)
	{
		var step = jQuery(formOrContainer.firstChild).closest('.step')[0];
		var found = false;
		for (var k = 0; k < this.steps.length; k++)
		{
			if (found)
			{
				jQuery(this.steps[k]).addClass('step-disabled');
			}

			if (step == this.steps[k])
			{
				found = true;
			}
		}
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
			if (!element.style.backgroundColor || ('transparent' == element.style.backgroundColor))
			{
				jQuery(element).effect('highlight');
			}
		}

		this.bindModifyLinks();

		this.showOverview();
	}
}