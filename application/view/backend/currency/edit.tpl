{form action="controller=backend.currency action=save query=id=`$id`" onsubmit="Backend.Currency.prototype.saveFormat(this); return false;" handle=$form role="currency.update"}

<fieldset class="currencyPriceFormatting">

    <legend>{t _pr_form}</legend>

    <p>
        <label>{t _pr_prefix}:</label>
        {textfield name="pricePrefix" class="currencyPricePrefix"}
    </p>

    <p>
        <label>{t _pr_suffix}:</label>
        {textfield name="priceSuffix" class="currencyPriceSuffix"}
    </p>
    
    <fieldset class="controls">
        <span class="progressIndicator" style="display: none;"></span>
        <input type="submit" value="{tn _save}" class="submit"> 
        {t _or} 
        <a href="#cancel" onclick="this.parentNode.parentNode.parentNode.innerHTML = ''; return false;" class="cancel">{t _cancel}</a>
    </fieldset>
</fieldset>

{/form}