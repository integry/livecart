{%- macro categoryTree(node, filters, current, subCategories) %}
	{% if node %}
		{% set level=level+1 %}
		<ul class="nav nav-pills nav-stacked">
		{% for category in node %}
			{% if category.lft <= current.lft and category.rgt >= current.rgt %}
				<li class="active">
			{% else %}
				<li>
			{% endif %}

			{# <a href="{categoryUrl data=category filters=category.filters}">[[category.name()]]</a> #}
			<a href="[[ url(route(category)) ]]">[[category.name()]]</a>

			{% if config('DISPLAY_NUM_CAT') %}
				[[ partial('block/count.tpl', ['count': category.count]) ]]
			{% endif %}

			{% if !empty(subCategories[category.ID]) %}
				[[ categoryTree(subCategories[category.ID], level, current, subCategories) ]]
			{% endif %}

			</li>
		{% endfor %}

		{% if 2 == level %}
		<div class="divider"></div>
		{% endif %}
		</ul>
	{% endif %}
{%- endmacro %}

<div class="panel panel-primary categories">
	<div class="panel-heading">
		<span class="glyphicon glyphicon-search"></span>
		<span>{t _categories}</span>
	</div>

	<div class="content">
		[[ categoryTree(categories, 0, current, subCategories) ]]
	</div>
</div>
