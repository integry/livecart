{pageTitle}{translate text=_language_definitons} (<img src="image/localeflag/{$id}.png" /> {$edit_language}){/pageTitle}
{includeJs file=library/json.js}
{loadScriptaculous}

{literal} 	
<script type="text/javascript"> 
	var translations = {/literal}{$translations}{literal}
	var english = {/literal}{$english}{literal}
</script>
{/literal}

<fieldset class="inlineHelp">
	<legend>{t _editing_translations}</legend>
	{translate|nl2br text="_help"}
</fieldset>

<fieldset class="menuFieldSet">
	<legend>{t _translation_filter}</legend>
	<form id="navLang" method="post" style="margin-bottom: 10px;" action="{link controller=backend.language action=edit id=$id}">
	<table>
		<tr>
			<td>
				{t _show_words}: 
			</td>
			<td>
				<input type="hidden" name="langFileSel" value='{$langFileSel|escape:"quotes"}' />

				<input type="radio" name="show" value="all" id="show-all" {$selected_all} onclick="this.form.submit()" />
					<label for="show-all">{t _all}</label>
			
				<input type="radio" name="show" value="notDefined" id="show-undefined" {$selected_not_defined} onclick="this.form.submit()" />
					<label for="show-undefined">{t _not_defined}</label>
				
				<input type="radio" name="show" value="defined" id="show-defined" {$selected_defined} onclick="this.form.submit()" />
					<label for="show-defined">{t _defined}</label>
			</td>
		</tr>
		<tr>
			<td>
				{t _search_trans}:
			</td>
			<td>
				<input type="text" name="filter" onkeyup="langSearch(this.value);">			
			</td>
		</tr>
	</table>
	</form>	
</fieldset>
<br /><br />

<form name="editLang" method="post" action="{link controller=backend.language action=save id=$id}" onSubmit="langPassDisplaySettings(this);">
	
	<input type="hidden" name="langFileSel" />
	<input type="hidden" name="show" />
	
	<table class="langTranslations lang-template" style="display: none;">
		<caption>
			<img src="image/backend/icon/collapse.gif">
			<a href="#" onClick="return false;"></a>
		</caption>
		<tbody style="display: none;">	
			<tr class="lang-trans-template" style="display: none;">
				<td class="lang-key"></td>
				<td class="lang-translation">
					<input type="text"><br />
					<span></span>
				</td>
			<tr>	
		</tbody>	
	</table>

	<div id="translations"> </div>

{literal}
<script type="text/javascript">
	langGenerateTranslationForm();
</script>
{/literal}

	<div id="langNotFound">{t _no_translations_found}</div>
	
	<input type="submit" value="{t _save}">
	
</form>