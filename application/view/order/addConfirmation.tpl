{capture assign="body"}
	{include file="order/changeMessages.tpl"}

	{if $error}
		<div class="errorMessage">[[error]]</div>
	{/if}

	<p class="addedToCart">[[msg]]</p>
{/capture}

{capture assign="footer"}
	{include file="order/block/navigationButtons.tpl" hideTos=true}
{/capture}

{include file="block/modal.tpl" title="_item_added_title" body=$body footer=$footer}
