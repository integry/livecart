<tree id="{$rootID}">
	{foreach item="category" from=$categoryList}
	<item child="{$category.childrenCount}" id="{$category.ID}" text="{$category.name|escape:'html'}" {if !$category.isEnabled} style="color: #999;"{/if}></item>
	{/foreach}
</tree>