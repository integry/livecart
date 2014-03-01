{function name="categoryNode" node=null}
	{% if node.ParentNode %}
		{categoryNode node=node.ParentNode}
		{% if node.ParentNode.ID > 1 %}&gt;{% endif %}
		<a href="{categoryUrl data=node}">[[node.name()]]</a>
	{% endif %}
{/function}

<div class="resultStats">{t _found_cats} [[ partial('block/count.tpl', ['count': foundCategories|@count]) ]]</div>
<ul class="foundCategories">
	{% for category in foundCategories %}
		<li>{categoryNode node=category}</li>
	{% endfor %}
</ul>