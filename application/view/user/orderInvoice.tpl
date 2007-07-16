<div class="userOrderInvoice">

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">

    <h1>{t _order_invoice} {$order.ID} ({$order.formatted_dateCompleted.date_long})</h1>
    
	{include file="user/userMenu.tpl" current="ordersMenu"}    
    
    <div id="userContent">
    
	This feature will be implemented in the Second Beta version.

    </div>

</div>

{include file="layout/frontend/footer.tpl"}    

</div>