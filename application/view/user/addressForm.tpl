<input type="hidden" name="return" value="{$return}" />    

<p class="required">
	{err for="firstName"}
	   {label}{t _your_first_name}:{/label}  
       {textfield class="text"}
	{/err}
</p>

<p class="required">
	{err for="lastName"}
        {label}{t _your_last_name}:{/label}
		{textfield class="text"}
	{/err}
</p>

<p>
	{err for="companyName"}
        {label}{t _company_name}:{/label}
		{textfield class="text"}
	{/err}
</p>

<p{if $form|isRequired:"phone"} class="required"{/if}>
    {err for="phone"}
        {label}{t _your_phone}:{/label}
		{textfield class="text"}
	{/err}
</p>

<p class="required">
    {err for="address1"}
        {label}{t _address}:{/label}
        {textfield class="text"}
	{/err}
</p>

<p>
    <label></label>
    {textfield name="address_2" class="text"}
</p>

<p class="required">
    {err for="city"}
        {label}{t _city}{/label}
        {textfield class="text"}
	{/err}
</p>

<p class="required">
    {err for="country"}
        {label}{t _country}{/label}
        {selectfield options=$countries}
        <span class="progressIndicator" style="display: none;"></span>
	{/err}
</p>

<p class="required">
    {err for="state_select"}
        {label}{t _state}{/label}
        {selectfield style="display: none;" options=$states}
        {textfield name="state_text" class="text"}
	{/err}

    {literal}
    <script type="text/javascript">
    {/literal}
        new User.StateSwitcher($('country'), $('state_select'), $('state_text'),
                '{link controller=user action=states}');       
    </script>
</p>

<p class="required">
    {err for="zip"}
        {label}{t _postal_code}{/label}
        {textfield class="text"}
	{/err}
</p>