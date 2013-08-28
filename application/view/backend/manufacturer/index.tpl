{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/TabControl.js"}

{includeCss file="library/TabControl.css"}
{includeCss file="library/ActiveList.css"}
{includeCss file="library/ActiveGrid.css"}

{includeJs file="backend/ObjectImage.js"}
{includeJs file="backend/Manufacturer.js"}
{includeCss file="backend/Manufacturer.css"}
{includeCss file="backend/CategoryImage.css"}

{includeJs file="library/ActiveList.js"}
{includeCss file="library/ActiveGrid.css"}

[[ partial("backend/eav/includes.tpl") ]]

{pageTitle help="products"}{t _manufacturers}{/pageTitle}
[[ partial("layout/backend/header.tpl") ]]

<div class="manufacturerGrid" id="manufacturerGrid" class="maxHeight h--50">

	<ul class="menu" id="addMenu">
		<li class="addManufacturerMenu">
			<a href="#" onclick="Backend.Manufacturer.Editor.prototype.showAddForm(this); return false;">
				{t _add_manufacturer}
			</a>
			<span class="progressIndicator" id="currAddMenuLoadIndicator" style="display: none;"></span>
		</li>
	</ul>

	[[ partial("backend/manufacturer/grid.tpl") ]]

</div>

<div id="addManufacturer" style="display: none;">
	TEST
</div>

{* Editors *}
<div id="manufacturerManagerContainer" style="display: none;">
	<fieldset class="container">
		<ul class="menu">
			<li class="done"><a href="#cancelEditing" id="cancel_user_edit" class="cancel">{t _cancel_editing_manufacturer}</a></li>
		</ul>
	</fieldset>

	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="tabUserInfo" class="tab active">
				<a href="{link controller="backend.manufacturer" action=edit id=_id_}"}">{t _manufacturer_info}</a>
				<span class="tabHelp">products</span>
			</li>
			<li id="tabImages" class="tab active">
				<a href="{link controller="backend.manufacturerImage" id=_id_}">{t _manufacturer_images}</a>
				<span class="tabHelp">products</span>
			</li>
		</ul>
	</div>
	<div class="sectionContainer maxHeight h--50"></div>

	{literal}
	<script type="text/javascript">
		Event.observe($("cancel_user_edit"), "click", function(e) {
			e.preventDefault();
			var editor = Backend.Manufacturer.Editor.prototype.getInstance(Backend.Manufacturer.Editor.prototype.getCurrentId(), false);
			editor.cancelForm();
		});
	</script>
	{/literal}
</div>

[[ partial("layout/backend/footer.tpl") ]]