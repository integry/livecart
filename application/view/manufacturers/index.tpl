{% extends "layout/frontend.tpl" %}

{% block title %}{t _manufacturers}{{% endblock %}

{% block content %}

	{if 'MANUFACTURER_PAGE_LIST_STYLE'|config == 'MANPAGE_STYLE_ALL_IN_ONE_PAGE'}
		{include file="manufacturers/listAllInOnePage.tpl"}
	{else} {* if MANPAGE_STYLE_GROUP_BY_FIRST_LETTER *}
		{include file="manufacturers/listGroupByFirstLetter.tpl"}
	{/if}
	<div style="clear:both;"></div>
	{if $count > $perPage && $perPage > 0}
		{paginate current=$currentPage count=$count perPage=$perPage url=$url}
	{/if}

{% endblock %}

