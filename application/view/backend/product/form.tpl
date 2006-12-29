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

		<hr/>
		Product specification...
		{foreach from=$specFieldList item=field}
		<p>
			<label>{$field.name.en}</label>
			{if $field.type == 1}
				{selectfield}

			{elseif $field.type == 2}
				{textfield name=$field.ID}

			{elseif $field.type == 3}
				{textfield}

			{elseif $field.type == 4}
				{textarea}

			{elseif $field.type == 5}
				{selectfield}

			{elseif $field.type == 6}
				{html_select_date}

			{/if}
		</p>
		{/foreach}
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