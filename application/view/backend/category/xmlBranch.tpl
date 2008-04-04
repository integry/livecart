<?xml version="1.0" encoding="UTF-8"?>
<tree id="{$rootID}">
	{foreach item="category" from=$categoryList}
	<item child="{$category.childrenCount}" id="{$category.ID}" text="{$category.name_lang|escape:'html'}"{if !$category.isEnabled} style="color: #999;"{/if}></item>
	{/foreach}
</tree>