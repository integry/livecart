{% extends "layout/frontend.tpl" %}

{% title %}{t _manufacturers}{% endblock %}

{% block content %}

	{% if config('MANUFACTURER_PAGE_LIST_STYLE') == 'MANPAGE_STYLE_ALL_IN_ONE_PAGE' %}
		[[ partial("manufacturers/listAllInOnePage.tpl") ]]
	{% else %} {* if MANPAGE_STYLE_GROUP_BY_FIRST_LETTER *}
		[[ partial("manufacturers/listGroupByFirstLetter.tpl") ]]
	{% endif %}
	<div style="clear:both;"></div>
	{% if count > perPage && perPage > 0 %}
		{paginate current=currentPage count=count perPage=perPage url=url}
	{% endif %}

{% endblock %}

