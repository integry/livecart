{pageTitle}{t _invoices}{/pageTitle}
{include file="user/layout.tpl"}
{include file="user/userMenu.tpl" current="invoicesMenu"}
{include file="block/content-start.tpl"}

	{include file="user/invoicesTable.tpl"
		itemList=$orders
		paginateAction="invoices"
		textDisplaying=_displaying_invoices
		textFound=_invoices_found
		id=0
		query=''
	}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}
