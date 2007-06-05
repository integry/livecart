{hidden name="ID"}

<label for="order_{$order.ID}_{$type}_firstName">{t _first_name}</label>
<fieldset class="error">
    {textfield name="firstName" id="order_`$order.ID`_`$type`_firstName"}
    <div class="errorText" style="display: none" ></span>
</fieldset>

<label for="order_{$order.ID}_{$type}_lastName">{t _last_name}</label>
<fieldset class="error">
    {textfield name="lastName" id="order_`$order.ID`_`$type`_lastName"}
    <div class="errorText" style="display: none" ></span>
</fieldset>

<label for="order_{$order.ID}_{$type}_countryID">{t _country}</label>
<fieldset class="error">
    {selectfield name="countryID" options=$countries id="order_`$order.ID`_`$type`_countryID"}
    <div class="errorText" style="display: none" ></span>
</fieldset>

<label for="order_{$order.ID}_{$type}_stateID">{t _state}</label>
<fieldset class="error">
    {selectfield name="stateName" options=$states id="order_`$order.ID`_`$type`_stateID"}
</fieldset>

<label for="order_{$order.ID}_{$type}_stateName">{t _other_state}</label>
<fieldset class="error">
    {textfield name="stateName" id="order_`$order.ID`_`$type`_stateName"}
</fieldset>

<label for="order_{$order.ID}_{$type}_city">{t _city}</label>
<fieldset class="error">
    {textfield name="city" id="order_`$order.ID`_`$type`_city"}
    <div class="errorText" style="display: none" ></span>
</fieldset>

<label for="order_{$order.ID}_{$type}_address1">{t _address} 1</label>
<fieldset class="error">
    {textfield name="address1" id="order_`$order.ID`_`$type`_address1"}
    <div class="errorText" style="display: none" ></span>
</fieldset>

<label for="order_{$order.ID}_{$type}_address2">{t _address} 2</label>
<fieldset class="error">
    {textfield name="address2" id="order_`$order.ID`_`$type`_address2"}
    <div class="errorText" style="display: none" ></span>
</fieldset>

<label for="order_{$order.ID}_{$type}_postalCode">{t _postal_code}</label>
<fieldset class="error">
    {textfield name="postalCode" id="order_`$order.ID`_`$type`_postalCode"}
    <div class="errorText" style="display: none" ></span>
</fieldset>

<label for="order_{$order.ID}_{$type}_phone">{t _phone}</label>
<fieldset class="error">
    {textfield name="phone" id="order_`$order.ID`_`$type`_phone"}
</fieldset>

<fieldset class="controls">
    <span class="activeForm_progress"></span>
    <input type="submit" class="button submit" value="{t _save}" />
    {t _or}
    <a href="#cancel" class="cancel">{t _cancel}</a>
</fieldset>