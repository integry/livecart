{if $tax.ID}
	{assign var="action" value="controller=backend.tax action=update id=`$tax.ID`"}
{else}
	{assign var="action" value="controller=backend.tax action=create"}
{/if}

{form handle=$taxForm action=$action id="taxForm_`$tax.ID`" method="post" onsubmit="Backend.Tax.prototype.getInstance(this).save(); return false;" role="taxes.update(edit),taxes.create(index)"}
	{hidden name="ID"}

	{input name="name"}
		{label}{t _name}:{/label}
		{textfield}
	{/input}

	{language}
		{input name="name_`$lang.ID`"}
			{label}{t _name}:{/label}
			{textfield class="observed"}
		{/input}
	{/language}

	<fieldset class="taxZonesContainer">
		{*
			for some reason smarty does not understand $foo[1][-1], but $foo[1][$bar] where $bar value -1 will work.
			Define variable containing -1
		*}
		{assign var="Default" value="-1"}
		<table class="taxZones">
			<tbody>
				{foreach $zones as $zone}
					<tr>
						<td class="zoneName">
							{if $zone.ID < 1}
								<h3>{t _base_tax_rate}</h3>
								<p>{t _base_tax_rates_description}</p>
							{else}
								<h3>[[zone.name]]</h3>
							{/if}
						</td>
						<td>
							{textfield class="number" value=$zone.taxRates[$zone.ID][$Default].rate|default:0 name="taxRate_`$zone.ID`_`$Default`"} %
							<div class="errorText" style="display: none"></div>
							{if count($classes)}
								<table class="taxClass">
									<tbody>
										{foreach $classes as $class}
											<tr>
												<td>[[class.name_lang]]</td>
												<td>
													{input name="taxRate_`$zone.ID`_`$class.ID`"}
														{textfield class="number" value=$zone.taxRates[$zone.ID][$class.ID].rate|default:0} %
													{/input}
												</td>
											</tr>
										{/foreach}
									</tbody>
								</table>
							{/if}
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</fieldset>
	<fieldset class="tax_controls controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="
		tax_save button submit" value="{t _save}" />
		{t _or}
		<a href="#cancel" class="tax_cancel cancel">{t _cancel}</a>
	</fieldset>


{/form}