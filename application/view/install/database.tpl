<h1>MySQL Database Information</h1>

<div>

	{form action="controller=install action=setDatabase" method="POST" handle=$form class="form-horizontal"}

	{error for="connect"}
		<div class="fail" style="float: left;">
			{$msg}
		</div>
		<div class="clear"></div>
	{/error}

	{input name="server"}
		{label}{t _db_server}:{/label}
		{textfield}
		<div style="margin-top: -5px;"><small>Usually <em>localhost</em></small></div>
	{/input}

	{input name="name"}
		{label}{t _db_name}:{/label}
		{textfield}
	{/input}

	{input name="username"}
		{label}{t _db_username}:{/label}
		{textfield}
	{/input}

	{input name="password"}
		{label}{t _db_pass}:{/label}
		{textfield type="password" class="password"}
	{/input}

	<div class="clear"></div>
	<input type="submit" value="Continue installation" />
	{/form}
</div>

{literal}
<script type="text/javascript">
	$('server').focus();
</script>
{/literal}