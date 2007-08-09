<div class="checkoutPay">

{loadJs form=true}
{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _pay}</h1>
		   	
	<div id="payTotal">
        <div>
			Order total: <span class="subTotal">{$order.formattedTotal.$currency}</span>
		</div>
    </div>
		   	
    <div class="clear"></div> 
    
	<form action="{link controller=checkout action=payExpressComplete}" method="POST" id="expressComplete">
    
	    <input type="submit" class="submit" value="{tn Complete Your Order}" />

    </form>
    
    <div class="clear"></div> 

    {include file="checkout/orderOverview.tpl"}
    
</div>

{include file="layout/frontend/footer.tpl"}

</div>