{defun name="dynamicCategoryTree" node=false level=0}
	{if $node}
		{foreach from=$node item=category}
			<option value="{categoryUrl data=$category}">{'&nbsp;&nbsp;&nbsp;'|@str_repeat:$level} {$category.name_lang}</option>
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
			<p>
				<select onchange="window.location.href = this.value;" style="width: 100%;">
					<option>{t _manufacturers}</option>
					{foreach $manufacturers as $man}
						<option value="{$man.url}">{$man.name}</option>
					{/foreach}
				</select>
			</p>
		{/if}

		{if $categories}
			<p>
				<select onchange="window.location.href = this.value;" style="width: 100%;">
					<option>{t _categories}</option>
					{fun name="dynamicCategoryTree" node=$categories}
				</select>
			</p>
		{/if}

	</div>
</div>
{/if}