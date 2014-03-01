{function name="categoryNode" node=null}
	{% if node.ParentNode %}
		{categoryNode node=node.ParentNode}
		{% if node.ParentNode.ID > 1 %}&gt;{% endif %}
		<a href="{categoryUrl data=node}">[[node.name()]]</a>
	{% endif %}
{/function}

<div class="resultStats">{t _related_categories}</div>
<ul class="foundCategories">
	{% for category in categories %}
		<li>{categoryNode node=category}</li>
	{% endfor %}
</ul>