Saņemts jauns pasūtījums {'STORE_NAME'|config}
Pasūtījuma ID: {$order.ID}

Pasūtījuma administrācija:
{backendOrderUrl order=$order url=true}

Pasūtītas sekojošas preces:
------------------------------------------------------------
Produkts                       Cena      Skaits    Summa
------------------------------------------------------------
{foreach from=$order.shipments item=shipment}
{foreach from=$shipment.items item=item}
{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{/foreach}
{/foreach}
------------------------------------------------------------
                                           Summa: {$order.formatted_itemSubtotal}
{if $order.shippingSubtotal}
                                         Piegāde: {$order.formatted_shippingSubtotal}
{/if}
{if $order.taxes|@count > 1}
                           ---------------------------------
                            Kopā pirms nodokļiem: {$order.formatted_subtotalBeforeTaxes}
{foreach from=$order.taxes[$order.Currency.ID] item=tax}
                                {$tax.name_lang}: {$tax.formattedAmount}
{/foreach}
{/if}
                           ---------------------------------
                                            Kopā: {$order.formatted_totalAmount}

                                       Samaksāts: {$order.formatted_amountPaid}
                                         Jāmaksā: {$order.formatted_amountDue}

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
Maksājuma adrese:
{fun name="address" address=$order.BillingAddress}
{/if}

{if $order.ShippingAddress}
Piegādes adrese:
{fun name="address" address=$order.ShippingAddress}
{/if}

{include file="email/lv/signature.tpl"}