{form action="controller=backend.currency action=save query=id=`$id`" onsubmit="Backend.Currency.prototype.saveFormat(this); return false;" handle=$form role="curency.update"}

<fieldset style="width: 70%; margin-top: 15px;">

    <legend>{t Price Formatting}</legend>

    <p>
        <label>{t Price display prefix}:</label>
        {textfield name="pricePrefix" style="width: 100%;"}
    </p>

    <p>
        <label>{t Price display suffix}:</label>
        {textfield name="priceSuffix" style="width: 100%;"}
    </p>
    
<span class="progressIndicator" style="display: none;"></span>

<span class="controls">
    <input type="submit" value="{tn _save}" class="submit"> 
    {t _or} 
    <a href="#cancel" onclick="this.parentNode.parentNode.parentNode.innerHTML = ''; return false;" class="cancel">{t _cancel}</a>
    </span>
</fieldset>

{/form}