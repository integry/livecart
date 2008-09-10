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

{includeJs file="backend/Category.js"}
{includeCss file="backend/Category.css"}

{includeJs file="backend/Discount.js"}
{includeCss file="backend/Discount.css"}

{includeJs file="library/ActiveList.js"}
{includeCss file="library/ActiveGrid.css"}

{include file="backend/eav/includes.tpl"}

{pageTitle help="products"}{t _pricing_rules}{/pageTitle}
{include file="layout/backend/header.tpl"}

<div class="discountGrid" id="discountGrid" class="maxHeight h--50">
	<ul class="menu">
		<li class="addDiscountMenu">
			<a href="#" onclick="Backend.Discount.Editor.prototype.showAddForm(this); return false;">
				{t _create_rule}
			</a>
			<span class="progressIndicator" id="currAddMenuLoadIndicator" style="display: none;"></span>
		</li>
	</ul>

	{include file="backend/discount/grid.tpl"}
</div>

<div id="addDiscountContainer" style="display: none;"></div>

{* Editors *}
<div id="discountManagerContainer" style="display: none;">
	<fieldset class="container">
		<ul class="menu">
			<li class="done"><a href="#cancelEditing" id="cancel_user_edit" class="cancel">{t _cancel_editing_manufacturer}</a></li>
		</ul>
	</fieldset>

	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="tabUserInfo" class="tab active">
				<a href="{link controller=backend.discount action=edit id=_id_}"}"></a>
				<span class="tabHelp">products</span>
			</li>
		</ul>
	</div>
	<div class="sectionContainer maxHeight h--50"></div>

	{literal}
	<script type="text/javascript">
		Backend.Discount.Editor.prototype.Links.add = Backend.Router.createUrl('backend.discount', 'add');
		Event.observe($("cancel_user_edit"), "click", function(e) {
			Event.stop(e);
			var editor = Backend.Discount.Editor.prototype.getInstance(Backend.Discount.Editor.prototype.getCurrentId(), false);
			editor.cancelForm();
		});
	</script>
	{/literal}
</div>

{include file="layout/backend/footer.tpl"}