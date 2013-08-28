<ul class="transactions">

	{foreach from=$transactions item="transaction"}
		
		[[ partial("backend/payment/transaction.tpl") ]]
	
	{/foreach}

</ul>   