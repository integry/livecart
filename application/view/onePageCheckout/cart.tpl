{% if 'SHOW_SKU_CART'|config %}
	{assign var="extraColspanSize" value=1+$extraColspanSize}
{% endif %}
<h2>{t _shopping_cart}</h2>

<a href="#" id="checkout-return-to-overview">{t _return_to_overview}</a>
<div class="clear"></div>

{capture assign="cartUpdate"}
	<td id="cartUpdate"><input type="submit" class="submit" value="{tn _update}" /></td>
{/capture}
{assign var="cartUpdate" value=$cartUpdate|@str_split:10000}
{php}$GLOBALS['cartUpdate'] = $smarty->getTemplateVars('cartUpdate'); $smarty->assignByRef('GLOBALS', $GLOBALS);{/php}

{form action="onePageCheckout/updateCart" method="POST" enctype="multipart/form-data" handle=$form id="cartItems" class="form-horizontal"}
<table id="cart">
	<thead>
		<tr>
			<th colspan="{% if 'SHOW_SKU_CART'|config %}4{% else %}3{% endif %}" class="cartListTitle"></th>
			<th class="cartPrice">{t _price}</th>
			<th class="cartQuant">{t _quantity}</th>
		</tr>
	</thead>
	<tbody>

		[[ partial("order/block/items.tpl") ]]
		[[ partial("order/block/discounts.tpl") ]]
		[[ partial("order/block/shipping.tpl") ]]

		{% if !'HIDE_TAXES'|config %}
			[[ partial("order/block/taxes.tpl") ]]
		{% endif %}

		[[ partial('order/block/total.tpl', ['extraColspanSize': $extraColspanSize]) ]]

		[[ partial("order/block/customFields.tpl") ]]
		[[ partial("order/block/coupons.tpl") ]]

	</tbody>
</table>
<input type="hidden" name="return" value="[[return]]" />

{/form}