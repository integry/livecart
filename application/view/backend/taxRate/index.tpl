{* upper menu *}
<fieldset class="container">
	<ul class="menu" id="taxRate_menu_{$deliveryZone.ID}">
	    <li><a href="#new_taxRate" id="taxRate_new_{$deliveryZone.ID}_show">{t _add_new_tax_rate}</a></li>
	    <li><a href="#cencel_taxRate" id="taxRate_new_{$deliveryZone.ID}_cancel" class="hidden">{t _cancel_adding_new_tax_rate}</a></li>
	</ul>
</fieldset>

{* new form *}
<fieldset id="taxRate_new_taxRate_{$deliveryZone.ID}_form" style="display: none;">
    {include file="backend/taxRate/rate.tpl" taxRate=$newTaxRate}
</fieldset>

<ul class="activeList activeList_add_delete activeList_add_edit taxRate_taxRatesList" id="taxRate_taxRatesList_{$deliveryZone.ID}">
{foreach from=$taxRates item="taxRate"}
    <li id="taxRate_taxRatesList_{$deliveryZone.ID}_{$taxRate.ID}">
        <span class="taxRate_taxRatesList_title">{$taxRate.Tax.name}</span>
    </li>
{/foreach}
</ul>

{literal}
<script type="text/jscript">
    try
    {
        Event.observe($("taxRate_new_{/literal}{$deliveryZone.ID}{literal}_show"), "click", function(e) 
        {
            Event.stop(e);
            var newForm = Backend.DeliveryZone.TaxRate.prototype.getInstance(
                $("taxRate_new_taxRate_{/literal}{$deliveryZone.ID}{literal}_form").down('form'),
                {/literal}{json array=$newTaxRate}{literal}
            );
            
            newForm.showNewForm();
        });   
    }
    catch(e)
    {
        console.info(e);
    }

    ActiveList.prototype.getInstance("taxRate_taxRatesList_{/literal}{$deliveryZone.ID}{literal}", Backend.DeliveryZone.TaxRate.prototype.Callbacks, function() {});
</script>
{/literal}
