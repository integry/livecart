<h1>MySQL Database Information</h1>

<div>

	{form action="install/setDatabase" method="POST" handle=$form class="form-horizontal"}

	{error for="connect"}
		<div class="fail" style="float: left;">
			[[msg]]
		</div>
		<div class="clear"></div>
	{/error}

	{input name="server"}
		{label}{t _db_server}:{/label}
		{textfield}
		<div style="margin-top: -5px;"><small>Usually <em>localhost</em></small></div>
	{/input}

	[[ textfld('name', '_db_name') ]]

	[[ textfld('username', '_db_username') ]]

	{input name="password"}
		{label}{t _db_pass}:{/label}
		{textfield type="password" class="password"}
	{/input}

	<div class="clear"></div>
	<input type="submit" value="Continue installation" />
	{/form}
</div>


<script type="text/javascript">
	$('server').focus();
</script>
