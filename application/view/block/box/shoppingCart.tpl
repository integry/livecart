<div id="smallCart">

	<div class="btn-toolbar pull-right">

	<div class="btn-group" id="topAccount">
		<a class="btn btn-default dropdown-toggle" data-toggle="dropdown disabled" href="[[ url("user/index") ]]">
			<span class="glyphicon glyphicon-user"></span>
			{t _your_account}
			<span class="caret"></span>
		</a>

		<ul class="dropdown-menu">
			{% if $user.ID > 0 %}
				<li class="logout">
					<a href="[[ url("user/logout") ]]">{t _sign_out}</a>
				</li>
			{% endif %}
		</ul>
	</div>

	{% if 'ENABLE_CART'|config %}
		{% if (req('controller') == 'product') || (req('controller') == 'category') %}{% set returnPath = true %}{% endif %}

		<div class="btn-group" id="topCart">
			<a class="btn btn-default dropdown-toggle" data-toggle="dropdown disabled" href="{link controller=order returnPath=$returnPath}">
				<span class="glyphicon glyphicon glyphicon-shopping-cart"></span>
				{t _shopping_cart}
				<span class="badge menu_cartItemCount" style="{% if !$order.basketCount %}display: none;{% endif %}">{maketext text="_cart_item_count" params=$order.basketCount}</span>
				<span class="caret"></span>
			</a>

			<ul class="dropdown-menu"></ul>
		</div>
	{% endif %}
	</div>

</div>

<script type="text/javascript">
	Observer.add('orderSummary', Frontend.SmallCart, 'smallCart');
</script>