{form handle=$productForm action="controller=backend.product action=save"}
	<fieldset>
		<legend>Main product information</legend>

		<p>
			<label>Product name</label>
			{textfield name="name"}
		</p>
		<p>
			<label>Short description:</label>
			{textarea name="shortDescription"}
		</p>
	</fieldset>

	<fieldset>
		<legend>Product Specification</legend>
	</fieldset>

	<fieldset>
		<legend>Translate product info</legend>
	</fieldset>

	{foreach from=$languageList key=lang item=langName}
	<fieldset class="expandingSection">
		<legend>Translate to: {$langName}</legend>
		<div class="expandingSectionContent">
			<p>
				<label>Product name:</label>
				{textfield name="name_$lang"}
			</p>
			<p>
				<label>Short description:</label>
				{textarea name="shortDescription_$lang"}
			</p>
		</div>
	</fieldset>
	{/foreach}
{/form}