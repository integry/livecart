{includeJs file=library/ActiveList.js}
{includeJs file=library/KeyboardEvent.js}
{includeJs file=backend/Language.js}
{includeCss file=library/ActiveList.css}
{includeCss file=backend/Language.css}
{pageTitle help="language"}{t _admin_languages}{/pageTitle}

{include file="layout/header.tpl"}

<script type="text/javascript">
	var lng = new Backend.LanguageIndex();	
	lng.setFormUrl('{link controller=backend.language action=addForm}');
	lng.setAddUrl('{link controller=backend.language action=add}');
	lng.setStatusUrl("{link controller=backend.language action=setEnabled}/");
	lng.setEditUrl("{link controller=backend.language action=edit}/");
	lng.setSortUrl("{link controller=backend.language action=saveorder}/");
	lng.setDeleteUrl("{link controller=backend.language action=delete}/");
	lng.setDelConfirmMsg('{t _confirm_delete}');
</script>

{tip}{t _index_tip}{/tip}

<ul class="menu" id="langPageMenu">
	<li><a href="#" onClick="lng.showAddForm(); return false;">{t _add_language}</a></li>
</ul>

<div class="menuLoadIndicator" id="langAddMenuLoadIndicator"></div>
<div id="addLang" class="slideForm"></div>

<br />

<li id="languageList_template" class="activeList_add_sort activeList_remove_delete disabled default">
	<div>
		<div class="langListContainer">

			<span class="langCheckBox">
				<input type="checkbox" class="checkbox" disabled="disabled" onclick="lng.setEnabled(this);" />
			</span>	
		
			<span class="langData">
				<img src="" />
				<span class="langTitle"></span> 
				<span class="langInactive">({t _inactive})</span>
			</span>
			
			<div class="langListMenu">
				<a href="{link controller=backend.language action=setDefault}/" class="listLink setDefault">
					{t _set_as_default}
				</a>
				<span class="langDefault">{t _default_language}</span>
			</div>
			
		</div>
	</div>			
</li>

<ul id="languageList" class="activeList_add_delete activeList_add_edit">
</ul>

{literal}
<script type="text/javascript">
	lng.renderList({/literal}{$languageArray}{literal});
	lng.initLangList();
</script>
{/literal}

{*
<!-- {maketext text="_statistic_languages_full" params="$count_all,$count_active"}. -->
*}

{include file="layout/footer.tpl"}