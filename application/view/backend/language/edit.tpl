{pageTitle help="language.edit"}
	{translate text=_language_definitons} (<img src="image/localeflag/{$id}.png" /> {$edit_language})
{/pageTitle}

{includeJs file=library/json.js}
{includeJs file=backend/Language.js}

{includeCss file=backend/Language.css}

{include file="layout/header.tpl"}

{literal} 	
<script type="text/javascript"> 
	var translations = {/literal}{$translations}{literal}
	var english = {/literal}{$english}{literal}
</script>
{/literal}

{if $saved}
<div class="saveConfirmation" id="langSaveConf" style="display: none;">
	<div>Translations were saved successfuly</div>
</div>
{/if}

<fieldset class="menuFieldSet">
	<legend>{t _translation_filter}</legend>
	<form id="navLang" method="post" style="margin-bottom: 10px;" action="{link controller=backend.language action=edit id=$id}" class="">

			<label>{t _show_words}:</label>
		
			<input type="hidden" name="langFileSel" value='{$langFileSel|escape:"quotes"}' />

			<input type="radio" class="radio" name="show" value="all" id="show-all" {$selected_all} onclick="langEdit.displayFilter(0)" />
			<label class="radio" for="show-all">{t _all}</label>
						
			<input type="radio" class="radio" name="show" value="notDefined" id="show-undefined" {$selected_not_defined} onclick="langEdit.displayFilter(1)" />
			<label class="radio" for="show-undefined">{t _not_defined}</label>
			
			<input type="radio" class="radio" name="show" value="defined" id="show-defined" {$selected_defined} onclick="langEdit.displayFilter(2)" />
			<label class="radio" for="show-defined">{t _defined}</label>

			<br />			
			<br />

			<label>{t _search_trans}:</label>

			<input type="text" id="filter" onkeyup="langEdit.langSearch(this.value, langEdit.getDisplayFilter(), true);">			
			<div id="langNotFound">{t _no_translations_found}</div>

	</form>	
</fieldset>

<br /><br />

<form id="editLang" method="post" action="{link controller=backend.language action=save id=$id}" onSubmit="langPassDisplaySettings(this); document.getElementById('saveProgress').style.display = 'inline';">
	
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
		<div class="branch" style="display: none;">
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

	<div id="translations" style="display: block; margin-bottom: 15px;"></div>

	{literal}
	<script type="text/javascript">
	{/literal}
		var langEdit = new Backend.LanguageEdit(translations, english, document.getElementById('translations'));
		langEdit.preFilter();
		{if $saved}
		new Backend.SaveConfirmationMessage('langSaveConf');
		{/if}
		breadcrumb.addItem('{$edit_language}', '');
	</script>

	<img id="saveProgress" src="image/indicator.gif" style="display: none;"> <input type="submit" class="submit" value="{t _save}"> {t _or} <a href="#" onClick="window.location.reload(); return false;" class="cancel">{t _cancel}</a>
	
</form>

{include file="layout/footer.tpl"}