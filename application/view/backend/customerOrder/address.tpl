<a id="order_{$order.ID}_{$type}_showView" href="#view" style="display: none">{t _view}</a>
<a id="order_{$order.ID}_{$type}_showEdit" href="#edit" >{t _edit}</a>

{literal}
<script>
    Element.observe($('{/literal}order_{$order.ID}_{$type}_showView{literal}'), 'click', function(e) { 
        Event.stop(e);
        $('{/literal}order_{$order.ID}_{$type}_edit{literal}').hide(); 
        $('{/literal}order_{$order.ID}_{$type}_view{literal}').show();
        $('{/literal}order_{$order.ID}_{$type}_showEdit{literal}').show(); 
        $('{/literal}order_{$order.ID}_{$type}_showView{literal}').hide();
    });

    Element.observe($('{/literal}order_{$order.ID}_{$type}_showEdit{literal}'), 'click', function(e) { 
        Event.stop(e);
        $('{/literal}order_{$order.ID}_{$type}_edit{literal}').show(); 
        $('{/literal}order_{$order.ID}_{$type}_view{literal}').hide();
        $('{/literal}order_{$order.ID}_{$type}_showEdit{literal}').hide(); 
        $('{/literal}order_{$order.ID}_{$type}_showView{literal}').show();
    });
</script>
{/literal}

<fieldset id="order_{$order.ID}_{$type}_edit" class="error" style="display: none">
    {hidden name="ID"} 
    
    <fieldset class="error">
        <label for="order_{$order.ID}_{$type}_existingAddress_select">{t _use_existing_address}</label>
        {selectfield options=$existingUserAddressOptions id="order_`$orderID`_`$type`_existingAddress_select" name="existingUserAddress"} 
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
        <span class="activeForm_progress"></span>
        <input type="submit" class="button submit" value="{t _save}" />
        {t _or}
        <a href="#cancel" class="cancel">{t _cancel}</a>
    </fieldset>
</fieldset>

<fieldset id="order_{$order.ID}_{$type}_view" class="container">
    <p>
        <label>{t _name}</label>
        <label>{$order.fullName}</label>    
    </p>    

    {if $order.companyName}
        <p>
            <label>{t _company}</label>    
            <label>{$order.companyName}</label>
        </p>
    {/if}
    
    <p>
        <label>{t _country}</label>    
        <label>{$order.countryName}</label>
    </p>
    
    <p>
        <label>{t _state}</label>    
        <label>
            {if $order.State.ID}
                {$order.State.name}
            {else}
                {$order.stateName}
            {/if}
        </label>
    </p>    
    
    <p>
        <label>{t _city}</label>    
        <label>{$order.city}</label>
    </p>
    
    <p>
        <label>{t _address}</label>    
        <label>{$order.address1}</label>
    </p>    
    
    <p>
        <label>{t _address}</label>    
        <label>{$order.address2}</label>
    </p>    
    
    <p>
        <label>{t _postal_code}</label>    
        <label>{$order.postalCode}</label>
    </p>
    
    <p>
        <label>{t _phone}</label>    
        <label>{$order.phone}</label>
    </p>
</fieldset>