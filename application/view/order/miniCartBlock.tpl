<div id="miniCart">
	{% if $order.basketCount %}

	{% if empty(hidePanel) %}
	<div class="panel panel-primary miniCart">
		<div class="panel-heading">
			<span class="glyphicon glyphicon-search"></span>
			<span><a href="{link controller=order}">{t _shopping_cart}</a></span>
		</div>
	{% endif %}

		<div class="content">
			<ul class="list-unstyled" id="miniCartContents">
			{foreach from=$order.cartItems item="item" name="cart"}
				<li><span class="miniCartCount">[[item.count]]</span> x <a href="{productUrl product=$item.Product}">[[item.Product.name_lang]]</a></li>
			{/foreach}
			</ul>

			<div class="miniCartTotal">
				<div>{t _total}: <span class="miniCartTotalAmount">{$order.formattedTotal[$order.Currency.ID]}</span></div>
				<a href="{link controller=checkout}" class="btn btn-danger checkout">
					{t _checkout}
				</a>
			</div>
		</div>

	{% if empty(hidePanel) %}
	</div>
	{% endif %}

	{% endif %}
</div>

{% if empty(hidePanel) %}
<script type="text/javascript">
	Observer.add('miniCart', Frontend.MiniCart, 'miniCart');
</script>
{% endif %}
