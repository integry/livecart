[[ partial("layout/frontend/header.tpl") ]]
{% if empty(hideLeft) %}
	[[ partial("layout/frontend/leftSide.tpl") ]]
{% endif %}
[[ partial("layout/frontend/rightSide.tpl") ]]