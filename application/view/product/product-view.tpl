<div class="row product-view" ng-controller="ProductController" ng-init="setProductID([[ product.ID ]])">
	<div class="col-md-6">
		[[ render("product/images") ]]
	</div>
	<div class="col-md-6">
		<h1>[[ product.name() ]]</h1>
		<div class="short-description">[[ product.shortDescription() ]]</div>
		
		<div class="price-block">
			<div ng-show="!hasSelectedOptions(item)">
				[[ partial("product/block/productPrice.tpl") ]]
			</div>
			
			<div ng-show="hasSelectedOptions(item)">
				<div class="price">
					{{ orderedItem.formattedDisplaySubTotal }}
				</div>
				<div ng-show="item.count > 1" class="subtotal-calc">
					{{ item.count }} x {{ orderedItem.formattedDisplayPrice }}
				</div>
			</div>
		</div>
		
		{% if (product.longDescription != product.shortDescription) and product.longDescription %}
			<div class="long-description">[[ product.longDescription() ]]</div>
		{% endif %}
		
		[[ render("product/order") ]]
	</div>
</div>
