{if $pages}
<div class="well sidebar-nav informationMenu">
	<div class="nav-header">{t _information}</div>

	<div class="content">
		<ul class="nav nav-list">
		{foreach from=$pages item=page}
			<li id="static_{$page.ID}"><a href="{pageUrl data=$page}">{$page.title_lang}</a></li>
			{if $page.children}
				<ul class="nav nav-list">
					{foreach from=$page.children item=subPage}
						<li id="static_{$subPage.ID}"><a href="{pageUrl data=$subPage}">{$subPage.title_lang}</a></li>
					{/foreach}
				</ul>
			{/if}
		{/foreach}
		</ul>
	</div>
</div>
{/if}