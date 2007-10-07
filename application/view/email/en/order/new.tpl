{'STORE_NAME'|config} Order Confirmation
Dear {$user.fullName},

Thank you for your order, which you just placed at {'STORE_NAME'|config}. If you need to contact us regarding this order, please quote the order ID #{$order.ID}.

You will be able to track the progress of your order at this page:
{link controller=user action=viewOrder id=$order.ID url=true}

If you have any questions regarding this order, you can send us a message from the above page as well.

We remind that the following items have been ordered:
------------------------------------------------------------
Item                           Price     Qty      Subtotal
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
The order is billed to:
{fun name="address" address=$order.BillingAddress}
{/if}

{if $order.ShippingAddress}
The order will be shipped to:
{fun name="address" address=$order.ShippingAddress}
{/if}

{include file="email/en/signature.tpl"}