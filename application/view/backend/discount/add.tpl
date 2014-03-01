<ul class="menu">
	<li class="done"><a href="#cancelEditing" id="cancel_discount_add" class="cancel">{t _cancel_adding_rule}</a></li>
</ul>

{form action="backend.discount/save" method="POST" id="discountAddForm" handle=form}
	<fieldset>
		<legend>[[ capitalize({t _create_rule}) ]]</legend>

		[[ partial('backend/discount/conditionForm.tpl', ['id': "discountAddForm"]) ]]

	</fieldset>

	<fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" value="{t _save_and_continue}" />
		{t _or}
		<a class="cancel" href="#" onclick="('cancel_discount_add').onclick(event);">{t _cancel}</a>
	</fieldset>
{/form}