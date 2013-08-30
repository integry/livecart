{% if $deliveryZone.ID == -1 %}
	<p class="inlineWarning">
		{t _tip_default_zone_taxes}
	</p>
{% endif %}

{% if empty(taxes) %}
	<div class="noRecords"><div>{t _no_taxes} <a href="{link controller="backend.tax"}" class="menu">{t _add_tax}</a></div></div>
{% else %}
	{form action="controller=backend.taxRate action=save id=`$deliveryZone.ID`" method="post" onsubmit="new LiveCart.AjaxRequest(this); return false;" handle=$form}
	<table class="taxes">
		{foreach from=$taxes item=tax name="taxes"}
			<tr>
				<td class="taxName">[[tax.name_lang]]:</td>
				<td>{textfield class="text number" name="tax_`$tax.ID`_"}%</td>
			</tr>
			{foreach from=$classes item=class name="classes"}
				<tr class="classes">
					<td class="taxClassName">[[class.name_lang]]:</td>
					<td>{textfield class="text number" name="tax_`$tax.ID`_`$class.ID`"}%</td>
				</tr>
			{/foreach}
		{/foreach}
	</table>

	<fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" value="{t _save}"/> or
		<a href="#" class="cancel" onclick="this.up('form').reset(); return false;">{t _cancel}</a>
	</fieldset>

	{/form}
{% endif %}