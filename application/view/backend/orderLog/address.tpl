{$address.firstName} {$address.lastName}
{if $address.firstName || $address.lastName}<br />{/if}

{if $address.companyName}{$address.companyName}<br />{/if}

{if $address.address1}{$address.address1}<br />{/if}
{if $address.address2}{$address.address2}<br />{/if}

{if $address.city || $address.postalCode}
    {$address.city}{if $address.city && $address.postalCode},{/if}
    {$address.postalCode}
    <br />
{/if}

{if $address.countryName}{$address.countryName}<br />{/if}