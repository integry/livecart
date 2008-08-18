{include file="backend/eav/includes.tpl"}
{assign var=containerId value=$blah|rand:1000000}

{sect}
	{header}
		<div id="eavContainer_{$containerId}" class="eavContainer">
	{/header}
	{content}
		{foreach from=$specFieldList key=groupID item=fieldList}

			{if $groupID}
				<fieldset>
					<legend>{$fieldList.0.$groupClass.name_lang}</legend>
			{/if}

			{foreach from=$fieldList item=field}
				{if !$filter || ($filter && $field[$filter])}
					<p class="{if $field.isRequired}required{/if} {if !$field.isDisplayed}notDisplayed{/if}">
						<label for="product_{$cat}_{$product.ID}_{$field.fieldName}"><span>{$field.name_lang}:</span></label>
						<fieldset class="error">
							{include file="backend/eav/specFieldFactory.tpl" field=$field cat=$cat autocompleteController="backend.eavFieldValue"}
							{if $field.description}
								<div class="fieldDescription">{$field.description}</div>
							{/if}
							<div class="errorText hidden{error for=$field.fieldName} visible{/error}">{error for=$field.fieldName}{$msg}{/error}</div>
						</fieldset>
					</p>
				{/if}
			{/foreach}

			{if $groupID}
				</fieldset>
			{/if}
		{/foreach}
	{/content}

	{footer}
		</div>

		{literal}
		<script type="text/javascript">
			new Backend.Eav($('eavContainer_{/literal}{$containerId}'));
		</script>
	{/footer}
{/sect}