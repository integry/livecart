{if $hasInvoices}
	<li id="invoicesMenu" class="{if "invoicesMenu" == $current}selected{/if}"><a href="{link controller=user action=invoices}">{t _invoices}</a></li>
{/if}

{if $hasPendingInvoices}
	<li id="pendingInvoicesMenu" class="{if "pendingInvoicesMenu" == $current}selected{/if}"><a href="{link controller=user action=pendingInvoices}">{t _pending_invoices}</a></li>
{/if}
