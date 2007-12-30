{'butikknavnet_ditt'|config} Ordre bekreftelse
Kjære {$user.fullName},

Takk for din bestilling fra {'butikknavnet_ditt'|config}. Hvis du har behov for å kontakte oss ang. denne bestillingen, vennligst oppgi følgende bestillingsnummer #{$order.ID}.

Du kan følge din bestilling på denne siden:
{link controller=user action=viewOrder id=$order.ID url=true}

Om du har spørmaål ang. denne bestillingen, kan du også sende en beskjed på linken over.

Vi minner om at følgende produkter er bestilt:
------------------------------------------------------------
Produkt						   Pris	 Antall	  Sum
------------------------------------------------------------
{foreach from=$order.shipments item=shipment}
{foreach from=$shipment.items item=item}
{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{/foreach}
{/foreach}
------------------------------------------------------------
										Sum: {$order.formatted_itemSubtotal}
{if $order.shippingSubtotal}
										Porto: {$order.formatted_shippingSubtotal}
{/if}
{if $order.taxes|@count > 1}
						   ---------------------------------
										Sum eksl. moms: {$order.formatted_subtotalBeforeTaxes}
{foreach from=$order.taxes[$order.Currency.ID] item=tax}
								{$tax.name_lang}: {$tax.formattedAmount}
{/foreach}
{/if}
						   ---------------------------------
										Sum totalt: {$order.formatted_totalAmount}

										Betalt beløp: {$order.formatted_amountPaid}
										Betalingsfrist: {$order.formatted_amountDue}

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
Bestillingen er betalt av:
{fun name="address" address=$order.BillingAddress}
{/if}

{if $order.ShippingAddress}
Bestillingen vil bli sendt til:
{fun name="address" address=$order.ShippingAddress}
{/if}

{include file="email/en/signature.tpl"}