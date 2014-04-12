<div class="image">
{#
	{block LIST-IMAGE}
	{block QUICK-SHOP product=product}
#}
	<a href="[[ url(route(product)) ]]" ng-click="showProduct([[ product.ID ]], $event)">
	{% if product.get_DefaultImage() and product.get_DefaultImage().getID() %}
		<img src="[[ product.get_DefaultImage().getPath(2) ]]" />
	{% else %}
		<img src="[[ config('MISSING_IMG_SMALL') ]]" />
	{% endif %}
	</a>
</div>
