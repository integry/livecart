{if !'DISABLE_STATE'|config}
	<p {if !$notRequired}class="required"{/if}>
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
{/if}
