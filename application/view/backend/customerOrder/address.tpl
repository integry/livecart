<ul class="menu orderAddress_showEdit">
    <li class="order_editAddress">
        <a href="#edit" {denied role='order.update'}style="display: none"{/denied} >{t _edit}</a>
    </li>
</ul>
<div class="clear"></div>

<fieldset id="{$type}_edit" class="orderAddress_edit error" style="display: none">
    <input name="orderID" type="hidden" value="{$order.ID}" />
    
    <fieldset class="error">
        <label for="{$type}_existingAddress_select">{t _use_existing_address}</label>
        {selectfield options=$existingUserAddressOptions id="`$type`_existingAddress_select" name="existingUserAddress"} 
    </fieldset>

    {include file=backend/user/address_edit.tpl idPrefix=$type states=$states}

    <fieldset class="controls">
        <span style="display: none;" class="progressIndicator"></span>
        
        <input type="submit" class="button submit" value="{t _save}" />
        {t _or}
        <a href="#cancel" class="cancel">{t _cancel}</a>
    </fieldset>
</fieldset>

<fieldset id="{$type}_view" class="container orderAddress_view">
    {include file=backend/user/address_view.tpl idPrefix=$type address=$address}
</fieldset>