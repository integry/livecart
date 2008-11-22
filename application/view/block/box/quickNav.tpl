{defun name="dynamicCategoryTree" node=false level=0}
	{if $node}
		{foreach from=$node item=category}
			<option value="{categoryUrl data=$category}">{'--'|@str_repeat:$level} {$category.name_lang}</option>
			{if $category.subCategories}
				{fun name="dynamicCategoryTree" node=$category.subCategories level=$level+1}
			{/if}
		{/foreach}
		</ul>
	{/if}
{/defun}

{if $manufacturers || $categories}
<div class="box">
	<div class="title">
		<div>{t _quick_nav}</div>
	</div>

	<div class="content">

		{if $manufacturers}
			<select onchange="window.location.href = this.value;" style="width: 100%;">
				<option>{t _manufacturers}</option>
				{foreach $manufacturers as $man}
					<option value="{categoryUrl data=$rootCat addFilter=$man.filter}">{$man.name}</option>
				{/foreach}
			</select>
		{/if}

		{if $categories}
			<select onchange="window.location.href = this.value;" style="width: 100%;">
				<option>{t _categories}</option>
				{fun name="dynamicCategoryTree" node=$categories}
			</select>
		{/if}

	</div>
</div>
{/if}