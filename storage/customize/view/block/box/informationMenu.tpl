{if $pages}		
<div class="informationMenu">
	<ul>
	{foreach from=$pages item=page}
		<li id="static_{$page.ID}"><a href="{pageUrl data=$page}">{$page.title_lang}</a></li> | 
	{/foreach}
	</ul>
</div>
{/if}