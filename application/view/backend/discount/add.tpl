<ul class="menu">
	<li class="done"><a href="#cancelEditing" id="cancel_discount_add" class="cancel">{t _cancel_adding_rule}</a></li>
</ul>

{form action="controller=backend.discount action=save" method="POST" id="discountAddForm" handle=$form}
	<fieldset>
		<legend>{t _create_rule|capitalize}</legend>

		{include file="backend/discount/conditionForm.tpl"}

	</fieldset>

	<fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" value="{tn _save_and_continue}" />
		{t _or}
		<a class="cancel" href="#" onclick="$('cancel_discount_add').onclick(event);">{t _cancel}</a>
	</fieldset>
{/form}