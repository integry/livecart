{form action="controller=order action=update" method="POST" handle=$form id="cartItems"}
<h2>{t _cart_items}</h2>
<table id="cart">
	<thead>
		<tr>
			<th colspan="3" class="cartListTitle"></th>
			<th class="cartPrice">{t _price}</th>
			<th class="cartQuant">{t _quantity}</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$cart.cartItems item="item" name="cart"}
		<tr class="{zebra loop="cart"}{if $smarty.foreach.cart.first} first{/if}{if $smarty.foreach.cart.last} last{/if}">
			<td class="cartControl">
				{if 'ENABLE_WISHLISTS'|config}
					<a href="{link controller=order action=moveToWishList id=$item.ID query="return=`$return`"}">{t _move_to_wishlist}</a>
				{/if}
				<a href="{link controller=order action=delete id=$item.ID query="return=`$return`"}">{t _remove}</a>
			</td>

			<td class="cartImage">
				{if $item.Product.DefaultImage.paths.1}
				<a href="{productUrl product=$item.Product}">
					{img src=$item.Product.DefaultImage.paths.1 alt=$item.Product.name_lang|escape}
				</a>
				{/if}
			</td>

			<td class="cartName">
				<div>
					<a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a>
					<small>({$item.Product.Category.name_lang})</small>
				</div>
				{if $options[$item.ID] || $moreOptions[$item.ID]}
					<div class="productOptions">
						{foreach from=$options[$item.ID] item=option}
							{include file="product/optionItem.tpl" selectedChoice=$item.options[$option.ID]}
						{/foreach}

						{foreach from=$moreOptions[$item.ID] item=option}
							{if $item.options[$option.ID]}
								<div class="nonEditableOption">
									{$option.name_lang}:
									{if 0 == $option.type}
										{t _option_yes}
									{elseif 1 == $option.type}
										{$item.options[$option.ID].Choice.name_lang}
									{else}
										{$item.options[$option.ID].optionText|@htmlspecialchars}
									{/if}
									{if $item.options[$option.ID].Choice.priceDiff != 0}
										<span class="optionPrice">
											({$item.options[$option.ID].Choice.formattedPrice.$currency})
										</span>
									{/if}
								</div>
							{/if}
						{/foreach}

						{if $moreOptions[$item.ID]}
						<div class="productOptionsMenu">
							<a href="{link controller=order action=options id=$item.ID}" ajax="{link controller=order action=optionForm id=$item.ID}">{t _edit_options}</a>
						</div>
						{/if}
					</div>
				{/if}
			</td>
			<td class="cartPrice {if $item.itemBasePrice > $item.itemPrice}discount{/if}">
				{if $item.count == 1}
					<span class="basePrice">{$item.formattedBasePrice}</span><span class="actualPrice">{$item.formattedPrice}</span>
				{else}
					{$item.formattedDisplaySubTotal}
					<div class="subTotalCalc">
						{$item.count} x <span class="basePrice">{$item.formattedDisplayPrice}</span><span class="actualPrice">{$item.formattedPrice}</span>
					</div>
				{/if}
			</td>
			<td class="cartQuant">
				{textfield name="item_`$item.ID`" class="text"}
			</td>
		</tr>
	{/foreach}

	{foreach from=$cart.discounts item=discount}
			<tr>
				<td colspan="3" class="subTotalCaption">{t _discount}: <span class="discountDesc">{$discount.description}</span></td>
				<td class="amount discountAmount">{$discount.formatted_amount}</td>
				<td></td>
			</tr>
	{/foreach}

			<tr>
				<td colspan="3" class="subTotalCaption">{t _subtotal}:</td>
				<td class="subTotal">{$cart.formattedTotal.$currency}</td>
				<td id="cartUpdate"><input type="submit" class="submit" value="{tn _update}" /></td>
			</tr>

		{if $isCouponCodes}
				<tr id="couponCodes">
					<td colspan="5">
						<div class="container">
							{t _have_coupon}: <input type="text" class="text coupon" name="coupon" /> <input type="submit" class="submit coupon" value="{tn _add_coupon}" />
						</div>
					</td>
				<tr>
		{/if}

		{sect}
			{header}
				<tr id="cartFields">
					<td colspan="5">
						<div class="container">
			{/header}
			{content}
					{include file="block/eav/fields.tpl" item=$cart filter="isDisplayed"}
			{/content}
			{footer}
						<p>
							<label></label>
							<input type="submit" class="submit" value="{tn _update}" name="saveFields" />
						</p>
						</div>
					</td>
				</tr>
			{/footer}
		{/sect}

		<tr>
			<td colspan="4"></td>
			<td class="cartQuant"></td>
		</tr>
		<tr>
			<td colspan="5">
				<a href="{link route=$return}" class="continueShopping"><span><span><span><span>{t _continue_shopping}</span></span></span></span></a>
				{if $cart.isOrderable}
					<a href="{link controller=checkout}" class="proceedToCheckout" onclick="return Order.submitCartForm(this);"><span><span><span><span>{t _proceed_checkout}</span></span></span></span></a>
				{/if}
			</td>
		</tr>
	</tbody>
</table>
<input type="hidden" name="return" value="{$return}" />

{if $expressMethods && $cart.isOrderable}
	<div id="expressCheckoutMethods">
		{foreach from=$expressMethods item=method}
			<a href="{link controller=checkout action=express id=$method}"><img src="image/payment/{$method}.gif" /></a>
		{/foreach}
	</div>
{/if}
{/form}