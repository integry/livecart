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

{* Template nodes *}
<div id="conditionTemplate">
	{form handle=$conditionForm}
		<li>
			<div class="conditionInfo">
				<span class="conditionDeleteMenu">
					<img src="image/silk/cancel.png" class="conditionDelete" />
					<span class="progressIndicator" style="display: none;"></span>
				</span>

				<span>{selectfield name="type" class="conditionType" options=$conditionTypes}</span>
				<span>{selectfield name="productComparisonField" class="comparisonField" options=$comparisonFields}</span>
				<span>{selectfield name="comparisonType" class="comparisonType" options=$comparisonTypes}</span>
				<span>{textfield name="comparisonValue" class="number comparisonValue"}</span>

				<span class="subConditionMenu">
					<a href="#" class="subCondition">{t _add_subcondition}</a>
					<span class="progressIndicator" style="display: none;"></span>
				</span>
			</div>

			<div class="recordContainer" style="display: none;">
				<ul class="menu">
					<div class="conditionItems">
						<li class="addConditionProduct"><a href="#">{t _add_product}</a></li>
						<li class="addConditionCategory"><a href="#">{t _add_category}</a></li>
						<li class="addConditionManufacturer"><a href="#">{t _add_manufacturer}</a></li>
					</div>

					<li class="addConditionUserGroup"><a href="#">{t _add_usergroup}</a></li>
					<li class="addConditionUser"><a href="#">{t _add_user}</a></li>
					<li class="addConditionDeliveryZone"><a href="#">{t _add_deliveryzone}</a></li>
				</ul>
				<ul class="records"></ul>
				<div class="allItemsMenu">
					<span>
						<input type="checkbox" value="1" class="checkbox isAnyRecord" id="isAnyRecord" name="isAnyRecord" />
						<label class="checkbox" for="isAnyRecord">{t _all_items_must_be_present}</label>
					</span>
					<div class="clear"></div>
				</div>
			</div>

			<div class="selectRecordContainer">
				<ul></ul>
				<div class="clear"></div>
			</div>

			<ul class="conditionContainer" style="display: none;">
				<div class="allSubsMenu">
					<span>
						<input type="checkbox" value="1" class="checkbox isAllSubconditions" id="isAllSubconditions" name="isAllSubconditions" />
						<label class="checkbox" for="isAllSubconditions">{t _all_subconditions_must_match}</label>
					</span>
					<div class="clear"></div>
				</div>
			</ul>
		</li>
	{/form}
</div>

<div id="recordTemplate">
	<li>
		<span class="recordDeleteMenu">
			<img src="image/silk/cancel.png" class="recordDelete" />
			<span class="progressIndicator" style="display: none;"></span>
		</span>
		<a href="#"><span class="recordClass"></span><span class="recordTypeSep">: </span><span class="recordName"></span></a>
	</li>
</div>

<div id="selectRecordTemplate">
	<li>
		<span>
			<input type="checkbox" class="checkbox" />
		</span>
		<label class="checkbox"><a href="#"></a></label>
	</li>
</div>

<div id="actionTemplate">
	{form handle=$conditionForm}
		<li>

			<label style="width: 80px;"></label>
			<span>
				<input type="checkbox" class="checkbox isEnabled" name="isEnabled" />
				<label class="checkbox">{t _is_enabled}</label>
			</span>
			<div class="clear"></div>

			<p>
				<label>{t _action}</label>
				<span>{selectfield name="amountMeasure" class="actionType" options=$actionTypes}</span>
			</p>
			<p>
				<label>{t _amount}</label>
				<span>{textfield name="amount" class="number comparisonValue"}</span>
				<span class="percent">%</span>
				<span class="currency">{$currencyCode}</span>
			</p>
			<p>
				<label>{t _apply_to}</label>
				<span>{selectfield name="type" class="applyTo" options=$applyToChoices}</span>
			</p>

			<div class="conditionContainer actionCondition">
				<ul class="conditionContainer root" style="display: none;"></ul>
			</div>
		</li>
	{/form}
</div>

{include file="layout/backend/footer.tpl"}