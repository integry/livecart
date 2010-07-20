<h2>{t _shopping_cart}</h2>

<a href="#" id="checkout-return-to-overview">{t _return_to_overview}</a>
<div class="clear"></div>

{capture assign="cartUpdate"}
	<td id="cartUpdate"><input type="submit" class="submit" value="{tn _update}" /></td>
{/capture}
{assign var="cartUpdate" value=$cartUpdate|@str_split:10000}
{php}$GLOBALS['cartUpdate'] = $this->get_template_vars('cartUpdate'); $this->assign_by_ref('GLOBALS', $GLOBALS);{/php}

{form action="controller=onePageCheckout action=updateCart" method="POST" enctype="multipart/form-data" handle=$form id="cartItems"}
<table id="cart">
	<thead>
		<tr>
			<th colspan="3" class="cartListTitle"></th>
			<th class="cartPrice">{t _price}</th>
			<th class="cartQuant">{t _quantity}</th>
		</tr>
	</thead>
	<tbody>

		{include file="order/block/items.tpl"}
		{include file="order/block/discounts.tpl"}
		{include file="order/block/shipping.tpl"}

		{if !'HIDE_TAXES'|config}
			{include file="order/block/taxes.tpl"}
		{/if}

		{include file="order/block/total.tpl"}

		{include file="order/block/customFields.tpl"}
		{include file="order/block/coupons.tpl"}

	</tbody>
</table>
<input type="hidden" name="return" value="{$return}" />

{/form}