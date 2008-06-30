{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/TabControl.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="library/ActiveList.css"}
{includeCss file="library/ActiveGrid.css"}

{includeJs file="backend/Manufacturer.js"}
{includeCss file="backend/Manufacturer.css"}

{includeJs file="library/ActiveList.js"}
{includeCss file="library/ActiveGrid.css"}

{include file="backend/eav/includes.tpl"}

{pageTitle help="products"}{t _manufacturers}{/pageTitle}
{include file="layout/backend/header.tpl"}

<div class="manufacturerGrid" id="manufacturerGrid" class="maxHeight h--50">

	{include file="backend/manufacturer/grid.tpl"}

</div>

{* User managing container *}
<div id="manufacturerManagerContainer" style="display: none;">
	<fieldset class="container">
		<ul class="menu">
			<li class="done"><a href="#cancelEditing" id="cancel_user_edit" class="cancel">{t _cancel_editing_manufacturer}</a></li>
		</ul>
	</fieldset>

	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="tabUserInfo" class="tab active">
				<a href="{link controller=backend.manufacturer action=edit id=_id_}"}">{t _manufacturer_info}</a>
				<span class="tabHelp">products</span>
			</li>
			<li id="tabOrdersList" class="tab active">
				<a href="{link controller=backend.customerOrder action=orders id=1 query='userID=_id_'}">{t _orders}</a>
				<span class="tabHelp">products</span>
			</li>
		</ul>
	</div>
	<div class="sectionContainer maxHeight h--50"></div>

	{literal}
	<script type="text/javascript">
		Event.observe($("cancel_user_edit"), "click", function(e) {
			Event.stop(e);
			var editor = Backend.Manufacturer.Editor.prototype.getInstance(Backend.Manufacturer.Editor.prototype.getCurrentId(), false);
			editor.cancelForm();
		});
	</script>
	{/literal}
</div>

{include file="layout/backend/footer.tpl"}