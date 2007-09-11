{form id="taxRate_`$taxRate.DeliveryZone.ID`_`$taxRate.ID`" handle=$form  action="controller=backend.TaxRate action=save id=`$taxRate.ID`" method="post" onsubmit="Backend.DeliveryZone.TaxRate.prototype.getInstance(this).save(); return false;"}
    <input type="hidden" name="deliveryZoneID" value="{$taxRate.DeliveryZone.ID}" class="taxRate_deliveryZoneID" />
    <input type="hidden" name="taxRateID" value="{$taxRate.ID}" class="taxRate_tarRateID" />
    
    <label for="taxRate_{$taxRate.DeliveryZone.ID}_{$taxRate.ID}_taxID">{t _tax}</label>
    {if !$taxRate.ID}
        <fieldset class="error">
    		{selectfield name="taxID" options=$enabledTaxes class="taxRate_taxID" id="taxRate_`$taxRate.DeliveryZone.ID`_`$taxRate.ID`_taxID" role="delivery.update"}
    		<span class="errorText hidden"> </span>
            <div class="noAwailableTaxes" {if $enabledTaxes|@count > 0}style="display: none"{/if}>
                {t _more_taxes_can_be_added} 
                <a href="{link controller=backend.tax action=index}">{t _here}</a>
            </div>
        </fieldset>  
    {else}
        <fieldset class="taxRate_taxIDFieldset">{$taxRate.Tax.name}</fieldset>
        <input type="hidden" name="taxID" value="{$taxRate.Tax.ID}" />
    {/if}
    
    <label for="taxRate_{$taxRate.DeliveryZone.ID}_{$taxRate.ID}_rate">{t _rate}</label>
    <fieldset class="error">
        {textfield name="rate" class="observed taxRate_rate number" id="taxRate_`$taxRate.DeliveryZone.ID`_`$taxRate.ID`_rate" role="delivery.update"} %
        <span class="errorText hidden"> </span>
    </fieldset>

    <fieldset class="taxRate_controls" {denied role="delivery.update"}style="display: none;"{/denied}>
        <span class="progressIndicator" style="display: none;"></span>
        <input type="submit" class="taxRate_save button submit" value="{t _save}" />
        {t _or}
        <a href="#cancel" class="taxRate_cancel cancel">{t _cancel}</a>
    </fieldset>
{/form}