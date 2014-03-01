{% extends "layout/frontend.tpl" %}

{% title %}{t _all_categories}{% endblock %}

{% block content %}

	{foreach from=sorted item=categories key=letter}
		<h2>{letter|@capitalize}</h2>

		{% for category in categories %}
			{% if !index || ((totalCount/2) <= index && columns < 2) %}
				{% if !empty(columns) %}
					</div>
				{% endif %}
				<div class="manufacturerColumn">
				{assign var=columns value=columns+1}
			{% endif %}

			<ul>
				<li><a href="{categoryUrl data=category}">[[category.name]]</a>
				[[ partial('block/count.tpl', ['count': category.count]) ]]
			</ul>
			{assign var=index value=index+1}
		{% endfor %}
	{% endfor %}

{% endblock %}
