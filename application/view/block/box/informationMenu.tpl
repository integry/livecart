{if $pages}
<div class="box informationMenu">
	<div class="title">
		<div>{t _information}</div>
	</div>

	<div class="content">
		<ul>
		{foreach from=$pages item=page}
			<li id="static_{$page.ID}"><a href="{pageUrl data=$page}">{$page.title_lang}</a></li>
		{/foreach}
		</ul>
	</div>
</div>
{/if}