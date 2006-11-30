{includeCss file="form.css"}

{assign var="action" value="create"}

{form handle=$catalogForm action="controller=backend.catalog action=$action id=1" method="post"}
	<fieldset>
		<legend>Category details {$ID}</legend>

		<label for="name">Category name:</label>
		{textfield name="name" id="name"}
		<br/>

		<label for="details">Details:</label>
		{textarea name="details" id="details"}
		<br/>

		<label for="keywords">Keywords:</label>
		{textarea name="keywords" id="keywords"}
		<br/>

		<label for="isActive"> </label>
		{checkbox name="isActive"} Category is activated
		<br/>

	</fieldset>

	{foreach from=$languageList item=lang}
	<fieldset>
		<legend>Translate to: {$lang}</legend>
			<label>Category name:</label>
			{textfield name="name_$lang"}
			<br/>

			<label>Details:</label>
			{textarea name="details_$lang"}
			<br/>

			<label>Keywords:</label>
			{textarea name="keywords_$lang"}
			<br/>
	</fieldset>
	{/foreach}
	<script type="text/javascript">
		var expander = new SectionExpander();
	</script>

	<fieldset>
		<label for="submit"> </label>
		<input type="submit" class="submit" id="submit" value="save"/>
	</fieldset>
{/form}