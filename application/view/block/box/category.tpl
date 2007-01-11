{defun name="categoryTree" node=false}
	{if $node}
		<ul>			
		{foreach from=$node item=category}
			<li{if $category.ID == $currentId} class="current"{/if}>
				<a href="{categoryUrl data=$category}">{$category.name}</a>
			</li>	
			{if $category.subCategories}
				{fun name="categoryTree" node=$category.subCategories}
			{/if}
		{/foreach}
		</ul>
	{/if}	
{/defun}

<div class="box">
	<div class="title">
		<div>Categories {$currentID}</div>
	</div>

	<div class="content">
		{fun name="categoryTree" node=$categories}
	</div>
</div>