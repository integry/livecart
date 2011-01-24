{pageTitle}{t _manufacturers}{/pageTitle}
<div class="manufacturersIndex">
{include file="layout/frontend/layout.tpl"}
<div id="content">
	<h1>{t _manufacturers}</h1>
	{if 'MANUFACTURER_PAGE_LIST_STYLE'|config == 'MANPAGE_STYLE_ALL_IN_ONE_PAGE'}
		{include file="manufacturers/listAllInOnePage.tpl"}
	{else} {* if MANPAGE_STYLE_GROUP_BY_FIRST_LETTER *}
		{include file="manufacturers/listGroupByFirstLetter.tpl"}
	{/if}
	<div style="clear:both;"></div>
	{if $count > $perPage && $perPage > 0}
		<div class="resultPages">
			<span>{t _pages}:</span> {paginate current=$currentPage count=$count perPage=$perPage url=$url}
		</div>
	{/if}
</div>
{include file="layout/frontend/footer.tpl"}
</div>