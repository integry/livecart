<input type="hidden" name="return" value="{$return}" />    

<p class="required">
    <label for="firstName">{t _your_first_name}:</label>
    
	<fieldset class="error">
		{textfield name="firstName" class="text"}
		<div class="errorText hidden{error for="firstName"} visible{/error}">{error for="firstName"}{$msg}{/error}</div>
	</fieldset>
</p>

<p class="required">
    <label for="lastName">{t _your_last_name}:</label>
    
	<fieldset class="error">
		{textfield name="lastName" class="text"}
		<div class="errorText hidden{error for="lastName"} visible{/error}">{error for="lastName"}{$msg}{/error}</div>
	</fieldset>
</p>

<p>
    <label for="companyName">{t _company_name}:</label>
    
	<fieldset class="error">
		{textfield name="companyName" class="text"}
		<div class="errorText hidden{error for="companyName"} visible{/error}">{error for="companyName"}{$msg}{/error}</div>
	</fieldset>
</p>

<p{if $form|isRequired:"phone"} class="required"{/if}>
    <label for="phone">{t _your_phone}:</label>
	<fieldset class="error">
		{textfield name="phone" class="text"}
		<div class="errorText hidden{error for="phone"} visible{/error}">{error for="phone"}{$msg}{/error}</div>
	</fieldset>
</p>

<p class="required">
    <label for="address1">{t _address}:</label>
	<fieldset class="error">
        {textfield name="address1" class="text"}
		<div class="errorText hidden{error for="address1"} visible{/error}">{error for="address1"}{$msg}{/error}</div>
	</fieldset>
</p>

<p>
    <label for="address_2"></label>
    {textfield name="address_2" class="text"}
</p>

<p class="required">
    <label for="city">{t _city}</label>
	<fieldset class="error">
        {textfield name="city" class="text"}
		<div class="errorText hidden{error for="city"} visible{/error}">{error for="city"}{$msg}{/error}</div>
	</fieldset>
</p>

<p class="required">
    <label for="country">{t _country}</label>
	<fieldset class="error">
        {selectfield name="country" id="country" options=$countries}
        <span class="progressIndicator" style="display: none;"></span>
		<div class="errorText hidden{error for="country"} visible{/error}">{error for="country"}{$msg}{/error}</div>
	</fieldset>
</p>

<p class="required">
    <label for="state_select">{t _state}</label>
	<fieldset class="error">
        {selectfield name="state_select" id="state_select" style="display: none;" options=$states}
        {textfield name="state_text" class="text"}
		<div class="errorText hidden{error for="state_select"} visible{/error}">{error for="state_select"}{$msg}{/error}</div>
	</fieldset>

    {literal}
    <script type="text/javascript">
    {/literal}
        new User.StateSwitcher($('country'), $('state_select'), $('state_text'),
                '{link controller=user action=states}');       
    </script>
</p>

<p class="required">
    <label for="zip">{t _postal_code}</label>
	<fieldset class="error">
        {textfield name="zip" class="text"}
		<div class="errorText hidden{error for="zip"} visible{/error}">{error for="zip"}{$msg}{/error}</div>
	</fieldset>
</p>