<div class="userViewOrder">

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">

    <h1>{t _view_order} {$order.ID} ({$order.formatted_dateCreated.date_long})</h1>
    
	{include file="user/userMenu.tpl" current="ordersMenu"}    
    
	This feature will be implemented in the Second Beta version.

</div>

{include file="layout/frontend/footer.tpl"}    

</div>