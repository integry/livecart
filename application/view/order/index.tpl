{% extends "layout/frontend.tpl" %}

{% title %}{t _your_basket}{% endblock %}

{% block content %}

	<div ng-controller="OrderController" ng-init="setOrder([[ json(order.toArray()) ]])">
		<table class="table table-striped shopping-cart">
			<thead>
				<tr>
					<th></th>
					<th>{t _price}</th>
					<th>{t _quantity}</th>
					<th>{t _subtotal}</th>
				</tr>
			</thead>
			<tbody>
				<tr ng-repeat="item in order.OrderedItems">
					<td class="col-lg-6">{{ item.name }}</td>
					<td class="col-lg-3">{{ item.formattedDisplayPrice }}</td>
					<td class="col-lg-3">
						[[ partial("product/block/quantity.tpl") ]]
						<button class="btn btn-default btn-xs btn-remove" ng-click="remove(item)"><span class="glyphicon glyphicon-remove"></span></button>
					</td>
					<td class="col-lg-3">{{ item.formattedDisplaySubTotal }}</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3">{t _total}:</td>
					<td>{{ order.formattedTotal[order.currencyID] }}</td>
				</tr>
			</tfoot>
		</table>
		
		<div class="row">
			<div class="col-md-6">
				
			</div>
			<div class="col-md-6 text-right">
				<a class="btn btn-primary">{t _checkout}</a>
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
