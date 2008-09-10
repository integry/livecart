{form handle=$form action="controller=backend.discount action=save id=`$condition.ID`" id="userInfo_`$condition.ID`_form" onsubmit="Backend.Discount.Editor.prototype.getInstance(`$condition.ID`, false).submitForm(); return false;" method="post" role="product.update"}

	<fieldset>
		<legend>{t _main_info}</legend>
		{include file="backend/discount/conditionForm.tpl"}

		<fieldset class="controls">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" name="save" class="submit" value="{t _save}">
			{t _or}
			<a class="cancel" href="#">{t _cancel}</a>
		</fieldset>

	</fieldset>

{/form}

<fieldset class="conditions" id="condition_{$condition.ID}">
	<legend>{t _conditions}</legend>

	<ul class="menu">
		<li class="addRootCondition">
			<a href="#" id="addRootCondition_{$condition.ID}">{t _add_new_condition}</a>
			<span class="progressIndicator" style="display: none;"></span>
		</li>
	</ul>

	<ul class="conditionContainer root"></ul>

</fieldset>

<fieldset class="actions">
	<legend>{t _actions}</legend>

</fieldset>

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

<script type="text/javascript">
	var inst = new Backend.Discount.Condition({json array=$condition}, {json array=$records}, $('condition_{$condition.ID}').down('.conditionContainer'));
	Event.observe($('addRootCondition_{$condition.ID}'), 'click', inst.createSubCondition.bind(inst));
</script>