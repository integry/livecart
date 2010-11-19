{defun name="optionPrice" choice=null}
	{if $choice && $choice.priceDiff != 0}
		{if $choice.Option.isPriceIncluded && $choice.formattedTotalPrice.$currency}
			- <span class="optionFullPrice">{$choice.formattedTotalPrice.$currency}</span>
		{else}
			({if $choice && $choice.priceDiff > 0}+{/if}{$choice.formattedPrice.$currency})
		{/if}
	{/if}
{/defun}

<div{if $option.isRequired} class="required"{/if} class="productOption">
	{if $option.fieldName}{assign var=fieldName value=$option.fieldName}{else}{assign var=fieldName value="option_`$option.ID`"}{/if}
	{assign var=fieldName value="`$optionPrefix``$fieldName`"}
	{if 0 == $option.type}
		{{err for="`$fieldName`"}}
			{checkbox class="checkbox"}
			<label for={$fieldName} class="checkbox">
				{$option.name_lang}
				{fun name="optionPrice" choice=$option.DefaultChoice}
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
				{if 0 == $option.displayType}
					<fieldset class="error">
					<select name="{$fieldName}">
						<option value="">{$option.selectMessage_lang}</option>
						{foreach from=$option.choices item=choice}
							<option value="{$choice.ID}"{if $selectedChoice.Choice.ID == $choice.ID} selected="selected"{/if}>
								{$choice.name_lang}
								{fun name="optionPrice" choice=$choice}
							</option>
						{/foreach}
					</select>
				{else}
					<div class="radioOptions">
						{if $option.selectMessage_lang}
							<p>
								<input name="{$fieldName}" type="radio" class="radio" id="{uniqid}" value=""{if !$selectedChoice.Choice.ID} checked="checked"{/if} />
								<label class="radio" for="{uniqid last=true}">{$option.selectMessage_lang}</label>
							</p>
						{/if}

						{foreach from=$option.choices item=choice}
							<p>
								<input name="{$fieldName}" type="radio" class="radio" id="{uniqid}" value="{$choice.ID}"{if $selectedChoice.Choice.ID == $choice.ID} checked="checked"{/if} />
								<label class="radio" for="{uniqid last=true}">
									{$choice.name_lang}
									{fun name="optionPrice" choice=$choice}
								</label>
							</p>
						{/foreach}
						<div class="clear"></div>
					</div>
				{/if}
			{elseif 2 == $option.type}
				{textfield class="text"}
			{elseif 3 == $option.type}
				{uniqid assign=uniq noecho=true}
				{filefield name="upload_`$fieldName`" id=$uniq}
				{hidden name=$fieldName}
				{error for="upload_`$fieldName`"}<div class="errorText">{$msg}</div>{/error}
				<div class="optionFileInfo" style="display: none;">
					<div class="optionFileName"></div>
					<div class="optionFileImage">
						<img src="" class="optionThumb" alt="" />
					</div>
				</div>
				<script type="text/javascript">
					var upload = $('{$uniq}');
					new LiveCart.FileUpload(upload, '{link controller=order action=uploadOptionFile id=$option.ID query="uniq=`$uniq`&field=`$fieldName`&productID=`$product.ID`"}', Order.previewOptionImage);
				</script>
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