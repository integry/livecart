<ul class="rootCategories">
	{foreach from=$categories item=category}
		<li{if $category.ID == $currentId} class="current"{/if}><span class="top_tab_left"><a href="{categoryUrl data=$category}">{$category.name_lang}</a></span></li>
	{/foreach}
</ul>
