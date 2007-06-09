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


<fieldset id="order_{$order.ID}_{$type}_view" class="error" >
    <label>{t _first_name}</label>
    <fieldset class="error">
        <div class="formDiv">{$order.firstName}</div>
    </fieldset> 
    
    
    <label>{t _last_name}</label>
    <fieldset class="error">
        <div class="formDiv">{$order.lastName}</div>
    </fieldset>
    
    
    <label>{t _country}</label>
    <fieldset class="error">
        <div class="formDiv">{$order.country} COUNTRY</div>
    </fieldset>
    
    
    <label>{t _state}</label>
    <fieldset class="error">
        <div class="formDiv">{$order.state} STATE</div>
    </fieldset>
    
    
    <label>{t _city}</label>
    <fieldset class="error">
        <div class="formDiv">{$order.city}</div>
    </fieldset>
    
    
    <label>{t _address}</label>
    <fieldset class="error">
        <div class="formDiv">{$order.address1}</div>
    </fieldset>
    
    
    <label>{t _address}</label>
    <fieldset class="error">
        <div class="formDiv">{$order.address2}</div>
    </fieldset>
    
    
    <label>{t _postal_code}</label>
    <fieldset class="error">
        <div class="formDiv">{$order.postalCode}</div>
    </fieldset>
    
    
    <label>{t _phone}</label>
    <fieldset class="error">
        <div class="formDiv">{$order.phone}</div>
    </fieldset>
</fieldset>
