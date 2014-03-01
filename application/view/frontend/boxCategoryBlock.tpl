{function name="categoryTree" node=false filters=false}
	{% if !empty(node) %}
		{$level=$level+1}
		<ul class="nav nav-pills nav-stacked">
		{foreach from=$node item=category}
			{% if $category.ID == $currentId %}
				<li class="active">
			{% else %}
				<li>
			{% endif %}
					<a href="{categoryUrl data=$category filters=$category.filters}">[[category.name_lang]]</a>

					{% if 'DISPLAY_NUM_CAT'|config %}
						[[ partial('block/count.tpl', ['count': category.count]) ]]
					{% endif %}
					{% if $category.subCategories %}
		   				{categoryTree node=$category.subCategories}
					{% endif %}
				</li>
		{/foreach}

		{% if 2 == $level %}
		<div class="divider"></div>
		{% endif %}
		</ul>
	{% endif %}
{/function}

<div class="panel panel-primary categories">
	<div class="panel-heading">
		<span class="glyphicon glyphicon-search"></span>
		<span>{t _categories}</span>
	</div>

	<div class="content">
		{categoryTree node=$categories level=0}
	</div>
</div>
