{form handle=$form action="controller=backend.discount action=save id=`$condition.ID`" id="userInfo_`$condition.ID`_form" onsubmit="Backend.Manufacturer.Editor.prototype.getInstance(`$manufacturer.ID`, false).submitForm(); return false;" method="post" role="product.update"}

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

<fieldset>
	<legend>{t _conditions}</legend>

</fieldset>

<fieldset>
	<legend>{t _actions}</legend>

</fieldset>