<fieldset class="container">

	<div class="image">
		<a href="{productUrl product=$product filterChainHandle=$filterChainHandle}">
		{if $product.DefaultImage.paths.2}
			{img src=$product.DefaultImage.paths.2 alt=$product.name_lang|escape}
		{else}
			{img src=image/missing_small.jpg alt=$product.name_lang|escape}
		{/if}
		</a>
	</div>

	<div class="descr">

		<div class="container">
			<div class="title">
				<a href="{productUrl product=$product filterChainHandle=$filterChainHandle}">{$product.name_lang}</a>
			</div>

			<div class="pricingInfo"><div><div>
				{if $product.isAvailable && 'ENABLE_CART'|config}
					<a href="{link controller=order action=addToCart id=$product.ID returnPath=true}" class="addToCart">{t _add_to_cart}</a>
				{/if}

				{if 'DISPLAY_PRICES'|config}
				<span>{t _our_price}:</span>

				<span class="price">
					{$product.formattedPrice.$currency}
					{if $product.formattedListPrice.$currency}
							<span class="listPrice">
								{$product.formattedListPrice.$currency}
							</span>
					{/if}
				</span>
				{/if}

				<br class="clear" />
			</div></div></div>
		</div>

		{if $product.attributes}
			<div class="spec">
				{foreach from=$product.attributes item="attr" name="attr"}
					{if $attr.values}
						{foreach from=$attr.values item="value" name="values"}
							{$value.value_lang}
							{if !$smarty.foreach.values.last}
							/
							{/if}
						{/foreach}
					{elseif $attr.value}
						{$attr.SpecField.valuePrefix_lang}{$attr.value}{$attr.SpecField.valueSuffix_lang}
					{elseif $attr.value_lang}
						{$attr.value_lang}
					{/if}

					{if !$smarty.foreach.attr.last}
					/
					{/if}
				{/foreach}
			</div>
		{/if}

		<div class="shortDescr">
			{$product.shortDescription_lang}
		</div>

		<div class="order">
			<div class="orderingControls">
				{if 'ENABLE_WISHLISTS'|config}
					<a href="{link controller=order action=addToWishList id=$product.ID returnPath=true}" class="addToWishList">{t _add_to_wishlist}</a>
				{/if}
			</div>
		</div>

	</div>

</fieldset>