<ul class="rootCategories">
	{foreach from=$categories item=category}
		<li{if $category.ID == $currentId} class="current"{/if}><a href="{categoryUrl data=$category}">{$category.name_lang}</a></li>
	{/foreach}
</ul>
