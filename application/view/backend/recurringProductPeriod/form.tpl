{if $recurringProductPeriod.ID}
	{assign var="action" value="controller=backend.recurringProductPeriod" action=update id=`$recurringProductPeriod.ID`"}
{else}
	{assign var="action" value="controller=backend.recurringProductPeriod" action=create"}
{/if}

{form
	onsubmit="Backend.RecurringProductPeriod.prototype.getInstance(this).save(); return false;"
	handle=$form action=$action id="recurringProductPeriodForm_`$recurringProductPeriod.ID`" method="post" }

	{hidden name="ID"}
	{hidden name="productID"}

	{input name="name"}
		{label}{t _name}:{/label}
		{textfield}
	{/input}

	{input name="periodLength"}
		{label}{t _period_length}:{/label}
		{textfield name="periodLength" class="number"} {selectfield name="periodType" options=$periodTypes}
	{/input}

	{input name="rebillCount"}
		{label}{t _rebill_count}:{/label}
		{textfield}
	{/input}

	{foreach $currencies as $currency}
		{input name="ProductPrice_setup_price_`$currency`"}
			{label}{t _setup_price} ([[currency]]):{/label}
			{textfield value=$recurringProductPeriod.ProductPrice_setup[$currency].price class="number"}
		{/input}
	{/foreach}

	{foreach $currencies as $currency}
		{input name="ProductPrice_period_price_`$currency`"}
			{label}{t _period_price} ([[currency]]):{/label}
			{textfield value=$recurringProductPeriod.ProductPrice_period[$currency].price class="number"}
		{/input}
	{/foreach}

	{input name="description"}
		{label}{t _description}:{/label}
		{textarea}
	{/input}

	{language}
		{input name="name_`$lang.ID`"}
			{label}{t _name}:{/label}
			{textfield}
		{/input}

		{input name="description_`$lang.ID`"}
			{label}{t _description}:{/label}
			{textarea name="description_`$lang.ID`"}
		{/input}
	{/language}

	<fieldset class="rpp_controls controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="rpp_save button submit" value="{t _save}" />
		{t _or}
		<a href="#cancel" class="rpp_cancel cancel">{t _cancel}</a>
	</fieldset>

{/form}
