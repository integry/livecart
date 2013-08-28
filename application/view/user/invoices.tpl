{% extends "layout/frontend.tpl" %}

{% block title %}{t _invoices}{{% endblock %}
[[ partial("user/layout.tpl") ]]
{include file="user/userMenu.tpl" current="invoicesMenu"}
{% block content %}

	{include file="user/invoicesTable.tpl"
		itemList=$orders
		paginateAction="invoices"
		textDisplaying=_displaying_invoices
		textFound=_invoices_found
		id=0
		query=''
	}

{% endblock %}

