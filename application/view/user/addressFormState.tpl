{if !'DISABLE_STATE'|config}
	{input name="`$prefix`state_select"}
		{label}{t _state}:{/label}
		{selectfield style="display: none;" options=$states id="{uniqid assign=id_state_select}"}
		{textfield name="`$prefix`state_text" class="text" id="{uniqid assign=id_state_text}"}
	{/input}

	{literal}
	<script type="text/javascript">
	{/literal}
		new User.StateSwitcher($('[[id_country]]'), $('[[id_state_select]]'), $('[[id_state_text]]'),
				'{link controller=user action=states}');
	</script>
{/if}
