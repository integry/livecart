{if $product.isAvailable && 'ENABLE_CART'|config}

	<div class="well">
		
		{block PRODUCT-OPTIONS}
		{block PRODUCT-VARIATIONS}

		<div id="productToCart" class="cartLinks">
			<span class="param">{t _quantity}:</span>
			<span class="value">
				{include file="product/block/quantity.tpl"}
			</span>

			<button type="submit" class="btn btn-success btn-large addToCart">
					<span class="glyphicon glyphicon-shopping-cart"></span>
					<span class="buttonCaption">{t _add_to_cart}</span>
			</button>

			{hidden name="return" value=$catRoute}
		</div>
	
	</div>
{/if}

