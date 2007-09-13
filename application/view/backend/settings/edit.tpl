<h1>{$title}</h1>

{form action="controller=backend.settings action=save" method="post" handle=$form onsubmit="settings.save(this); return false;" role="settings.update"}

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
		<div class="setting" {if 'bool' != $values.$fieldName.type}style="margin-top: 7px; margin-bottom: 7px;"{/if}>
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
				{if 'multi' == $values.$fieldName.extra}
                    <div class="multi">
                    {foreach from=$values.$fieldName.type item="value" key="key"}
				        <p>
                        {checkbox name="`$fieldName`[`$key`]" class="checkbox" value=1}
				        <label for="{$fieldName}[{$key}]" class="checkbox">{$value}</label>
				        </p>
				    {/foreach}
				        <div class="clear"></div>
                    </div>
				{else}
                    {selectfield options=$values.$fieldName.type name="$fieldName" id="$fieldName"}
                {/if}
			{/if}
			<div class="errorText hidden"></div>
		</fieldset>
		</p>
        </div>	
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

{language}
    {foreach from=$multiLingualValues key="fieldName" item="foo"}
    <p>
		<label for="{$fieldName}_{$lang.ID}" class="setting">{t `$values.$fieldName.title`}:</label>

		<fieldset class="error">
			{textfield class="text wide" name="`$fieldName`_`$lang.ID`" id="`$fieldName`_`$lang.ID`"}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	{/foreach}
{/language}

<input type="hidden" name="id" value="{$id}" />

<fieldset class="controls">
    <span class="progressIndicator" style="display: none;"></span>
    <input type="submit" value="{tn _save}" class="submit" /> 
    {t _or} 
    <a class="cancel" href="#" onclick="return false;">{t _cancel}</a>
</fieldset>
{/form}