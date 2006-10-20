{literal}
<script language="javascript">	
	function eventFileChanged(file) 
	{	  		
{/literal}
	  	document.location.href = '{link language=$language controller=backend.language action=edit id=$id}?show={$show}&file=' + file;
{literal}
	}		
</script>
{/literal}
<h1>{translate text=_language_definitons} ({$edit_language})</h1>
<form name="form" method="post" action="{link language=$language controller=backend.language action=save id=$id}">
	<select name="file" style="width: 200px" onChange="eventFileChanged(this.value);">
	   {html_options options=$files selected=$file}
	</select>
	<br><br>
	<b>{translate text=_show_words}</b><br>
	<input type="radio" name="show" value="all"  {$selected_all} onclick="eventFileChanged()">
		{translate text=_all}
	<input type="radio" name="show" value="not_defined" {$selected_not_defined} onclick="eventFileChanged()">
		{translate text=_not_defined} 
	<input type="radio" name="show" value="defined" {$selected_defined} onclick="eventFileChanged()">
		{translate text=_defined}
	<br><br>
	<table>
		{foreach from=$definitions key=file item=values}
		
			<tr>
				<td colspan="2">
					<strong>{$file}</strong>
				</td>
			</tr>
		
			{foreach from=$values key=key item=item}
			
				<tr>
					<td width=150>
						{$key}	
					</td>
					<td width="10">
					</td>
					<td>
						<input type="text" style="width:450px" name="lang[{$key}]" value="{$item|escape}">
						<br/>
						<small><span style="color:#CCCCCC">{$en_definitions.$key}</span></small>
					</td>
				<tr>

			{/foreach}

		{/foreach}
	</table>
	<input type="submit" value="{translate text=_save}">
</form>

	