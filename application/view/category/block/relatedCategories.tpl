{function name="categoryNode" node=null}
	{if $node.ParentNode}
		{categoryNode node=$node.ParentNode}
		{if $node.ParentNode.ID > 1}&gt;{/if}
		<a href="{categoryUrl data=$node}">[[node.name_lang]]</a>
	{/if}
{/function}

<div class="resultStats">{t _related_categories}</div>
<ul class="foundCategories">
	{foreach from=$categories item=category}
		<li>{categoryNode node=$category}</li>
	{/foreach}
</ul>