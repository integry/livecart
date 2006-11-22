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

				<input type="radio" name="show" value="all" id="show-all" {$selected_all} onclick="langEdit.displayFilter(0)" />
				<label for="show-all">{t _all}</label>
			
				<input type="radio" name="show" value="notDefined" id="show-undefined" {$selected_not_defined} onclick="langEdit.displayFilter(1)" />
				<label for="show-undefined">{t _not_defined}</label>
				
				<input type="radio" name="show" value="defined" id="show-defined" {$selected_defined} onclick="langEdit.displayFilter(2)" />
				<label for="show-defined">{t _defined}</label>
			</td>
		</tr>
		<tr>
			<td>
				{t _search_trans}:
			</td>
			<td>
				<input type="text" id="filter" onkeyup="langEdit.langSearch(this.value, langEdit.getDisplayFilter(), true);">			
				<div id="langNotFound">{t _no_translations_found}</div>
			</td>
		</tr>
	</table>
	</form>	
</fieldset>
<br /><br />

<form id="editLang" method="post" action="{link controller=backend.language action=save id=$id}" onSubmit="langPassDisplaySettings(this);">
	
	<input type="hidden" name="langFileSel" />
	<input type="hidden" name="show" />
	
	<div id="expandCollapse">
		<a href="#" onClick="langEdit.langExpandAll('translations', true); return false;">Expand all</a> 
		<a href="#" onClick="langEdit.langExpandAll('translations', false); return false;">Collapse all</a>
	</div>
	
	<fieldset class="langTranslations lang-template" style="display: none;">
		<legend>
			<img src="image/backend/Language/hor_line.gif" class="langTreeLine" />
			<img src="image/backend/icon/expand.gif" class="langTreeControl" />
			<img src="image/backend/Language/spacer.gif" class="langTreeSpacer" />
			<a href="#" onClick="return false;"></a>
		</legend>
		<div style="display: none; border-left: 1px solid black;">
			<table style="display: none;">	
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
		</div>
	</fieldset>

	<div id="translations" style="display: block;"></div>

{literal}
<script type="text/javascript">
	var langEdit = new LiveCart.LanguageEdit(translations, english, document.getElementById('translations'));
	langEdit.preFilter();
</script>
{/literal}

	<input type="submit" value="{t _save}">
	
</form>