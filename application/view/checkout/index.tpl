{% extends "layout/frontend.tpl" %}

{% title %}{t _checkout}{% endblock %}

{% block content %}

	<div ng-controller="CheckoutController" ng-init="setOrder([[ json(order.toArray()) ]])">
	[[ form() ]] action="" ng-submit="send(form)">
		<div class="row">
			<div class="col-lg-12">
				<accordion close-others="true">
					<accordion-group is-open="step == 'login'" ng-show="!user">
						<accordion-heading>
							Sign In
						</accordion-heading>
						
						<div class="row">
							<div class="col-lg-6">
								<ng-form>
									<input type="text" class="form-control" ng-model="email" placeholder="Email" />
									<input type="password" class="form-control" ng-model="email" placeholder="Password" />
									<button type="submit" class="btn btn-primary">{t _sign_in}</button>
								</ng-form>
							</div>
							<div class="col-lg-6">
								<a class="btn btn-primary" ng-click="setAnon(true)">Checkout Without Registration</a>
							</div>
						</div>
					</accordion-group>

					<accordion-group is-open="step == 'shipping'">
						<accordion-heading>
							Shipping Address
							
							<div class="text-muted checkout-filled" ng-show="shippingAddressPreview()">
								
							</div>
						</accordion-heading>
						
						<ng-form name="form">
							[[ partial("user/addressForm.tpl", ['prefix': 'ShippingAddress.']) ]]
							<button class="btn btn-primary" ng-click="saveShippingAddress(form)">{t _continue}</button>
						</ng-form>
					</accordion-group>

					<accordion-group is-open="step == 'method'">
						<accordion-heading>
							Shipping Method
						</accordion-heading>

					</accordion-group>

					<accordion-group is-open="step == 'billing'">
						<accordion-heading>
							Billing Address
						</accordion-heading>
						
						[[ partial("user/addressForm.tpl", ['prefix': 'BillingAddress.']) ]]
					</accordion-group>

					<accordion-group is-open="step == 'payment'">
						<accordion-heading>
							Payment Method
						</accordion-heading>

					</accordion-group>
				</accordion>
			</div>
			<div class="col-lg-0">
			
			</div>
		</div>
		
		<submit>{t _complete_checkout}</submit>
		</form>
	</div>

	
	<div ng-controller="OrderController" ng-init="setOrder([[ json(order.toArray()) ]])">
		<table class="table table-striped shopping-cart">
			<thead>
				<tr>
					<th></th>
					<th>{t _price}</th>
					<th>{t _quantity}</th>
				</tr>
			</thead>
			<tbody>
				<tr ng-repeat="item in order.OrderedItems">
					<td class="col-lg-6">{{ item.name }}</td>
					<td class="col-lg-3">{{ item.formattedDisplaySubTotal }}</td>
					<td class="col-lg-3">
						<button class="btn btn-default btn-xs" ng-click="decCount(item)" ng-disabled="item.count <= 1">-</button>
						<input class="form-control quantity" ng-model="item.count" />
						<button class="btn btn-default btn-xs" ng-click="incCount(item)">+</button>
						<button class="btn btn-default btn-xs" ng-click="remove(item)"><span class="glyphicon glyphicon-remove"></span></button>
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td>{t _total}:</td>
					<td>{{ order.formattedTotal[order.currencyID] }}</td>
				</tr>
			</tfoot>
		</table>
		
		<div class="row">
			<div class="col-md-6">
				
			</div>
			<div class="col-md-6 text-right">
				
			</div>
		</div>
	</div>
	
	{#
	<div class="checkoutHeader">
		{% if cart.cartItems && !isOnePageCheckout %}
			[[ partial('checkout/checkoutProgress.tpl', ['progress': "progressCart", 'order': cart]) ]]
		{% endif %}
	</div>

	[[ partial("order/changeMessages.tpl") ]]

	{% if !cart.cartItems && !cart.wishListItems %}
		<div style="clear: left;">
			{t _empty_basket}. <a href="[[ url(return) ]]">{t _continue_shopping}</a>.
		</div>
	{% else %}

	{% if cart.cartItems %}
		[[ partial("order/cartItems.tpl") ]]
	{% endif %}

	{% if cart.wishListItems && config('ENABLE_WISHLISTS') %}
		<div style="clear: left;">
			[[ partial("order/wishList.tpl") ]]
		</div>
	{% endif %}
	#}

{% endblock %}
