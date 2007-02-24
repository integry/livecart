<h1>EDIT</h1>

{form action="controller=backend.settings action=save" handle=$form}

{foreach from=$layout key=groupName item=fields}

	<fieldset class="settings">
	
		{if $groupName}
			<legend>{$groupName}</legend>
		{/if}
	
	{foreach from=$fields key=fieldName item=foo}	
		<p{if 'bool' == $values.$fieldName.type} class="checkbox"{/if}>
			
			{if 'bool' != $values.$fieldName.type}
				<label for="{$fieldName}">{$values.$fieldName.title}</label>
			{/if}
				
			{if 'string' == $values.$fieldName.type}
				{textfield class="text" name="$fieldName" id="$fieldName" value="`$values.$fieldName.value`"}
			{elseif 'num' == $values.$fieldName.type}
				{textfield class="text numeric" name="$fieldName" id="$fieldName" value="`$values.$fieldName.value`"}			
			{elseif 'bool' == $values.$fieldName.type}
				{checkbox class="checkbox" name="$fieldName" id="$fieldName" value="`$values.$fieldName.value`"}			
				<label class="checkbox" for="{$fieldName}">{$values.$fieldName.title}</label>
			{elseif 'array' == $values.$fieldName.type}						
			
			{/if}
		</p>	
	{/foreach}

	</fieldset>

{/foreach}

<input type="submit" value="{tn _save}" class="submit" /> {t _or} <a class="cancel" href="#" onclick="this.form.reset();return false;">{t _cancel}</a>

{/form}