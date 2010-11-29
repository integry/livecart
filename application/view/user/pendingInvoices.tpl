<div class="userInvoices">
	{include file="user/layout.tpl"}
	{include file="user/userMenu.tpl" current="pendingInvoicesMenu"}
	<div id="content">
		<h1>{t _pending_invoices}</h1>
		{include file="user/invoicesTable.tpl"
			itemList=$orders
			paginateAction="pendingInvoices"
			textDisplaying=_displaying_invoices
			textFound=_invoices_found
			id=0
			query=''
		}
	</div>
	{include file="layout/frontend/footer.tpl"}
</div>