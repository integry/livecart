{function name="categoryTree" node=false filters=false}
	{if $node}
		<ul class="nav nav-list">
		{foreach from=$node item=category}
			{if $category.ID == $currentId}
				<li class="active">
					<span class="currentName">{$category.name_lang}</span>
			{else}
				<li>
					<a href="{categoryUrl data=$category filters=$category.filters}">{$category.name_lang}</a>
			{/if}
					{if 'DISPLAY_NUM_CAT'|config}
						<span class="count">(&rlm;{$category.count})</span>
					{/if}
					{if $category.subCategories}
		   				{categoryTree node=$category.subCategories}
					{/if}
				</li>
		{/foreach}
		</ul>
	{/if}
{/function}

<div class="well sidebar-nav categories">
	<div class="nav-header">{t _categories}</div>

	<div class="content">
		{categoryTree node=$categories}
	</div>
</div>
