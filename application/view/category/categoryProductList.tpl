{% if !empty(products) %}
	<div class="row resultStats">
		<div class="col-sm-6 pagingInfo text-muted">
			[[ maketext('_showing_products', [paginator.getFrom(), paginator.getTo(), count]) ]]
		</div>

		<div class="col-sm-6 listOptions">
			
			{% if sortOptions and (count(sortOptions) > 1) %}
			<span class="sortOptions">
				[[ select("sort", sortOptions, 'class': 'form-control', 'ng-model': 'sort', 'ng-init': 'sort = "' ~ sort ~ '"') ]]
			</span>
			{% endif %}

			{# [[ partial("category/block/switchListLayout.tpl") ]] #}
		</div>
	</div>

	<hr />

	{% if !empty(products) %}
		[[ partial('category/productListLayout.tpl') ]]
	{% endif %}
{% endif %}
