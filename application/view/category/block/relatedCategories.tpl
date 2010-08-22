{defun name="categoryNode" node=null}

	{if $node.ParentNode}

		{fun name="categoryNode" node=$node.ParentNode}
		{if $node.ParentNode.ID > 1}&gt;{/if}
		<a href="{categoryUrl data=$node}">{$node.name_lang}</a>

	{/if}

{/defun}

<div class="resultStats">{t _related_categories}</div>
<ul class="foundCategories">
	{foreach from=$categories item=category}

		<li>{fun name="categoryNode" node=$category}</li>

	{/foreach}
</ul>