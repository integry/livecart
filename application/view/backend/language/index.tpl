{includeJs file="library/ActiveList.js"}
{includeJs file="library/KeyboardEvent.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="backend/Language.js"}
{includeCss file="library/ActiveList.css"}
{includeCss file="backend/Language.css"}
{pageTitle help="settings.languages"}{t _admin_languages}{/pageTitle}

{include file="layout/backend/header.tpl"}

<script type="text/javascript">
	var lng = new Backend.LanguageIndex();	
    {literal}
	lng.activeListMessages = { 
        _activeList_edit:    {/literal}'{t _activeList_edit|addslashes}'{literal},
        _activeList_delete:  {/literal}'{t _activeList_delete|addslashes}'{literal}
    }
    {/literal}
	lng.setFormUrl('{link controller=backend.language action=addForm}');
	lng.setStatusUrl("{link controller=backend.language action=setEnabled}/");
	lng.setEditUrl("{link controller=backend.language action=edit}");
	lng.setSortUrl("{link controller=backend.language action=saveorder}/");
	lng.setDeleteUrl("{link controller=backend.language action=delete}/");
	lng.setDelConfirmMsg('{t _confirm_delete}');
    
</script>

<div id="confirmations" class="rightConfirmations"></div>

<fieldset class="container" {denied role="language.create"}style="display: none;"{/denied}>
	<ul class="menu" id="langPageMenu">
		<li class="addNewLanguage">
			<a href="#" onClick="lng.showAddForm(); return false;">{t _add_language}</a>
			<span class="progressIndicator" id="langAddMenuLoadIndicator" style="display: none;"></span>
		</li>
	</ul>
</fieldset>

<div id="addLang" class="slideForm"></div>

<ul id="languageList" class="{allowed role="language.sort"}activeList_add_sort{/allowed} {allowed role="language.remove"}activeList_add_delete{/allowed} activeList_add_edit">
</ul>

<ul>
<li id="languageList_template" class="{allowed role="language.sort"}activeList_add_sort{/allowed} {allowed role="language.remove"}activeList_remove_delete{/allowed} disabled default">
	<div>
		<div class="langListContainer" >

			<span class="langCheckBox" {denied role="language.status"}style="display: none;"{/denied}>
				<input type="checkbox" class="checkbox" disabled="disabled" onclick="lng.setEnabled(this);" />
			</span>	
            
		    <span class="progressIndicator" style="display: none;"></span>
		
			<span class="langData">
				{img src=""}
				<span class="langTitle"></span> 
				<span class="langInactive">({t _inactive})</span>
			</span>
			
			<div class="langListMenu">
				<a href="{link controller=backend.language action=setDefault}/" class="listLink setDefault" {denied role="language.status"}style="display: none;"{/denied}>
					{t _set_as_default}
				</a>
				<span class="langDefault">{t _default_language}</span>
			</div>
			
		</div>
	</div>			
</li>
</ul>

{literal}
<script type="text/javascript">
	lng.renderList({/literal}{$languageArray}{literal});
	lng.initLangList();
</script>
{/literal}

<!-- {maketext text="_statistic_languages_full" params="$count_all,$count_active"}. -->

{include file="layout/backend/footer.tpl"}