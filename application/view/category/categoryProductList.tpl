{% if !empty(products) %}
	<div class="row resultStats">
		<div class="col-sm-6 pagingInfo text-muted">
			[[ maketext('_showing_products', [paginator.getFrom(), paginator.getTo(), count]) ]]
		</div>

		<div class="col-sm-6 listOptions">
			
			{#
			{% if sortOptions && (sortOptions|@count > 1) %}
			<span class="sortOptions">
					{t _sort_by}
					{form handle=sortForm action="self" method="get"}
					{selectfield id="productSort" name="sort" options=sortOptions onchange="this.form.submit();"}
					{/form}
			</span>
			{% endif %}
			#}

			{# [[ partial("category/block/switchListLayout.tpl") ]] #}
		</div>
	</div>

	<hr />

	{% if !empty(products) %}
		[[ partial('category/productListLayout.tpl') ]]
	{% endif %}
{% endif %}
