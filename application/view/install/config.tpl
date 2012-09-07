<h1>Store Configuration</h1>

<p>
	This step allows you to configure the most important aspects of your store. <Br />More configuration options will be available after the installation is completed.
</p>

{form action="controller=install action=setConfig" method="POST" handle=$form}
	{input name="name"}
		{label}{t _store_name}:{/label}
		{textfield}
	{/input}

	{input name="language"}
		{label}{t _base_language}:{/label}
		{selectfield options=$languages}
	{/input}

	{input name="curr"}
		{label}{t _base_currency}:{/label}
		{selectfield options=$currencies}
	{/input}

	<input type="submit" class="submit" value="Complete installation" />
{/form}

{literal}
<script type="text/javascript">
	$('name').focus();
</script>
{/literal}