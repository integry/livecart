{form handle=$taxForm action="controller=backend.tax action=save" id="taxForm_`$tax.ID`" method="post" onsubmit="Backend.Tax.prototype.getInstance(this).save(); return false;"}
	{hidden name="ID"}
    
    
    <fieldset class="error">
        <label>{t _name}</label>
        {textfield name="name"}
        <span class="errorText" style="display: none" />
	</fieldset>
    
    <fieldset class="checkbox error">
	    {checkbox name="isEnabled" class="checkbox"}
        <label class="checkbox">{t _enabled}</label>
        <span class="errorText" style="display: none" />
	</fieldset>
    
    
    <fieldset>
    {foreach from=$alternativeLanguagesCodes item=lang}
        <fieldset class="expandingSection">
            <legend>Translate to: {$lang.name}</legend>
            <div class="expandingSectionContent">
                <label>{t _name}</label>
                <fieldset class="error">
                    {textfield name="name_`$lang.ID`" class="observed"}
                    <span class="errorText hidden"> </span>
                </fieldset>
            </div>
        </fieldset>
    {/foreach}
    </fieldset>
    
    
    <fieldset class="tax_controls">
        <span class="activeForm_progress"></span>
        <input type="submit" class="tax_save button submit" value="{t _save}" />
        {t _or}
        <a href="#cancel" class="tax_cancel cancel">{t _cancel}</a>
    </fieldset>
{/form}