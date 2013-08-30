<!--
{form action="order/update" method="POST" handle=$form id="cartItems"}
-->

{foreach from=$options[$item.ID] item=option}
	{include file="product/optionItem.tpl" selectedChoice=$item.options[$option.ID]}
{/foreach}

<!--
{/form}
-->