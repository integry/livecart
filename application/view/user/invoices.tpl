{% extends "layout/frontend.tpl" %}

{% title %}{t _invoices}{% endblock %}
[[ partial("user/layout.tpl") ]]
[[ partial('user/userMenu.tpl', ['current': "invoicesMenu"]) ]]
{% block content %}

	[[ partial('user/invoicesTable.tpl', ['itemList': orders, 'paginateAction': "invoices", 'textDisplaying': _displaying_invoices, 'textFound': _invoices_found, 'id': 0, 'query': '']) ]]

{% endblock %}

