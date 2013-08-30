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

[[ partial("backend/eav/includes.tpl") ]]

{pageTitle help="products"}{t _pricing_rules}{/pageTitle}
[[ partial("layout/backend/header.tpl") ]]

<div class="discountGrid" id="discountGrid" class="maxHeight h--50">
	<ul class="menu">
		<li class="addDiscountMenu">
			<a href="#" onclick="Backend.Discount.Editor.prototype.showAddForm(this); return false;">
				{t _create_rule}
			</a>
			<span class="progressIndicator" id="currAddMenuLoadIndicator" style="display: none;"></span>
		</li>
	</ul>

	[[ partial("backend/discount/grid.tpl") ]]
</div>

<div id="addDiscountContainer" style="display: none;"></div>

{* Editors *}
<div id="discountManagerContainer" style="display: none;">
	<fieldset class="container">
		<ul class="menu">
			<li class="done"><a href="#cancelEditing" id="cancel_user_edit" class="cancel">{t _cancel_editing_discount}</a></li>
		</ul>
	</fieldset>

	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="tabUserInfo" class="tab active">
				<a href="{link controller="backend.discount" action=edit id=_id_}"}"></a>
				<span class="tabHelp">products</span>
			</li>
		</ul>
	</div>
	<div class="sectionContainer maxHeight h--50"></div>

	{literal}
	<script type="text/javascript">
		Backend.Discount.Editor.prototype.Links.add = Backend.Router.createUrl('backend.discount', 'add');
		Backend.Discount.Action.prototype.itemActions = {/literal}{json array=$itemActions}{literal};
		Event.observe($("cancel_user_edit"), "click", function(e) {
			e.preventDefault();
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

				<span>{selectfield name="conditionClass" class="conditionClass" options=$conditionTypes}</span>
				<span>{selectfield name="productComparisonField" class="comparisonField" options=$comparisonFields}</span>
				<span>{selectfield name="comparisonType" class="comparisonType" options=$comparisonTypes}</span>
				<span>{textfield name="comparisonValue" class="number comparisonValue"}</span>

				{block BUSINESS-RULE-CONDITION-PARAMS}

				<span class="conditionTime">
					{t _include_orders_time}
					<select name="conditionTime" class="value">
						<option value="before">{t _condition_time_before}</option>
						<option value="range">{t _condition_time_range}</option>
					</select>

					<span class="conditionTimeBefore">
						<input name="min" type="text" class="minutes text number value" /> {t _minutes}
						<input name="hr" type="text" class="hours text number value" /> {t _hours}
						<input name="day" type="text" class="days text number value" /> {t _days}
						<input name="year" type="text" class="years text number value" /> {t _years}
					</span>

					<span class="conditionTimeRange">
						{calendar name="from" id="from"}
						{calendar name="to" id="to"}
					</span>

					<span class="progressIndicator" style="display: none;"></span>
				</span>

				<span class="subConditionMenu">

					<span class="isReverseContainer">
						<input type="checkbox" class="checkbox isReverse" name="isReverse" id="isReverse">
						<label class="checkbox">{tip _isReverse _isReverse_help}</label>
					</span>

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
						<label class="checkbox" for="isAnyRecord">{tip _all_items_must_be_present}</label>
					</span>
					<div class="clear"></div>
				</div>
			</div>

			<div class="selectRecordContainer">
				<ul></ul>
				<div class="clear"></div>
			</div>

			<div class="valueContainer">
				<ul></ul>
				<div class="clear"></div>
			</div>

			<div class="ruleFields">
				{foreach from=$ruleFields key=ruleClass item=fields}
					{% if $fields %}
					<div class="classContainer [[ruleClass]]">
						{foreach from=$fields item=field}
							<p>
								<label>{translate text=$field.label}</label>
								<span>
									{% if $field.type == 'number' %}
										{textfield class="text number ruleField `$field.name`" name=$field.name}
									{% elseif $field.type == 'select' %}
										{textfield class="text wide ruleField `$field.name`" name=$field.name}
									{% elseif $field.type == 'select' %}
										{selectfield class="ruleField `$field.name`" name=$field.name options=$field.options}
									{% endif %}
									<span class="progressIndicator" style="display: none;"></span>
								</span>
							</p>
						{/foreach}
					</div>
					{% endif %}
				{/foreach}
			</div>

			<ul class="conditionContainer" style="display: none;">
				<div class="allSubsMenu">
					<span>
						<input type="checkbox" value="1" class="checkbox isAllSubconditions" id="isAllSubconditions" name="isAllSubconditions" />
						<label class="checkbox" for="isAllSubconditions">{tip _all_subconditions_must_match}</label>
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
				<label>{tip _action}</label>
				<span>{selectfield name="actionClass" class="actionClass" options=$actionTypes}</span>
			</p>

			<div class="amountFields">
				<p>
					<label>{t _amount}</label>
					<span>{textfield name="amount" class="number comparisonValue"}</span>
					<span class="percent">%</span>
					<span class="currency">[[currencyCode]]</span>
				</p>
				<p>
					<label>{tip _discount_step _discount_step_descr}</label>
					<span>{textfield name="discountStep" class="number discountStep"}</span>
				</p>
				<p>
					<label>{tip _discount_limit _discount_limit_descr}</label>
					<span>{textfield name="discountLimit" class="number discountLimit"}</span>
				</p>

				<label></label>
				<span>
					<input type="checkbox" class="checkbox isOrderLevel" name="isOrderLevel" />
					<label class="checkbox">{tip _is_order_level _discount_isOrderLevel_descr}</label>
				</span>
				<div class="clear"></div>
			</div>

			<div class="actionFields">
				{foreach from=$actionFields key=actionClass item=fields}
					{% if $fields %}
						<div class="classContainer [[actionClass]]">
							{foreach from=$fields item=field}
								<p>
									<label>{translate text=$field.label}</label>
									<span>
										{% if $field.type == 'number' %}
											{textfield class="text number actionField `$field.name`" name=$field.name}
										{% elseif $field.type == 'text' %}
											{textfield class="text wide actionField `$field.name`" name=$field.name}
										{% elseif $field.type == 'select' %}
											{selectfield class="actionField `$field.name`" name=$field.name options=$field.options}
										{% endif %}
										<span class="progressIndicator" style="display: none;"></span>
									</span>
								</p>
							{/foreach}
						</div>
					{% endif %}
				{/foreach}
			</div>

			<div class="clear"></div>

			<div class="applyTo">
				<p>
					<label>{t _apply_to}</label>
					<span>{selectfield name="type" class="applyTo" options=$applyToChoices}</span>
				</p>

				<div class="conditionContainer actionCondition">
					<ul class="conditionContainer root" style="display: none;"></ul>
				</div>
			</div>
		</li>
	{/form}
</div>

[[ partial("layout/backend/footer.tpl") ]]