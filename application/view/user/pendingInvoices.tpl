{% extends "layout/frontend.tpl" %}

{% block title %}{t _pending_invoices}{{% endblock %}
[[ partial("user/layout.tpl") ]]
[[ partial('user/userMenu.tpl', ['current': "pendingInvoicesMenu"]) ]]
{% block content %}

	[[ partial('user/invoicesTable.tpl', ['itemList': $orders, 'paginateAction': "pendingInvoices", 'textDisplaying': _displaying_invoices, 'textFound': _invoices_found, 'id': 0, 'query': '']) ]]

{% endblock %}

