{includeJs file="library/livecart.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="backend/Template.js"}
{includeJs file="library/TabControl.js"}
{includeCss file="library/TabControl.css"}
{includeJs file="library/editarea/edit_area_full.js"}

{includeCss file="backend/Template.css"}
{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}

{pageTitle help="customize.templates"}{t _edit_templates}{/pageTitle}
[[ partial("layout/backend/header.tpl") ]]

<div id="templatePageContainer">

	[[ partial('backend/quickSearch/form.tpl', ['limit': 10, 'hint': _hint_template_search, 'formid': "TemplateSearch", 'classNames': "SearchableTemplate"]) ]]

	<div class="treeContainer">
		<div id="templateBrowser" class="treeBrowser"></div>

		<ul id="categoryBrowserActions" class="verticalMenu">
			<li class="addTreeNode" id="createTemplate">
				<a href="{link controller="backend.template" action=add query="tabid=_tabid_"}">
					{t _create_template}
				</a>
			</li>
			<li class="removeTreeNode" id="deleteTemplate" style="display: none;">
				<a href="{link controller="backend.template" action=delete query="file=_id_"}">
					{t _delete_template}
				</a>
			</li>
		</ul>

	</div>

	<div class="treeManagerContainer">
		<div class="templateContent">
			<div id="templateTabContainer" class="tabContainer" style="height:100%">
				<div id="loadingNewsletter" style="display: none; position: absolute; text-align: center; width: 100%; padding-top: 200px; z-index: 50000;">
					<span style="padding: 40px; background-color: white; border: 1px solid black;">{t _loading_newsletter}<span class="progressIndicator"></span></span>
				</div>
				<ul class="tabList tabs">
				</ul>
				<div class="sectionContainer" style="display:none;">
				</div>
				<div class="notabsContainer">
					[[ partial("backend/template/emptyPage.tpl") ]]
				</div>
			</div>
		</div>
	</div>
</div>

{literal}
<script type="text/javascript">
	// creates global variable backendTemplateInstance
	backendTemplateInstance = new Backend.Template({/literal}[[categories]]{literal});
	backendTemplateInstance.urls['edit'] = '{/literal}{link controller="backend.template" action=edit}?file=_id_&tabid=_tabid_{literal}';
	backendTemplateInstance.urls['empty'] = '{/literal}{link controller="backend.template" action=emptyPage}{literal}';
	backendTemplateInstance.urls['templateData'] = '{/literal}{link controller="backend.template" action=templateData}?file=_id_&tabid=_tabid_&theme=_theme_&version=_version_{literal}';
	backendTemplateInstance.translations['_tab_title_new'] = "{/literal}{t _tab_title_new}{literal}";
	backendTemplateInstance.setTabControlInstance(
		TabControl.prototype.getInstance(
			'templateTabContainer',
			Backend.Template.prototype.getTabUrl,
			Backend.Template.prototype.getContentTabId,
			{
				afterClick:backendTemplateInstance.tabAfterClickCallback.bind(backendTemplateInstance)
			}
		)
	);
</script>
{/literal}

[[ partial("layout/backend/footer.tpl") ]]