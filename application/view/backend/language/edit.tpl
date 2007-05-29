{pageTitle help="language.edit"}
	{translate text=_language_definitons} (<img src="image/localeflag/{$id}.png" /> {$edit_language})
{/pageTitle}

{includeJs file="library/json.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="backend/Language.js"}

{includeCss file="backend/Language.css"}
{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}

{include file="layout/backend/header.tpl"}

{literal} 	
<script type="text/javascript"> 
	var translations = {/literal}{$translations}{literal}
	var english = {/literal}{$english}{literal}
</script>
{/literal}

<div id="fileTemplate">

    <h1>_name_</h1>

    <div>
        _edit_        
    </div>

</div>

<div id="transTemplate" class="lang-trans-template">
	<label class="lang-key">_key_</label>
	<fieldset class="container lang-translation">
		<input id="_file_#_key_" type="text" {denied role="language.status"}readonly="readonly"{/denied}><br />
		<span>_english_</span>
	</fieldset>
</div>	

<div id="pageContainer">
		
	<div style="float: left;">
		<div id="langBrowser" class="treeBrowser">
		</div>
	
	    <div class="clear"></div>
	
		<div style="margin-top: 20px;">
			<div class="yellowMessage" style="display: none;">
				<div>
					{t Template saved successfuly}
				</div>
			</div>
			<div class="redMessage" style="display: none;">
				<div>
					{t Template could not be saved}
				</div>
			</div>
		</div>		
		
		<div style="clear: both;"></div>
	
	</div>

	<div style="float: left; margin-left: 20px;">
		
		<span id="langIndicator" class="progressIndicator" style="display: none;"></span>
				
		<div id="langContent">
			{include file="backend/template/emptyPage.tpl"}
		</div>
	</div>

</div>

{literal}
<script type="text/javascript">
{/literal}
	var edit = new Backend.LangEdit(translations, english);
</script>


{tip}
	{capture assign=tipUrl}{link controller=backend.customize action=index}{/capture}
	{maketext text="_tip_live_trans" params="$tipUrl"}
{/tip}

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
							<input type="text" {denied role="language.status"}readonly="readonly"{/denied}><br />
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
/*
		var langEdit = new Backend.LanguageEdit(translations, english, document.getElementById('translations'));
		langEdit.preFilter();
		{if $saved}{literal}
		new Backend.SaveConfirmationMessage($('langSaveConf'), { message: 'Translations were saved successfuly', type: 'yellow' });
		{/literal}{/if}
		breadcrumb.addItem('{$edit_language}', '');
*/
	</script>

    <span {denied role='language.update'}style="display: none"{/denied}>
    	<img id="saveProgress" src="image/indicator.gif" style="display: none;"> 
        <input type="submit" class="submit" value="{t _save}"> 
        {t _or} 
        <a href="#" onClick="window.location.reload(); return false;" class="cancel">{t _cancel}</a>
    </span>
	
</form>

{include file="layout/backend/footer.tpl"}