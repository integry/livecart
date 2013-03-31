<div class="userIndex">

{include file="user/layout.tpl"}

{include file="user/userMenu.tpl" current="homeMenu"}
{include file="block/content-start.tpl"}

	<h1>{t _your_account} ({$user.fullName})</h1>

	{if $userConfirm}
	<div class="confirmationMessage">
		{$userConfirm}
	</div>
	{/if}

	{if $message}
		<div class="confirmationMessage">{$message}</div>
	{/if}

	{if $notes}
		<h2 id="unreadMessages">{t _unread_msg}</h2>
		<ul class="notes">
			{foreach from=$notes item=note}
			   <a href="{link controller=user action=viewOrder id=$note.orderID}#msg">{t _order} #{$note.orderID}</a>
			   {include file="user/orderNote.tpl" note=$note}
			{/foreach}
		</ul>
	{/if}

	{if $files}
		<h2 id="recentDownloads">{t _download_recent}</h2>

		{foreach from=$files item="item"}
			<h3>
				<a href="{link controller=user action=item id=$item.ID}">{$item.Product.name_lang}</a>
			</h3>
			{include file="user/fileList.tpl" item=$item}
			<div class="clear"></div>
		{/foreach}
	{/if}

	{if $orders}
		<h2 id="recentOrders">{t _recent_orders}</h2>
		{foreach from=$orders item="order"}
			{include file="user/orderEntry.tpl" order=$order}
		{/foreach}
	{else}
		<p>
			{t _no_orders_placed}
		</p>
	{/if}

	<div class="clear"></div>

	{if $pendingInvoiceCount > 0}
		<h2>{t _invoices}</h2>
		{include file="user/invoicesTable.tpl"
			itemList=$lastInvoiceArray
			paginateAction="pendingInvoice"
			textDisplaying=_displaying_invoices
			textFound=_invoices_found
			id=0
			query=''
			pendingInvoiceCount=$pendingInvoiceCount
		}
	{/if}

{include file="block/content-stop.tpl"}

{include file="layout/frontend/footer.tpl"}

</div>