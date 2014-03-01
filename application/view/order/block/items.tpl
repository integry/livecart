{foreach from=cart.cartItems item="item" name="cart"}
	<tr>
		<td class="cartControl">
			{% if config('ENABLE_WISHLISTS') %}
				<a href="[[ url("order/moveToWishList/" ~ item.ID, "return=`return`") ]]">{t _move_to_wishlist}</a>
			{% endif %}
			<a href="[[ url("order/delete/" ~ item.ID, "return=`return`") ]]">{t _remove}</a>
		</td>

		<td class="cartImage">
			{% if item.Product.DefaultImage.urls.1 %}
			<a href="{productUrl product=item.Product}">
				{img src=item.Product.DefaultImage.urls.1 alt=item.Product.name()|escape}
			</a>
			{% endif %}
		</td>

		{% if config('SHOW_SKU_CART') %}
			<td>{item.Product.sku|escape}</td>
		{% endif %}

		<td class="cartName">
			<div>
				{% if item.Product.ID %}
					<a href="{productUrl product=item.Product}">[[item.Product.name()]]</a>
				{% else %}
					<span>[[item.Product.name()]]</span>
				{% endif %}
				<small class="categoryName">(&rlm;[[item.Product.Category.name()]])</small>
			</div>

			[[ partial("order/itemVariations.tpl") ]]
			[[ partial("order/block/itemOptions.tpl") ]]

			[[ partial("order/block/recurringItem.tpl") ]]

			{% if !empty(multi) %}
				[[ partial('order/selectItemAddress.tpl', ['item': item]) ]]
			{% endif %}
		</td>

		<td class="cartPrice {% if item.itemBasePrice != item.itemPrice %}discount{% endif %}">
			{% if item.count == 1 %}
				<span class="basePrice">[[item.formattedBasePrice]]</span><span class="actualPrice">[[item.formattedPrice]]</span>
			{% else %}
				[[item.formattedDisplaySubTotal]]
				<div class="subTotalCalc">
					<span class="itemCount">
						[[item.count]] x
					</span>
					<span class="basePrice">[[item.formattedBasePrice]]</span><span class="actualPrice">[[item.formattedPrice]]</span>
				</div>
			{% endif %}
		</td>

		<td class="cartQuant">
			{textfield name="item_`item.ID`" class="text"}
		</td>
	</tr>
{% endfor %}
