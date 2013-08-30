{function name="dynamicCategoryTree" node=false level=0}
	{% if !empty(node) %}
		{foreach from=$node item=category}
			<option value="{categoryUrl data=$category}">{'&nbsp;&nbsp;&nbsp;'|@str_repeat:$level} [[category.name_lang]]</option>
			{% if $category.subCategories %}
				{dynamicCategoryTree node=$category.subCategories level=$level+1}
			{% endif %}
		{/foreach}
	{% endif %}
{/function}

{% if $manufacturers || $categories %}
<div class="panel quickNav">
	<div class="panel-heading">
		<span class="glyphicon glyphicon-link"></span>
		{t _quick_nav}
	</div>

	<div class="content">

		{% if !empty(manufacturers) %}
			<p>
			<select onchange="window.location.href = this.value;">
				<option>{t _manufacturers}</option>
				{foreach $manufacturers as $man}
					<option value="[[man.url]]">[[man.name]]</option>
				{/foreach}
			</select>
			</p>
		{% endif %}

		{% if !empty(categories) %}
			<p>
			<select onchange="window.location.href = this.value;">
				<option>{t _categories}</option>
				{dynamicCategoryTree node=$categories}
			</select>
			</p>
		{% endif %}

	</div>
</div>
{% endif %}
