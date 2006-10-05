{literal}
<script language="javascript">	
	function eventFileChanged() {
	  		
	  	document.form.event.value = "filter";	  	
		document.form.submit();
	}		
</script>
{/literal}
<h1>{translate text=_language_definitons} ({$edit_language})</h1>
<form name="form" method="post" action="{link language=$language controller=backend.language action=edit id=$id}">
	<select name="file" style="width: 200px" onChange="eventFileChanged();">
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
		{foreach from=$definitions key=key item=item}
		<tr>
			<td width=150>
				{$key}	
			</td>
			<td width="10">
			</td>
			<td>
				<input type="text" style="width:450px" name="lang_{$key}" value="{$item|escape}">
			</td>
			<td width="10">
			</td>
			<td>
				{$en_definitions.$key}
			</td>
		<tr>
		{/foreach}
	</table>
	<input type="hidden" name="event" value="save">
	<input type="submit" value="{translate text=_save}">
</form>

	