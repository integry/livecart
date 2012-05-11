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

	<p>
		<label>{t _name}</label>
		<fieldset class="error">
			{textfield name="name"}
			<div class="errorText" style="display: none"></div>
		</fieldset>
	</p>

	<p>
		<label>{t _period_length}</label>
		<fieldset class="error">
			{textfield name="periodLength" class="number"} {selectfield name="periodType" options=$periodTypes}
			<div class="errorText" style="display: none"></div>
		</fieldset>
	</p>

	<p>
		<label>{t _rebill_count}</label>
		<fieldset class="error">
			{textfield name="rebillCount"}
			<div class="errorText" style="display: none"></div>
		</fieldset>
	</p>

	<p>
		{foreach $currencies as $currency}
			{if !$shown}
				<label>{t _setup_price}</label>
				{assign var=shown value=true}
			{else}
				<label></label>
			{/if}
			<fieldset class="error">
				{textfield
					name="ProductPrice_setup_price_`$currency`"
					value=$recurringProductPeriod.ProductPrice_setup[$currency].price
					class="number"
				} {$currency}

				<div class="errorText" style="display: none"></div>
			</fieldset>
		{/foreach}
	</p>

	<p>
		{assign var=shown value=false}
		{foreach $currencies as $currency}
			{if !$shown}
				<label>{t _period_price}</label>
				{assign var=shown value=true}
			{else}
				<label></label>
			{/if}

			<fieldset class="error">
				{textfield name="ProductPrice_period_price_`$currency`"
				value=$recurringProductPeriod.ProductPrice_period[$currency].price class="number"} {$currency}
				<div class="errorText" style="display: none"></div>
			</fieldset>
		{/foreach}
	</p>


	<p>
		<label>{t _description}</label>
		<fieldset class="error">
			{textarea name="description"}
			<div class="errorText" style="display: none"></div>
		</fieldset>
	</p>


	{language}
		<p>
			<label>{t _name}</label>
			<fieldset class="error">
				{textfield name="name_`$lang.ID`"}
				<span class="errorText hidden"> </span>
			</fieldset>
		</p>

		<p>
			<label>{t _description}</label>
			<fieldset class="error">
				{textarea name="description_`$lang.ID`"}
				<div class="errorText" style="display: none"></div>
			</fieldset>
		</p>


	{/language}



	<fieldset class="rpp_controls controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="rpp_save button submit" value="{t _save}" />
		{t _or}
		<a href="#cancel" class="rpp_cancel cancel">{t _cancel}</a>
	</fieldset>

{/form}
