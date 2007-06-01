{if $tax.ID}
    {assign var="action" value="controller=backend.tax action=update id=`$tax.ID`"}
{else}
    {assign var="action" value="controller=backend.tax action=create"}
{/if}

{form handle=$taxForm action=$action id="taxForm_`$tax.ID`" method="post" onsubmit="Backend.Tax.prototype.getInstance(this).save(); return false;" role="taxes.update(edit),taxes.create(index)"}
	
    {hidden name="ID"}
    
    <label>{t _name}</label>
    <fieldset class="error">
        {textfield name="name"}
        <span class="errorText" style="display: none" />
	</fieldset>
    
    <p>
        <fieldset class="error">
            <label></label>
        	{checkbox name="isEnabled" class="checkbox"}
            <label for="isEnabled" class="checkbox">{t _enabled}</label>
            <span class="errorText" style="display: none" />
    	</fieldset>
	</p>
    
    {if $alternativeLanguagesCodes}
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
    {/if}
    
    <fieldset class="tax_controls controls">
        <span class="progressIndicator" style="display: none;"></span>
        <input type="submit" class="tax_save button submit" value="{t _save}" />
        {t _or}
        <a href="#cancel" class="tax_cancel cancel">{t _cancel}</a>
    </fieldset>
    
{/form}