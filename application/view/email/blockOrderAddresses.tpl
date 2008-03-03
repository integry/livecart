
{if $order.BillingAddress}
{t _billing_address}:
{include file="email/blockAddress.tpl" address=$order.BillingAddress}
{/if}

{if $order.ShippingAddress}
{t _shipping_address}:
{include file="email/blockAddress.tpl" address=$order.ShippingAddress}
{/if}