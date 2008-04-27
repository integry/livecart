{includeJs file="library/livecart.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="backend/Template.js"}

{includeJs file="library/editarea/edit_area_full.js"}

{includeCss file="backend/Template.css"}
{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}

{pageTitle help="customize.templates"}{t _edit_templates}{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="templatePageContainer">
	<div class="treeContainer">
		<div id="templateBrowser" class="treeBrowser"></div>

		<ul id="categoryBrowserActions" class="verticalMenu">
			<li class="addTreeNode" id="createTemplate">
				<a href="{link controller=backend.template action=add}">
					{t _create_template}
				</a>
			</li>
			<li class="removeTreeNode" id="deleteTemplate" style="display: none;">
				<a href="{link controller=backend.template action=delete query="file=_id_"}">
					{t _delete_template}
				</a>
			</li>
		</ul>

	</div>

	<div class="treeManagerContainer">
		<div id="templateContent">
			{include file="backend/template/emptyPage.tpl"}
		</div>
	</div>
</div>

{literal}
<script type="text/javascript">
	var settings = new Backend.Template({/literal}{$categories}{literal});
	settings.urls['edit'] = '{/literal}{link controller=backend.template action=edit}?file=_id_{literal}';
	settings.urls['empty'] = '{/literal}{link controller=backend.template action=emptyPage}{literal}';
</script>
{/literal}

{include file="layout/backend/footer.tpl"}