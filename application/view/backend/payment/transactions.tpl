<ul class="transactions">

	{foreach from=$transactions item="transaction"}
		
		{include file="backend/payment/transaction.tpl"}
	
	{/foreach}

</ul>   