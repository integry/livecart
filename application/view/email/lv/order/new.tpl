{'STORE_NAME'|config} pasūtījuma apstiprinājums
Cien. {$user.fullName},

Paldies par Jūsu {'STORE_NAME'|config} pasūtījumu. Ja nepieciešams griezties pie mums dēļ papildus informācijas sakarā ar šo pasūtījumus, lūdzu miniet pasūtījuma numuru #{$order.ID}.

Jūs varat sekot līdzi pasūtījuma izpildes gaitai no šīs lapas:
{link controller=user action=viewOrder id=$order.ID url=true}

Ja radušies kādi jautājumi sakarā ar pasūtījumu, no šīs pašas lapas varat tos mums arī nosūtīt.

Atgādinām, ka pasūtījāt sekojošos produktus:
------------------------------------------------------------
Produkts					   Cena	  Skaits	Summa
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
						   Summa pirms nodokļiem: {$order.formatted_subtotalBeforeTaxes}
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
Pasūtījumu apmaksā:
{fun name="address" address=$order.BillingAddress}
{/if}

{if $order.ShippingAddress}
Pasūtījums tiks piegādāts:
{fun name="address" address=$order.ShippingAddress}
{/if}

{include file="email/lv/signature.tpl"}