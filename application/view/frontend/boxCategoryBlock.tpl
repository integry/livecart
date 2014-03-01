{%- macro categoryTree(node, filters) %}
	{% if node %}
		{% set level=level+1 %}
		<ul class="nav nav-pills nav-stacked">
		{% for category in node %}
			{% if category.ID == currentId %}
				<li class="active">
			{% else %}
				<li>
			{% endif %}

				{# <a href="{categoryUrl data=category filters=category.filters}">[[category.name()]]</a> #}
				<a href="[[ url(route(category)) ]]">[[category.name()]]</a>

				{% if config('DISPLAY_NUM_CAT') %}
					[[ partial('block/count.tpl', ['count': category.count]) ]]
				{% endif %}

				{% if !empty(category.subCategories) %}
					[[ categoryTree(category.subCategories, level) ]]
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
		[[ categoryTree(categories, 0) ]]
	</div>
</div>
