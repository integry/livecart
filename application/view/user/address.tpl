<div class="address">
    <div class="addressBlock">
        <p>
            {$item.UserAddress.firstName} {$item.UserAddress.lastName}
        </p>
        
        {if $item.UserAddress.companyName}
        <p>
           {$item.UserAddress.companyName}
        </p>
        {/if}
        
        <p>
            {$item.UserAddress.address1}
        </p>
        
        {if $item.UserAddress.address2}
        <p>
            {$item.UserAddress.address2}
        </p>
        {/if}
        
        <p>
            {$item.UserAddress.city}
        </p>
        
        <p>
            {if $item.State.name}
                {$item.State.name},
            {else}
                {$item.UserAddress.stateName},
            {/if}
            {$item.UserAddress.postalCode}
        </p>
        
        <p>
            {$item.UserAddress.countryName}
        </p>
    </div>
    <div class="clear"></div>
</div>