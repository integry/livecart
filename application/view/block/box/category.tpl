{function name="categoryTree" node=false filters=false}
	{if $node}
		<ul class="nav nav-pills nav-stacked">
		{foreach from=$node item=category}
			{if $category.ID == $currentId}
				<li class="active">
			{else}
				<li>
			{/if}
					<a href="{categoryUrl data=$category filters=$category.filters}">{$category.name_lang}</a>

					{if 'DISPLAY_NUM_CAT'|config}
						{include file="block/count.tpl" count=$category.count}
					{/if}
					{if $category.subCategories}
		   				{categoryTree node=$category.subCategories}
					{/if}
				</li>
		{/foreach}
		</ul>
	{/if}
{/function}

<div class="panel categories">
	<div class="panel-heading">{t _categories}</div>

	<div class="content">
		{categoryTree node=$categories}
	</div>
</div>
