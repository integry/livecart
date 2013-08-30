{function name="categoryNode" node=null}
	{% if $node.ParentNode %}
		{categoryNode node=$node.ParentNode}
		{% if $node.ParentNode.ID > 1 %}&gt;{% endif %}
		<a href="{categoryUrl data=$node}">[[node.name_lang]]</a>
	{% endif %}
{/function}

<div class="resultStats">{t _found_cats} {include file="block/count.tpl" count=$foundCategories|@count}</div>
<ul class="foundCategories">
	{foreach from=$foundCategories item=category}
		<li>{categoryNode node=$category}</li>
	{/foreach}
</ul>