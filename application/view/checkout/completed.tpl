<div class="checkoutCompleted">

{pageTitle}{t _order_completed}{/pageTitle}

{include file="layout/frontend/layout.tpl"}

<div id="content" class="left right">

	<h1>{t _order_completed}</h1>

	{if $order.isPaid}
		{t _completed_paid}
	{else}
		{t _completed_not_paid}
	{/if}

</div>

{include file="layout/frontend/footer.tpl"}

</div>