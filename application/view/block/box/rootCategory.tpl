<ul class="rootCategories{if $currentId == $categories.0.ID} firstActive{/if}">
	{foreach from=$categories item=category}
		<li{if $category.ID == $currentId} class="current"{/if}><a href="{categoryUrl data=$category}"><span>{$category.name_lang}</span></a></li>
	{/foreach}
</ul>
