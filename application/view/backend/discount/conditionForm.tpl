<p class="required">
	{err for="name"}
		{label {t DiscountCondition.name}}
		{textfield}
	{/err}
</p>

<p>
	{err for="couponCode"}
		{label {t DiscountCondition.couponCode}}
		{textfield}
	{/err}
</p>

<p>
	{err for="validFrom"}
		{label {t DiscountCondition.validFrom}}
		{calendar id="validFrom"}
	{/err}
</p>

<p>
	{err for="validTo"}
		{label {t DiscountCondition.validTo}}
		{calendar id="validTo"}
	{/err}
</p>

{language}
	<p>
		<label>{t DiscountCondition.name}</label>
		{textfield name="name_`$lang.ID`"}
	</p>
{/language}