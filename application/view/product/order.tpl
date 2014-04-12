{% if product.isAvailable() and config('ENABLE_CART') %}

	<div class="well">
		
		{# block PRODUCT-OPTIONS #}
		{# block PRODUCT-VARIATIONS #}

		<div id="productToCart" class="cartLinks" ng-class="{'hasOptions': options.length}" >
			<div class="selectOptions" ng-show="options" ng-init="options = [[ json(toArray(product.productOptions)) ]]">
				<div ng-repeat="option in options" class="option">
					<span class="param">{{ option.name }}</span>
					<span class="value">
						<prod-option option="option"></prod-option>
					</span>
				</div>
			</div>
			
			<div class="selectQuantity">
				<span class="param">{t _quantity}</span>
				<span class="value">
					[[ partial("product/block/quantity.tpl") ]]
				</span>
			</div>

			<button type="submit" class="btn btn-success btn-large addToCart" ng-click="addToCart(item)">
				<span class="glyphicon glyphicon-shopping-cart"></span>
				<span class="buttonCaption">{t _add_to_cart}</span>
			</button>

			{# hidden name="return" value=catRoute #}

		</div>
	
	</div>
{% endif %}

