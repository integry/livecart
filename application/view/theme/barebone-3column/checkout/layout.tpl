[[ partial("layout/frontend/header.tpl") ]]
{* include file="layout/frontend/leftSide.tpl" *}

{% if !empty(rightSide) %}
	[[ partial("layout/frontend/rightSide.tpl") ]]
{% endif %}