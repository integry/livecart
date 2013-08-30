{% if $specFieldList %}
	{form handle=$form}
	<div class="specFieldContainer">
		[[ partial('backend/product/form/specFieldList.tpl', ['angular': "product", 'product': product, 'cat': cat, 'specFieldList': specFieldList]) ]]
	</div>
	{/form}
{% endif %}
