{% extends "layout/frontend.tpl" %}

{% block title %}{$results.meta.name|capitalize} &gt;&gt; "[[query]]"{{% endblock %}


{% block content %}

	<div class="modelSearchResults">
		<div class="resultStats">{maketext text="_found_x" params=$results.meta.name} {include file="block/count.tpl" count=$results.count}</div>

		<ol>
			{foreach $results.records as $record}
				{include file=$results.meta.template}
			{/foreach}
		</ol>

	</div>

	{% if $results.count > $perPage %}
		{paginate current=$page count=$results.count perPage=$perPage url=$url}
	{% endif %}

{% endblock %}

