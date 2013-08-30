[[ partial("layout/frontend/header.tpl") ]]
{% if !$hideLeft %}
	[[ partial("layout/frontend/leftSide.tpl") ]]
{% endif %}
[[ partial("layout/frontend/rightSide.tpl") ]]