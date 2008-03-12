{if $pages}
<div class="informationMenu">
	<ul>
	{foreach from=$pages item=page name="pages"}
		<li id="static_{$page.ID}"><a href="{pageUrl data=$page}">{$page.title_lang}</a></li>
		{if !$smarty.foreach.pages.last}
		<span class="sep"> | </span>
		{/if}
	{/foreach}
	</ul>
</div>
{/if}