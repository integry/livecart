<h1>{$title}</h1>

{form action="controller=backend.settings action=save" handle=$form onsubmit="settings.save(this); return false;"}

{foreach from=$layout key=groupName item=fields name="groups"}

	{if !$fields && !$smarty.foreach.groups.first}
		{assign var="subsections" value=false}	
		</fieldset>
	{/if}

	<fieldset class="settings">
	
		{if $groupName}
			<legend>{t $groupName}</legend>
		{/if}
	
	{foreach from=$fields key="fieldName" item="foo"}	
		<p{if 'bool' == $values.$fieldName.type} class="checkbox"{/if}>
			
			{if 'bool' != $values.$fieldName.type}
				<label for="{$fieldName}" class="setting">{t `$values.$fieldName.title`}:</label>
			{/if}
				
		<fieldset class="error">
			{if 'string' == $values.$fieldName.type}
				{textfield class="text wide" name="$fieldName" id="$fieldName"}
			{elseif 'num' == $values.$fieldName.type}
				{textfield class="text number" name="$fieldName" id="$fieldName"}			
			{elseif 'bool' == $values.$fieldName.type}
				{checkbox class="checkbox" name="$fieldName" id="$fieldName" value="1"}			
				<label class="checkbox" for="{$fieldName}">{t `$values.$fieldName.title`}</label>
			{elseif is_array($values.$fieldName.type)}						
				{selectfield options=$values.$fieldName.type name="$fieldName" id="$fieldName"}
			{/if}
			<div class="errorText hidden"></div>
		</fieldset>
		</p>	
	{foreachelse}
		{assign var="subsections" value=true}	
	{/foreach}

	{if $fields || $smarty.foreach.groups.last}
		</fieldset>
	{/if}

{/foreach}

{if $subsections}
	</fieldset>
{/if}

{if $multiLingualValues}
    {foreach from=$languages item="language"}
		<fieldset class="expandingSection">
		<legend>{t Translate to}: {$language.originalName}</legend>
			<div class="expandingSectionContent">
			    {foreach from=$multiLingualValues key="fieldName" item="foo"}
                <p>
    				<label for="{$fieldName}_{$language.ID}" class="setting">{t `$values.$fieldName.title`}:</label>

            		<fieldset class="error">
           				{textfield class="text wide" name="`$fieldName`_`$language.ID`" id="`$fieldName`_`$language.ID`"}
            			<div class="errorText hidden"></div>
            		</fieldset>
				</p>
				{/foreach}
			</div>
		</fieldset>
    {/foreach}

	<script type="text/javascript">
		var expander = new SectionExpander();
		$('name_{$categoryId}').focus();
	</script>
{/if}

<span class="progressIndicator" style="display: none;"></span>

<input type="hidden" name="id" value="{$id}" />
<input type="submit" value="{tn _save}" class="submit" /> {t _or} <a class="cancel" href="#" onclick="return false;">{t _cancel}</a>

{/form}