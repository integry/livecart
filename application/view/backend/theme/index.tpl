{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="library/jscolor/jscolor.js"}
{includeJs file="frontend/Customize.js"}
{includeJs file="backend/Theme.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="backend/Theme.css"}

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

			<li id="removeMenu" class="removeTreeNode"><a href="" onclick="pageHandler.deleteSelected(); return false;">{t _remove}</a></li>
<div style="display: none;">
			<li id="exportMenu" class="exportTreeNode"><a href="" onclick="pageHandler.showAddForm(); return false;">{t _export}</a></li>
			<li id="importMenu" class="importTreeNode"><a href="" onclick="pageHandler.deleteSelected(); return false;">{t _import}</a></li>
</div>
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

<div style="display: none;">
					<li id="tabCss" class="tab">
						<a href="{link controller=backend.theme action=css query='id=_id_'}">{t _css}</a>
					</li>
					<li id="tabFiles" class="tab">
						<a href="{link controller=backend.theme action=files query='id=_id_'}">{t _files}</a>
					</li>
</div>

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
</script>
{/literal}

<div class="clear"></div>

{include file="layout/backend/footer.tpl"}