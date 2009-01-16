
{if $order.BillingAddress}
<br /><b>{t _billing_address}:</b>
{include file="email/blockAddress.tpl" address=$order.BillingAddress}
{/if}

{if $order.ShippingAddress}
<b>{t _shipping_address}:</b>
{include file="email/blockAddress.tpl" address=$order.ShippingAddress}
{/if}