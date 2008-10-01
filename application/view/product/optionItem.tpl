<div{if $option.isRequired} class="required"{/if} class="productOption">
	{if $option.fieldName}{assign var=fieldName value=$option.fieldName}{else}{assign var=fieldName value="option_`$option.ID`"}{/if}
	{if 0 == $option.type}
		{{err for="`$fieldName`"}}
			{checkbox class="checkbox"}
			<label for={$fieldName} class="checkbox">
				{$option.name_lang}
				{if $option.DefaultChoice.priceDiff != 0}
					(+{$option.DefaultChoice.formattedPrice.$currency})
				{/if}
			</label>

			{if $option.description_lang}
				<p class="description">
					{$option.description_lang}
				</p>
			{/if}

		{/err}
	{else}
		<label class="field">{$option.name_lang}</label>
			{{err for="`$fieldName`"}}
			{if 1 == $option.type}
				<fieldset class="error">
				<select name="{$fieldName}">
					<option></option>
					{foreach from=$option.choices item=choice}
						<option value="{$choice.ID}"{if $selectedChoice.Choice.ID == $choice.ID} selected="selected"{/if}>
							{$choice.name_lang}
							{if $choice.priceDiff != 0}
								(+{$choice.formattedPrice.$currency})</label>
							{/if}
						</option>
					{/foreach}
				</select>
			{else}
				{textfield class="text"}
			{/if}

			{if $option.description_lang}
				<p class="description">
					{$option.description_lang}
				</p>
			{/if}

		{/err}
	{/if}
</div>
<div class="clear"></div>