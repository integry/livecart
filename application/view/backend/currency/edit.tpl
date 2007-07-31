{form action="controller=backend.currency action=save query=id=`$id`" onsubmit="Backend.Currency.prototype.saveFormat(this); return false;" handle=$form role="currency.update"}

<fieldset class="currencyPriceFormatting">

    <legend>{t Price Formatting}</legend>

    <p>
        <label>{t Price display prefix}:</label>
        {textfield name="pricePrefix" style="width: 100%;"}
    </p>

    <p>
        <label>{t Price display suffix}:</label>
        {textfield name="priceSuffix" style="width: 100%;"}
    </p>
    
    <fieldset class="controls">
        <span class="progressIndicator" style="display: none;"></span>
        <input type="submit" value="{tn _save}" class="submit"> 
        {t _or} 
        <a href="#cancel" onclick="this.parentNode.parentNode.parentNode.innerHTML = ''; return false;" class="cancel">{t _cancel}</a>
    </fieldset>
</fieldset>

{/form}