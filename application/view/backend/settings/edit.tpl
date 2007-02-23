<h1>EDIT</h1>

{foreach from=$layout key=groupName item=fields}

	<h2>{$groupName}</h2>
	
	{foreach from=$fields key=fieldName item=foo}	
		<p>
			<label for="{$fieldName}">{$values.$fieldName.title}<label>
			<input name="$fieldName" id="$fieldName" value="{$values.$fieldName.value}" />
		</p>	
	{/foreach}

{/foreach}