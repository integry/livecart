{function name="categoryTree" node=false filters=false}
	{if $node}
		{$level=$level+1}
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

		{if 2 == $level}
		<div class="divider"></div>
		{/if}
		</ul>
	{/if}
{/function}

<div class="panel panel-primary categories">
	<div class="panel-heading">
		<span class="glyphicon glyphicon-search"></span>
		<span>{t _categories}</span>
	</div>

	<div class="content">
		{categoryTree node=$categories level=0}
	</div>
</div>
