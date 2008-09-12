{if $pages}
<div class="informationMenu">
	<ul>
	{foreach from=$pages item=page name="pages"}
		<li id="static_{$page.ID}"><a href="{pageUrl data=$page}">{$page.title_lang}</a></li>
		{if !$smarty.foreach.pages.last}
		<span class="sep"> | </span>
		{/if}
	{/foreach}

	<div class="extraPages">
		{if $pages}<span class="sep"> | </span> {/if}
		<li id="contactFormLink"><a href="{link controller=contactForm}">{t _contact}</a></li>
		<span class="sep"> | </span> <li id="allManufacturersLink"><a href="{link controller=manufacturers}">{t _manufacturers}</a></li>
		<span class="sep"> | </span> <li id="allCategoriesLink"><a href="{link controller=category action=all}">{t _categories}</a></li>
		<span class="sep"> | </span> <li id="allProductsLink"><a href="{link controller=category action=allProducts}">{t _all_products}</a></li>
	</div>

	</ul>
</div>
{/if}