{include file="backend/eav/includes.tpl"}

{sect}
	{header}
		<div class="eavContainer">
	{/header}
	{content}
		{foreach from=$specFieldList key=groupID item=fieldList}

			{sect}
				{header}
					{if $groupID}
						<fieldset class="eavGroup">
							<legend>{$fieldList.0.$groupClass.name_lang}</legend>
					{/if}
				{/header}
				{content}
					{foreach from=$fieldList item=field}
						{if !$filter || ($filter && ($field[$filter] || ($field.handle == $filter)))}
							{capture assign=class}eavField field_[[field.fieldName]] eavHandle_[[field.handle]] {if $field.isRequired}required{/if} {if !$field.isDisplayed}notDisplayed{/if}{/capture}
							{input name=$field.fieldName class=$class}
								{label}[[field.name_lang]]:{/label}
								{include file="backend/eav/specFieldFactory.tpl" field=$field autocompleteController="backend.eavFieldValue"}
								{if $field.description}
									<div class="fieldDescription">[[field.description_lang]]</div>
								{/if}
							{/input}
						{/if}
					{/foreach}
				{/content}
				{footer}
					{if $groupID}
						</fieldset>
					{/if}
				{/footer}
			{/sect}
		{/foreach}
	{/content}

	{footer}
		</div>
	{/footer}
{/sect}