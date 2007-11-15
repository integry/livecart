New Order Placed at {'STORE_NAME'|config}
Order ID: {$order.ID}

Order administration:
{backendOrderUrl order=$order url=true}

The following items have been ordered:
------------------------------------------------------------
Item						   Price	 Qty	  Subtotal
------------------------------------------------------------
{foreach from=$order.shipments item=shipment}
{foreach from=$shipment.items item=item}
{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{/foreach}
{/foreach}
------------------------------------------------------------
										Subtotal: {$order.formatted_itemSubtotal}
{if $order.shippingSubtotal}
										Shipping: {$order.formatted_shippingSubtotal}
{/if}
{if $order.taxes|@count > 1}
						   ---------------------------------
						   Subtotal before taxes: {$order.formatted_subtotalBeforeTaxes}
{foreach from=$order.taxes[$order.Currency.ID] item=tax}
								{$tax.name_lang}: {$tax.formattedAmount}
{/foreach}
{/if}
						   ---------------------------------
									 Grand total: {$order.formatted_totalAmount}

									 Amount paid: {$order.formatted_amountPaid}
									  Amount due: {$order.formatted_amountDue}

{* Function to generate address output (address template) *}
{defun name="address"}
{if $address}
{$address.fullName}				
{if $address.companyName}
{$address.companyName}

{/if}
{if $address.address1}
{$address.address1}

{/if}
{if $address.address2}
{$address.address2}

{/if}
{$address.city}
{if $address.stateName}{$address.stateName}, {/if}{$address.postalCode}
{$address.countryName}
{/if}
{/defun}

{if $order.BillingAddress}
Billing address:
{fun name="address" address=$order.BillingAddress}
{/if}

{if $order.ShippingAddress}
Shipping address:
{fun name="address" address=$order.ShippingAddress}
{/if}

{include file="email/en/signature.tpl"}