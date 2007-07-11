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

<div style="display: none;">
	<div id="fileTemplate">
	
	    <h1>_name_</h1>
	
	    <div>
	        _edit_        
	    </div>
	
	</div>
	
	<div id="transTemplate" class="lang-trans-template">
		<div style="margin-bottom: 10px;">
			<label class="lang-key">_key_</label>
			<fieldset class="container lang-translation">
				<input id="_file_#_key_" type="text" value="" {denied role="language.status"}readonly="readonly"{/denied}><br />
				<span>_english_</span>
			</fieldset>
		</div>
	</div>	
</div>

{tip}
	{capture assign=tipUrl}{link controller=backend.customize action=index}{/capture}
	{maketext text="_tip_live_trans" params="$tipUrl"}
{/tip}

<div id="pageContainer">
		
	<div class="treeContainer">
		<div id="langBrowser" class="treeBrowser"></div>
        <div id="confirmations"></div>
	</div>

	<div class="treeManagerContainer">
		
		<span id="langIndicator" class="progressIndicator" style="display: none;"></span>
				
		<div id="langContent">

            <fieldset>
            	<legend>{t _translation_filter}</legend>
            	<form id="navLang" method="post" style="margin-bottom: 10px;" action="{link controller=backend.language action=edit id=$id}" class="">
            
            			<label>{t _show_words}:</label>
            		
            			<input type="hidden" name="langFileSel" value='{$langFileSel|escape:"quotes"}' />
            
            			<input type="radio" class="radio" name="show" value="all" id="show-all" />
            			<label class="radio" for="show-all">{t _all}</label>
            						
            			<input type="radio" class="radio" name="show" value="notDefined" id="show-undefined" />
            			<label class="radio" for="show-undefined">{t _not_defined}</label>
            			
            			<input type="radio" class="radio" name="show" value="defined" id="show-defined" />
            			<label class="radio" for="show-defined">{t _defined}</label>
            
            			<br />			
            			<br />
            
            			<label>{t _search_trans}:</label>
            
            			<fieldset class="container">
                            <input type="text" id="filter" />			
                            
                            <input type="checkbox" class="checkbox" id="allFiles" />
                            <label for="allFiles">{t _all_files}</label>
                            
                        </fieldset>
            			
            			<div id="langNotFound" style="display: none;">{t _no_translations_found}</div>
            			<div id="foundMany" style="display: none;">{t _found_many}</div>
                                    
            	</form>	
            </fieldset>
            
            <br /><br />
            
            <div id="translations" style="display: block; margin-bottom: 15px;"></div>
            
            
            <form id="editLang" method="post" action="{link controller=backend.language action=save id=$id}" onSubmit="langPassDisplaySettings(this); $('saveProgress').style.display = 'inline';">
            
                <fieldset class="controls" {denied role='language.update'}style="display: none"{/denied}>
                	<input type="hidden" name="translations" />
            		<span class="progressIndicator" id="saveProgress" style="display: none;"></span>
                    <input type="submit" class="submit" value="{t _save}"> 
                    {t _or} 
                    <a href="#" onClick="window.location.reload(); return false;" class="cancel">{t _cancel}</a>
                </fieldset>
            	
            </form>

		</div>
	</div>

</div>

<div class="clear"></div>

{literal}
<script type="text/javascript">
{/literal}
	var edit = new Backend.LangEdit(translations, english);
</script>

{include file="layout/backend/footer.tpl"}