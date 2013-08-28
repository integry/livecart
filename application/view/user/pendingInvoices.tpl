{% extends "layout/frontend.tpl" %}

{% block title %}{t _pending_invoices}{{% endblock %}
{include file="user/layout.tpl"}
{include file="user/userMenu.tpl" current="pendingInvoicesMenu"}
{% block content %}

	{include file="user/invoicesTable.tpl"
		itemList=$orders
		paginateAction="pendingInvoices"
		textDisplaying=_displaying_invoices
		textFound=_invoices_found
		id=0
		query=''
	}

{% endblock %}

