{if $pages}
<div class="box informationMenu">
	<div class="title">
		<div>{t _information}</div>
	</div>

	<div class="content">
		<ul>
		{foreach from=$pages item=page}
			<li id="static_{$page.ID}"><a href="{pageUrl data=$page}">{$page.title_lang}</a></li>
			{if $page.children}
				<ul>
					{foreach from=$page.children item=page}
						<li id="static_{$page.ID}"><a href="{pageUrl data=$page}">{$page.title_lang}</a></li>
					{/foreach}
				</ul>
			{/if}
		{/foreach}
		</ul>
	</div>
</div>
{/if}