{form handle=$form action="controller=backend.discount action=save id=`$condition.ID`" id="userInfo_`$condition.ID`_form" onsubmit="Backend.Discount.Editor.prototype.getInstance(`$condition.ID`, false).submitForm(); return false;" method="post" role="product.update"}

	<fieldset>
		<legend>{t _main_info}</legend>

		<p>
			<label></label>
			{checkbox name="isEnabled" class="checkbox" id="isEnabled_`$condition.ID`"}
			<label for="isEnabled_{$condition.ID}" class="checkbox">{t _is_enabled}</label>
		</p>

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

	<ul class="menu">
		<li class="addAction">
			<a href="#" id="addAction_{$condition.ID}" onclick="Backend.Discount.Action.prototype.addAction(event, {$condition.ID});">{t _add_new_action}</a>
			<span class="progressIndicator" style="display: none;"></span>
		</li>
	</ul>

	<ul class="actionContainer activeList activeList_add_delete activeList_add_sort" id="actionContainer_{$condition.ID}"></ul>

</fieldset>

<script type="text/javascript">
	var inst = new Backend.Discount.Condition({json array=$condition}, {json array=$records}, $('condition_{$condition.ID}').down('.conditionContainer'));
	Event.observe($('addRootCondition_{$condition.ID}'), 'click', inst.createSubCondition.bind(inst));

	var action = null;
	{json array=$actions}.each(function(act) {literal}{action = Backend.Discount.Action.prototype.createAction(act); });
	if (action)
	{
		action.initializeList();
	}
	{/literal}
</script>