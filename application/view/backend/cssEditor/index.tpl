{includeJs file="library/livecart.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="backend/CssEditor.js"}

{includeJs file="library/editarea/edit_area_full.js"}

{includeCss file="backend/CssEditor.css"}
{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}

{pageTitle help="customize.css"}{t _edit_css}{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="templatePageContainer">
	<div class="treeContainer">
		<div id="templateBrowser" class="treeBrowser"></div>
	</div>

	<div class="treeManagerContainer">
		<div id="templateContent">
			{include file="backend/cssEditor/emptyPage.tpl"}
		</div>
	</div>
</div>

{literal}
<script type="text/javascript">
	var settings = new Backend.CssEditor({/literal}{$categories}{literal});
	settings.urls['edit'] = '{/literal}{link controller=backend.cssEditor action=edit}?file=_id_{literal}';
	settings.urls['empty'] = '{/literal}{link controller=backend.cssEditor action=emptyPage}{literal}';
</script>
{/literal}

{include file="layout/backend/footer.tpl"}