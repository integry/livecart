{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="library/jscolor/jscolor.js"}
{includeJs file="frontend/Customize.js"}
{includeJs file="backend/Theme.js"}
{includeJs file="library/editarea/edit_area_full.js"}
{includeJs file="backend/CssEditor.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="backend/Theme.css"}
{includeCss file="backend/CssEditor.css"}

{includeJs file="library/ActiveList.js"}
{includeCss file="library/ActiveList.css"}
{includeJs file="backend/ThemeFile.js"}

{includeJs file="library/lightbox/lightbox.js"}
{includeCss file="library/lightbox/lightbox.css"}


{pageTitle help="content.pages"}{t _themes}{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="themeContainer">
	<div class="treeContainer">
		<div id="pageBrowser" class="treeBrowser"></div>

		<ul class="verticalMenu">
			<li id="addMenu" class="addTreeNode"><a href="" onclick="pageHandler.showAddForm(); return false;">{t _add_new}</a></li>
			<fieldset id="addForm" style="display: none;">
				{form action="controller=backend.theme action=add" method="POST" handle=$addForm onsubmit="pageHandler.addTheme(); return false;"}
					{err for="name"}
						{{label {t _theme_name} }}:
						{textfield class="text themeName"}
					{/err}

					<div>
						<span class="progressIndicator" style="display: none;"></span>
						<input type="submit" value="{tn _add}" class="submit" />
						{t _or}
						<a class="cancel" href="#" onclick="pageHandler.hideAddForm(); return false;">{t _cancel}</a>
					</div>
				{/form}
			</fieldset>

			<li id="importMenu" class="importTreeNode"><a href="" onclick="pageHandler.importTheme(); return false;">{t _import_theme}</a></li>
			<fieldset id="importForm" style="display: none;">
				{form handle=$importForm action="controller=backend.theme action=import"
					target="themeImportTarget" method="POST" enctype="multipart/form-data"
					autocomplete="off"
				}
					<span class="progressIndicator" style="display: none;"></span>
					<p class="required">
						{err for="file"}
							{{label {t _select_file}: }}
							{filefield value="" name="theme"}
							<br />
							<span class="maxFileSize">{maketext text=_max_file_size params=$maxSize}</span>
						{/err}
					</p>
					
					<fieldset class="controls">
						<span class="progressIndicator" style="display: none;"></span>
						<input type="submit" name="upload" class="submit" value="{tn _import}"> 
						{t _or} 
						<a class="cancel" href="#" onclick="pageHandler.hideImportForm(); return false;">{t _cancel}</a>
					</fieldset>
				{/form}
				<iframe name="themeImportTarget" id="themeImportTarget" style="display:none"></iframe>
			</fieldset>

			<li id="copyMenu" class="exportTreeNode"><a href="" onclick="pageHandler.showCopyForm(); return false;">{t _copy_theme}</a></li>
			<fieldset id="copyForm" style="display: none;">
				{form action="controller=backend.theme action=copyTheme" method="POST" handle=$copyForm onsubmit="pageHandler.copyTheme(); return false;"}
					<input type="hidden" name="id" value="" id="copyFromID" />
					{err for="name"}
						{{label {t _theme_name} }}:
						{textfield class="text themeName"}
					{/err}

					<div>
						<span class="progressIndicator" id="copyFormProgressIndicator" style="display: none;"></span>
						<input type="submit" value="{tn _copy}" class="submit" />
						{t _or}
						<a class="cancel" href="#" onclick="pageHandler.hideCopyForm(); return false;">{t _cancel}</a>
					</div>
				{/form}
			</fieldset>

			<li id="exportMenu" class="exportTreeNode"><a href="" onclick="pageHandler.exportSelected(); return false;">{t _export_theme}</a></li>
			<li id="removeMenu" class="removeTreeNode"><a href="" onclick="pageHandler.deleteSelected(); return false;">{t _remove}</a></li>

		</ul>
	</div>

	<div class="treeManagerContainer maxHeight h--100">
		<div id="tabContainer">
			<div class="tabContainer">
				<ul class="tabList tabs">
					<li id="tabSettings" class="tab active">
						<a href="{link controller=backend.theme action=edit query='id=_id_'}"}">{t _settings}</a>
					</li>
					<li id="tabColors" class="tab">
						<a href="{link controller=backend.theme action=colors query='id=_id_'}">{t _colors}</a>
					</li>
					<li id="tabCss" class="tab">
						<a href="{link controller=backend.cssEditor action=edit query='file=_id_'}">{t _css}</a>
					</li>
					<li id="tabFiles" class="tab">
						<a href="{link controller=backend.themeFile action=index query='id=_id_'}">{t _files}</a>
					</li>
					{block THEME_TABS}
				</ul>
			</div>
			<div class="sectionContainer maxHeight h--50"></div>
		</div>
	</div>
</div>

{literal}
<script type="text/javascript">
	var pageHandler = new Backend.Theme({/literal}{$themes}{literal});
	pageHandler.urls['edit'] = '{/literal}{link controller=backend.theme action=edit}?id=_id_{literal}';
	pageHandler.urls['add'] = '{/literal}{link controller=backend.theme action=add}{literal}';
	pageHandler.urls['delete'] = '{/literal}{link controller=backend.theme action=delete}?id=_id_{literal}';
	pageHandler.urls['moveup'] = '{/literal}{link controller=backend.theme action=reorder}?order=up&id=_id_{literal}';
	pageHandler.urls['movedown'] = '{/literal}{link controller=backend.theme action=reorder}?order=down&id=_id_{literal}';
	pageHandler.urls['empty'] = '{/literal}{link controller=backend.theme action=emptyPage}{literal}';
	pageHandler.urls['create'] = '{/literal}{link controller=backend.theme action=create}{literal}';
	pageHandler.urls['update'] = '{/literal}{link controller=backend.theme action=update}{literal}';
	pageHandler.urls['export'] = '{/literal}{link controller=backend.theme action=export}?id=_id_{literal}';
</script>
{/literal}

<div class="clear"></div>

{include file="layout/backend/footer.tpl"}
