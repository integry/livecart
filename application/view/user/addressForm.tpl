<input type="hidden" name="return" value="{$return}" />

<p class="required">
	{{err for="`$prefix`firstName"}}
	   {{label {t _your_first_name}:}}
	   {textfield class="text" id="firstName"}
	{/err}
</p>

<p class="required">
	{{err for="`$prefix`lastName"}}
		{{label {t _your_last_name}:}}
		{textfield class="text"}
	{/err}
</p>

<p>
	{{err for="`$prefix`companyName"}}
		{{label {t _company_name}:}}
		{textfield class="text"}
	{/err}
</p>

<p{if $form|isRequired:"phone"} class="required"{/if}>
	{{err for="`$prefix`phone"}}
		{{label {t _your_phone}:}}
		{textfield class="text"}
	{/err}
</p>

<p class="required">
	{{err for="`$prefix`address1"}}
		{{label {t _address}:}}
		{textfield class="text"}
	{/err}
</p>

<p>
	<label>&nbsp;</label>
	<fieldset class="error">
		{textfield name="`$prefix`address2" class="text"}
	</fieldset>
</p>

<p class="required">
	{{err for="`$prefix`city"}}
		{{label {t _city}:}}
		{textfield class="text"}
	{/err}
</p>

<p class="required">
	{{err for="`$prefix`country"}}
		{{label {t _country}:}}
		{selectfield options=$countries id="{uniqid assign=id_country}"}
		<span class="progressIndicator" style="display: none;"></span>
	{/err}
</p>

<p class="required">
	{{err for="`$prefix`state_select"}}
		{{label {t _state}:}}
		{selectfield style="display: none;" options=$states id="{uniqid assign=id_state_select}"}
		{textfield name="`$prefix`state_text" class="text" id="{uniqid assign=id_state_text}"}
	{/err}

	{literal}
	<script type="text/javascript">
	{/literal}
		new User.StateSwitcher($('{$id_country}'), $('{$id_state_select}'), $('{$id_state_text}'),
				'{link controller=user action=states}');
	</script>
</p>

<p class="required">
	{{err for="`$prefix`zip"}}
		{{label {t _postal_code}:}}
		{textfield class="text"}
	{/err}
</p>