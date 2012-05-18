<div>
	<fieldset class="addForm">
		<legend>{t _add_currency|capitalize}</legend>
		<form onSubmit="curr.add(this); return false;" action="{link controller="backend.currency" action=add}">
			<select name="id" class="select" id="addLang-sel">
			   {html_options options=$currencies}
			</select>
			<span class="progressIndicator" id="addCurrIndicator" style="display: none;"></span>
			<input type="submit" value="{t _add_curr_button}" name="sm" class="submit" />
			<span>{t _or} </span>
			<a href="#" class="cancel" onClick="Backend.Currency.prototype.hideNewForm(); return false;">{t _cancel}</a>
		</form>
	</fieldset>
</div>