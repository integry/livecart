[[ partial("layout/frontend/header.tpl") ]]
{* include file="layout/frontend/leftSide.tpl" *}

{% if $rightSide %}
	[[ partial("layout/frontend/rightSide.tpl") ]]
{% endif %}