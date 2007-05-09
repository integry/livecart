<fieldset class="container">
	<ul class="menu" id="tax_new_menu">
	    <li><a href="#new_tax" id="tax_new_show">{t _add_new_tax}</a></li>
	    <li><a href="#cencel_tax" id="tax_new_cancel" class="hidden">{t _cancel_adding_new_tax}</a></li>
	</ul>
</fieldset>




<fieldset id="tax_new_form" style="display: none;">
    {include file="backend/tax/tax.tpl" tax=$newTax taxForm=$newTaxForm}
</fieldset>




<ul class="activeList activeList_add_delete activeList_add_edit tax_taxesList" id="tax_taxesList" style="height: auto;" >
{foreach from=$taxesForms key="key" item="taxForm"}
    <li id="tax_taxesList_{$taxes[$key].ID}">
        
	<span class="error tax_viewMode">
	    {$taxes[$key].name}
        (<strong>{if $taxes[$key].isEnabled}{t _enabled}{else}{t _disabled}{/if}</strong>)
	</span>
     
    </li>
{/foreach}
</ul>




{literal}
<script type="text/javascript">
  
    try
    {
        Event.observe($("tax_new_show"), "click", function(e) 
        {
            Event.stop(e);
            var newForm = Backend.Tax.prototype.getInstance( $("tax_new_form").down('form') );
            newForm.showNewForm();
        });   
    }
    catch(e)
    {
        console.info(e);
    }
    

console.info("tax_taxesList", Backend.Tax.prototype.Callbacks);
ActiveList.prototype.getInstance("tax_taxesList", Backend.Tax.prototype.Callbacks, function() {});
</script>
{/literal}
