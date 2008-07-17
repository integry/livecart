{if $tax.ID}
	{assign var="action" value="controller=backend.tax action=update id=`$tax.ID`"}
{else}
	{assign var="action" value="controller=backend.tax action=create"}
{/if}

{form handle=$taxForm action=$action id="taxForm_`$tax.ID`" method="post" onsubmit="Backend.Tax.prototype.getInstance(this).save(); return false;" role="taxes.update(edit),taxes.create(index)"}

	{hidden name="ID"}


	<p>
		<label>{t _name}</label>
		<fieldset class="error">
			{textfield name="name"}
			<div class="errorText" style="display: none"></div>
		</fieldset>
	</p>

	{language}
		<label>{t _name}</label>
		<fieldset class="error">
			{textfield name="name_`$lang.ID`" class="observed"}
			<span class="errorText hidden"> </span>
		</fieldset>
	{/language}

	<fieldset class="tax_controls controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="
		tax_save button submit" value="{t _save}" />
		{t _or}
		<a href="#cancel" class="tax_cancel cancel">{t _cancel}</a>
	</fieldset>

{/form}