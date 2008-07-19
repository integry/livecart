{if 'ENABLE_RATINGS'|config && !$isRated}
<h2>{maketext text="_rate_product_name" params=$product.name_lang}</h2>
<div id="rateProduct">
	{if $isLoginRequiredToRate}
		<p>{maketext text=_msg_rating_login_required params={link user/login}}</p>
	{elseif $isPurchaseRequiredToRate}
		<p>{t _msg_rating_purchase_required}</p>
	{else}
		{include file="product/rate.tpl"}
	{/if}
</div>
{/if}
