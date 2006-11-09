{pageTitle}{translate text=_language_definitons} ({$edit_language}){/pageTitle}

{literal}
<script language="javascript">	
	function eventFileChanged(file, show) 
	{	  		
{/literal}
		document.location.href = '{link language=$language controller=backend.language action=edit id=$id}?show=' +show + '&file=' + file;
{literal}
	}		
</script>

<style>
input label {
  background-color: green;
}

.langTranslations {
	width: 650px;  	
	margin-bottom: 20px;
}

.langTranslations caption {
	text-align: left;
	font-weight: bold;  
}  

.langTranslations tbody {
	border: 1px solid #777777;	
}  

.langTranslations td {
	padding: 3px;
}  

.langTranslations input {
	width: 100%;
}  

.lang-key {
  	width: 200px;
}

.lang-translation {
  	width: 450px;
}

</style>

<script>
function langToggleVisibility(tableInstance)
{
	tbody = tableInstance.getElementsByTagName('tbody')[0];

	if ('none' == tbody.style.display)
	{
		tbody.style.display = '';
	}
	else 
	{
		tbody.style.display = 'none';	  
	}
	  
}
</script>
{/literal}

<form name="editLang" method="post" action="{link language=$language controller=backend.language action=save id=$id}">
	<select name="file" style="width: 200px" onChange="eventFileChanged(this.value);">
	   {html_options options=$files selected=$file}
	</select>
	<br /><br />
	<strong>{t _show_words}</b><br />
	
	<input type="radio" name="show" value="all" id="show-all" {$selected_all} onclick="eventFileChanged('{$file}', this.value)">
		<label for="show-all">{t _all}</label>
	</input>

	<input type="radio" name="show" value="notDefined" id="show-undefined" {$selected_not_defined} onclick="eventFileChanged('{$file}', this.value)">
		<label for="show-undefined">{t _not_defined}</label>
	</input>
	
	<input type="radio" name="show" value="defined" id="show-defined" {$selected_defined} onclick="eventFileChanged('{$file}', this.value)">
		<label for="show-defined">{t _defined}</label>
	</input>
	
	<br><br>
	{foreach from=$definitions key=file item=values}
		<table class="langTranslations">	
			<caption onClick="langToggleVisibility(this.parentNode);">{$file}</caption>
			<tbody style="display: none;">	
				{foreach from=$values key=key item=item}
					<tr>
						<td class="lang-key">
							{$key}	
						</td>

						<td class="lang-translation">
							<input type="text" name="lang[{$file}][{$key}]" value="{$item|escape:"quotes"}">
							<br/>
							<small><span style="color:#CCCCCC">{$en_definitions.$file.$key}</span></small>
						</td>
					<tr>	
				{/foreach}
			</tbody>
		</table>
	{/foreach}
	<input type="submit" value="{t _save}">
</form>