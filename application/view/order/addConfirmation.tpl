<a class="cancel cartPopupClose popupClose" href="#">{t _close}</a>

{include file="order/changeMessages.tpl"}

{if $error}
	<div class="errorMessage">{$error}</div>
{/if}

<p class="addedToCart">{$msg}</p>

{include file="order/block/navigationButtons.tpl" hideTos=true}