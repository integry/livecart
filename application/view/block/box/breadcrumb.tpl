{% if (breadCrumb|@count > 1) && (config('SHOW_BREADCRUMB')) %}
	<ul class="breadcrumb">
		<style>
			.breadcrumb > li:after {ldelim} content: " [[ config('BREADCRUMB_SEPARATOR') ]] "; {rdelim}
		</style>

		{% if config('SHOW_BREADCRUMB_CAPTION') %}
		<span id="breadCrumbCaption">
			{t _you_are_here}:
		</span>
		{% endif %}

		{foreach from=breadCrumb item="breadCrumbItem" name="breadCrumb"}
			<li class="{% if smarty.foreach.breadCrumb.first %}first {% endif %}{% if smarty.foreach.breadCrumb.last %}last active{% endif %}">
				{% if !smarty.foreach.breadCrumb.last %}
					<a href="[[breadCrumbItem.url]]">[[breadCrumbItem.title]]</a>
				{% else %}
					[[breadCrumbItem.title]]
				{% endif %}
			</li>
		{% endfor %}
	</ul>
{% endif %}
