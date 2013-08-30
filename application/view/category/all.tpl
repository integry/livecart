{% extends "layout/frontend.tpl" %}

{% block title %}{t _all_categories}{{% endblock %}

{% block content %}

	{foreach from=$sorted item=categories key=letter}
		<h2>{$letter|@capitalize}</h2>

		{foreach from=$categories item=category}
			{% if !$index || (($totalCount/2) <= $index && $columns < 2) %}
				{% if $columns %}
					</div>
				{% endif %}
				<div class="manufacturerColumn">
				{assign var=columns value=$columns+1}
			{% endif %}

			<ul>
				<li><a href="{categoryUrl data=$category}">[[category.name]]</a>
				{include file="block/count.tpl" count=$category.count}
			</ul>
			{assign var=index value=$index+1}
		{/foreach}
	{/foreach}

{% endblock %}
