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

{pageTitle help="template"}Edit Templates{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="pageContainer">
	<div class="treeContainer">
		<div id="templateBrowser" class="treeBrowser"></div>
    	<div id="confirmations"></div>
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