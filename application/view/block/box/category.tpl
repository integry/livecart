{defun name="categoryTree" node=false filters=false}
	{if $node}
		<ul>			
		{foreach from=$node item=category}
			{if $category.ID == $currentId}
				<li class="current">
					<span>{$category.name_lang}</span> 
					<span class="count">({$category.activeProductCount})</span>
				</li>	
			{else}
				<li>
					<a href="{categoryUrl data=$category filters=$category.filters}">{$category.name_lang}</a>
					<span class="count">({$category.activeProductCount})</span>
				</li>	
			{/if}
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
