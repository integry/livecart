[[ partial("backend/eav/includes.tpl") ]]

{sect}
	{header}
		<div class="eavContainer">
	{/header}
	{content}
		{foreach from=$specFieldList key=groupID item=fieldList}

			{sect}
				{header}
					{% if !empty(groupID) %}
						<fieldset class="eavGroup">
							<legend>{$fieldList.0.$groupClass.name()}</legend>
					{% endif %}
				{/header}
				{content}
					{foreach from=$fieldList item=field}
						{% if !$filter || ($filter && ($field[$filter] || ($field.handle == $filter))) %}
							{capture assign=class}eavField field_[[field.fieldName]] eavHandle_[[field.handle]] {% if $field.isRequired %}required{% endif %} {% if !$field.isDisplayed %}notDisplayed{% endif %}{/capture}
							{input name=$field.fieldName class=$class}
								{label}[[field.name()]]:{/label}
								[[ partial('backend/eav/specFieldFactory.tpl', ['field': field, 'autocompleteController': "backend.eavFieldValue"]) ]]
								{% if $field.description %}
									<div class="fieldDescription">[[field.description()]]</div>
								{% endif %}
							{/input}
						{% endif %}
					{/foreach}
				{/content}
				{footer}
					{% if !empty(groupID) %}
						</fieldset>
					{% endif %}
				{/footer}
			{/sect}
		{/foreach}
	{/content}

	{footer}
		</div>
	{/footer}
{/sect}