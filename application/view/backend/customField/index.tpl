{includeJs file="library/livecart.js"}
{includeJs file="library/KeyboardEvent.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/ActiveList.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/TabControl.js"}

{* Calendar *}
{includeJs file="library/dhtmlCalendar/calendar.js"}
{includeJs file="library/dhtmlCalendar/lang/calendar-en.js"}
{*includeJs file="library/dhtmlCalendar/lang/calendar-`$curLanguageCode`.js"*}
{includeJs file="library/dhtmlCalendar/calendar-setup.js"}
{includeCss file="library/dhtmlCalendar/calendar-win2k-cold-2.css"}

{includeJs file="backend/SpecField.js"}
{includeJs file="backend/CustomField.js"}

{includeCss file="library/ActiveList.css"}
{includeCss file="library/ActiveGrid.css"}
{includeCss file="backend/Category.css"}
{includeCss file="backend/SpecField.css"}
{includeCss file="backend/CustomField.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/lightbox/lightbox.css"}

{pageTitle help="cat"}{t _custom_fields}{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="specField_item_blank" class="dom_template">{include file="backend/specField/form.tpl"}</div>
<div id="specField_group_blank" class="dom_template">{include file="backend/specField/group.tpl"}</div>

<div id="catgegoryContainer" class="treeContainer  maxHeight h--60">
	<div id="categoryBrowser" class="treeBrowser"></div>
</div>

<div id="managerContainer" class="treeManagerContainer maxHeight h--60">
	<div id="categoryTabs">
		<div id="tabContainer" class="tabContainer">
			<ul id="tabList" class="tabList tabs">
				<li id="tabFields" class="tab inactive" {denied role="category"}style="display: none"{/denied}>
					<a href="{link controller=backend.eavField action=index query="id=_id_"}">{t _attributes}</a>
					<span> </span>
					<span class="tabHelp">categories.attributes</span>
				</li>
			</ul>
		</div>
		<div id="sectionContainer" class="sectionContainer maxHeight  h--50">
		</div>
	</div>
</div>

<script type="text/javascript">
	Backend.showContainer('managerContainer');

	Backend.CustomField.init();

	Backend.CustomField.addCategories({json array=$nodes});

	Backend.CustomField.activeCategoryId = Backend.CustomField.treeBrowser.getSelectedItemId();
	Backend.CustomField.initPage();

</script>

<div id="specFieldSection"></div>

{include file="layout/backend/footer.tpl"}