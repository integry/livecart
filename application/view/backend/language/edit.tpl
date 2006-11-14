{pageTitle}{translate text=_language_definitons} (<img src="image/localeflag/{$id}.png" /> {$edit_language}){/pageTitle}
{includeJs file=library/json.js}
{loadScriptaculous}

{literal} 	
<script type="text/javascript"> 
	var translations = {/literal}{$translations}{literal}
	var english = {/literal}{$english}{literal}
</script>
{/literal}

<form id="navLang" method="post" style="margin-bottom: 10px;" action="{link controller=backend.language action=edit id=$id}">
	<input type="hidden" name="langFileSel" value="{$langFileSel}" />

	<strong>{t _show_words}:</strong> 
	
	<input type="radio" name="show" value="all" id="show-all" {$selected_all} onclick="this.form.submit()">
		<label for="show-all">{t _all}</label>
	</input>

	<input type="radio" name="show" value="notDefined" id="show-undefined" {$selected_not_defined} onclick="this.form.submit()">
		<label for="show-undefined">{t _not_defined}</label>
	</input>
	
	<input type="radio" name="show" value="defined" id="show-defined" {$selected_defined} onclick="this.form.submit()">
		<label for="show-defined">{t _defined}</label>
	</input>
</form>

<div id="langSearch">
	Search for translations: <input type="text" name="filter" onkeyup="langGenerateTranslationForm(this.value);">
</div>

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
					<input type="text">
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

	<input type="submit" value="{t _save}">
	
</form>