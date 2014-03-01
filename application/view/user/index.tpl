{% extends "layout/frontend.tpl" %}

{% block title %}{t _your_account} ([[user.fullName]]){% endblock %}
[[ partial("user/layout.tpl") ]]
[[ partial('user/userMenu.tpl', ['current': "homeMenu"]) ]]
{% block content %}

	{% if !empty(userConfirm) %}
	<div class="confirmationMessage">
		[[userConfirm]]
	</div>
	{% endif %}

	{% if !empty(message) %}
		<div class="confirmationMessage">[[message]]</div>
	{% endif %}

	{% if !empty(notes) %}
		<h2 id="unreadMessages">{t _unread_msg}</h2>
		<ul class="notes">
			{foreach from=$notes item=note}
			   <a href="[[ url("user/viewOrder/" ~ note.orderID) ]]#msg">{t _order} #[[note.orderID]]</a>
			   [[ partial('user/orderNote.tpl', ['note': note]) ]]
			{/foreach}
		</ul>
	{% endif %}

	{% if !empty(files) %}
		<h2 id="recentDownloads">{t _download_recent}</h2>

		{foreach from=$files item="item"}
			<h3>
				<a href="[[ url("user/item/" ~ item.ID) ]]">[[item.Product.name()]]</a>
			</h3>
			[[ partial('user/fileList.tpl', ['item': item]) ]]
			<div class="clear"></div>
		{/foreach}
	{% endif %}

	{% if !empty(orders) %}
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
