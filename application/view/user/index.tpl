{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _your_account}</h1>
	
	<ul id="userMenu">
	   <li id="homeMenu">Your Account Home</li>
	   <li id="ordersMenu">Orders</li>
	   <li id="addressMenu">Addresses</li>
	   <li id="emailMenu">Change E-mail</li>
	   <li id="passwordMenu">Change Password</li>
	   <li><a href="{link controller=user action=logout}">{t Sign Out}</a></li>
	</ul>
	
	<div class="clear"></div>
    
    <h2>{t Your Recent Orders}</h2>
    {foreach from=$orders item="order"}    
	    {$order.formatted_dateCreated.date_long}
	    {t Status}: 
		{if $order.isCancelled}
	    	<span class="cancelled">{t Cancelled}</span>
	    {else}
	    	{if !$order.isPaid}
	    		<span class="awaitingPayment">{t Awaiting payment} ($order.formattedTotal)</span>.
	    		{t Make payment for this order}.
	    	{else}
	    		
	    	
	    	{/if}
	    {/if}
    {/foreach}
    
</div>

{include file="layout/frontend/footer.tpl"}