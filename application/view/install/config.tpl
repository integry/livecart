<h1>Store Configuration</h1>

<p>
	This step allows you to configure the most important aspects of your store. <Br />More configuration options will be available after the installation is completed.
</p>

{form action="install/setConfig" method="POST" handle=form class="form-horizontal"}
	[[ textfld('name', '_store_name') ]]

	[[ selectfld('language', '_base()uage', languages) ]]

	[[ selectfld('curr', '_base_currency', currencies) ]]

	<input type="submit" class="submit" value="Complete installation" />
{/form}


<script type="text/javascript">
	('name').focus();
</script>
