{* Function to generate address output (address template) *}
{if $address}
[[address.fullName]]
{if $address.companyName}
[[address.companyName]]
{/if}
{if $address.address1}
[[address.address1]]
{/if}
{if $address.address2}
[[address.address2]]
{/if}
{if $address.city}
[[address.city]]
{/if}
{if $address.stateName}[[address.stateName]]{if $address.postalCode}, {/if}{/if}[[address.postalCode]]
{if $address.countryName}
[[address.countryName]]
{/if}{include file="order/addressFieldValues.tpl" showLabels=false}
{/if}