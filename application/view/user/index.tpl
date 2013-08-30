{% extends "layout/frontend.tpl" %}

{% block title %}{t _your_account} ([[user.fullName]]){{% endblock %}
[[ partial("user/layout.tpl") ]]
[[ partial('user/userMenu.tpl', ['current': "homeMenu"]) ]]
{% block content %}

	{% if $userConfirm %}
	<div class="confirmationMessage">
		[[userConfirm]]
	</div>
	{% endif %}

	{% if $message %}
		<div class="confirmationMessage">[[message]]</div>
	{% endif %}

	{% if $notes %}
		<h2 id="unreadMessages">{t _unread_msg}</h2>
		<ul class="notes">
			{foreach from=$notes item=note}
			   <a href="{link controller=user action=viewOrder id=$note.orderID}#msg">{t _order} #[[note.orderID]]</a>
			   [[ partial('user/orderNote.tpl', ['note': note]) ]]
			{/foreach}
		</ul>
	{% endif %}

	{% if $files %}
		<h2 id="recentDownloads">{t _download_recent}</h2>

		{foreach from=$files item="item"}
			<h3>
				<a href="{link controller=user action=item id=$item.ID}">[[item.Product.name_lang]]</a>
			</h3>
			[[ partial('user/fileList.tpl', ['item': item]) ]]
			<div class="clear"></div>
		{/foreach}
	{% endif %}

	{% if $orders %}
		<h2 id="recentOrders">{t _recent_orders}</h2>
		{foreach from=$orders item="order"}
			[[ partial('user/orderEntry.tpl', ['order': order]) ]]
		{/foreach}
	{% else %}
		<p>
			{t _no_orders_placed}
		</p>
	{% endif %}

	<div class="clear"></div>

	{% if $pendingInvoiceCount > 0 %}
		<h2>{t _invoices}</h2>
		[[ partial('user/invoicesTable.tpl', ['itemList': lastInvoiceArray, 'paginateAction': "pendingInvoice", 'textDisplaying': _displaying_invoices, 'textFound': _invoices_found, 'id': 0, 'query': '', 'pendingInvoiceCount': pendingInvoiceCount]) ]]
	{% endif %}

{% endblock %}
