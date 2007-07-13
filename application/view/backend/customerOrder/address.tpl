<a class="orderAddress_showEdit" href="#edit" style="{denied role='order.update'}display: none{/denied}" >{t _edit}</a>

<fieldset id="order_{$order.ID}_{$type}_edit" class="orderAddress_edit error" style="display: none">
    {hidden name="ID"} 
    <input name="orderID" type="hidden" value="{$order.ID}" />
    
    <fieldset class="error">
        <label for="order_{$order.ID}_{$type}_existingAddress_select">{t _use_existing_address}</label>
        {selectfield options=$existingUserAddressOptions id="order_`$order.ID`_`$type`_existingAddress_select" name="existingUserAddress"} 
    </fieldset>

    <fieldset class="error">
        <label for="order_{$order.ID}_{$type}_firstName">{t _first_name}</label>
        {textfield name="firstName" id="order_`$order.ID`_`$type`_firstName"}
        <div class="errorText" style="display: none" ></span>
    </fieldset>
    
    <fieldset class="error">
        <label for="order_{$order.ID}_{$type}_lastName">{t _last_name}</label>
        {textfield name="lastName" id="order_`$order.ID`_`$type`_lastName"}
        <div class="errorText" style="display: none" ></span>
    </fieldset>
    
    <fieldset class="error">
        <label for="order_{$order.ID}_{$type}_countryID">{t _country}</label>
        {selectfield name="countryID" options=$countries id="order_`$order.ID`_`$type`_countryID"}
        <div class="errorText" style="display: none" ></span>
    </fieldset>
    
    <fieldset class="error">
        <label for="order_{$order.ID}_{$type}_stateID">{t _state}</label>
        {selectfield name="stateID" options=$states id="order_`$order.ID`_`$type`_stateID"}
        {textfield name="stateName" id="order_`$order.ID`_`$type`_stateName"}
    </fieldset>
    
    <fieldset class="error">
        <label for="order_{$order.ID}_{$type}_city">{t _city}</label>
        {textfield name="city" id="order_`$order.ID`_`$type`_city"}
        <div class="errorText" style="display: none" ></span>
    </fieldset>
    
    <fieldset class="error">
        <label for="order_{$order.ID}_{$type}_address1">{t _address} 1</label>
        {textfield name="address1" id="order_`$order.ID`_`$type`_address1"}
        <div class="errorText" style="display: none" ></span>
    </fieldset>
    
    <fieldset class="error">
        <label for="order_{$order.ID}_{$type}_address2">{t _address} 2</label>
        {textfield name="address2" id="order_`$order.ID`_`$type`_address2"}
        <div class="errorText" style="display: none" ></span>
    </fieldset>
    
    <fieldset class="error">
        <label for="order_{$order.ID}_{$type}_postalCode">{t _postal_code}</label>
        {textfield name="postalCode" id="order_`$order.ID`_`$type`_postalCode"}
        <div class="errorText" style="display: none" ></span>
    </fieldset>
    
    <fieldset class="error">
        <label for="order_{$order.ID}_{$type}_phone">{t _phone}</label>
        {textfield name="phone" id="order_`$order.ID`_`$type`_phone"}
    </fieldset>
    
    <fieldset class="controls">
        <span style="display: none;" class="progressIndicator"></span>
        
        <input type="submit" class="button submit" value="{t _save}" />
        {t _or}
        <a href="#cancel" class="cancel">{t _cancel}</a>
    </fieldset>
</fieldset>

<fieldset id="order_{$order.ID}_{$type}_view" class="container orderAddress_view">
    <p>
        <label>{t _name}</label>
        <label>{$address.fullName}</label>    
    </p>    

    {if $order.companyName}
        <p>
            <label>{t _company}</label>    
            <label>{$address.companyName}</label>
        </p>
    {/if}
    
    <p>
        <label>{t _country}</label>    
        <label>{$address.countryName}</label>
    </p>
    
    <p>
        <label>{t _state}</label>    
        <label>
            {if $address.State.ID}
                {$address.State.name}
            {else}
                {$address.stateName}
            {/if}
        </label>
    </p>    
    
    <p>
        <label>{t _city}</label>    
        <label>{$address.city}</label>
    </p>
    
    <p>
        <label>{t _address} 1</label>    
        <label>{$address.address1}</label>
    </p>    
    
    <p>
        <label>{t _address} 2</label>    
        <label>{$address.address2}</label>
    </p>    
    
    <p>
        <label>{t _postal_code}</label>    
        <label>{$address.postalCode}</label>
    </p>
    
    <p>
        <label>{t _phone}</label>    
        <label>{$address.phone}</label>
    </p>
</fieldset>