{if $shipment.ID}
    {assign var="action" value="controller=backend.shipment action=update id=`$shipment.ID`"}
{else}
    {assign var="action" value="controller=backend.shipment action=create"}
{/if}


{form handle=$shipmentForm action=$action id="shipmentForm_`$shipment.ID`" method="post" onsubmit="Backend.Shipment.getInstance(this).save(); return false;" role="order.update(edit),order.create(index)"}
	
    {hidden name="ID"}
    {hidden name="orderID"}
    
    <label>{t _shipment_service}</label>
    <fieldset class="error">
        {selectfield name="shippingServiceID" options=$shippingServices}
        <span class="errorText" style="display: none" />
	</fieldset>
    
    <label>{t _shipment_status}</label>
    <fieldset class="error">
        {selectfield name="status" options=$statuses}
        <span class="errorText" style="display: none" />
	</fieldset>
    
    <fieldset class="controls">
        <span class="progressIndicator" style="display: none;"></span>
        <input type="submit" class="button submit" value="{t _save}" />
        {t _or}
        <a href="#cancel" class="cancel">{t _cancel}</a>
    </fieldset>
    
{/form}