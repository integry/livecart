{pageTitle}{t _manufacturers}{/pageTitle}
{include file="layout/frontend/layout.tpl"}
{include file="block/content-start.tpl"}

	{if 'MANUFACTURER_PAGE_LIST_STYLE'|config == 'MANPAGE_STYLE_ALL_IN_ONE_PAGE'}
		{include file="manufacturers/listAllInOnePage.tpl"}
	{else} {* if MANPAGE_STYLE_GROUP_BY_FIRST_LETTER *}
		{include file="manufacturers/listGroupByFirstLetter.tpl"}
	{/if}
	<div style="clear:both;"></div>
	{if $count > $perPage && $perPage > 0}
		{paginate current=$currentPage count=$count perPage=$perPage url=$url}
	{/if}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}
