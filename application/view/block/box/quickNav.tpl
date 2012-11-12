{function name="dynamicCategoryTree" node=false level=0}
	{if $node}
		{foreach from=$node item=category}
			<option value="{categoryUrl data=$category}">{'&nbsp;&nbsp;&nbsp;'|@str_repeat:$level} {$category.name_lang}</option>
			{if $category.subCategories}
				{dynamicCategoryTree node=$category.subCategories level=$level+1}
			{/if}
		{/foreach}
	{/if}
{/function}

{if $manufacturers || $categories}
<div class="well sidebar-nav quickNav">
	<div class="nav-header">{t _quick_nav}</div>

	<div class="content">

		{if $manufacturers}
			<select onchange="window.location.href = this.value;" style="width: 100%;">
				<option>{t _manufacturers}</option>
				{foreach $manufacturers as $man}
					<option value="{$man.url}">{$man.name}</option>
				{/foreach}
			</select>
		{/if}

		{if $categories}
			<select onchange="window.location.href = this.value;" style="width: 100%;">
				<option>{t _categories}</option>
				{dynamicCategoryTree node=$categories}
			</select>
		{/if}

	</div>
</div>
{/if}