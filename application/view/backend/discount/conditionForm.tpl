<div class="field">
	<label></label>
	{checkbox name="isFinal" class="checkbox" id="isFinal_`$condition.ID`"}
	<label for="isFinal_{$condition.ID}" class="checkbox">{t _stop_processing}</label>
</div>

<div class="required field">
	{err for="name"}
		{label {t DiscountCondition.name}}
		{textfield}
	{/err}
</div>

<div class="couponCode field">
	{err for="couponCode"}
		{label {t DiscountCondition.couponCode}}
		{textfield}
	{/err}
</div>

<div class="couponLimitType field">
	{err for="couponLimitType"}
		{label {t DiscountCondition.couponLimitType}}
		{selectfield options=$couponLimitTypes}
	{/err}
</div>

<div class="couponLimitCount field">
	{err for="couponLimitCount"}
		{label {t DiscountCondition.couponLimitCount}}
		{textfield class="number"}
	{/err}
</div>

<div class="field">
	{err for="validFrom"}
		{label {t DiscountCondition.validFrom}}
		{calendar id="validFrom"}
	{/err}
</div>

<div class="field">
	{err for="validTo"}
		{label {t DiscountCondition.validTo}}
		{calendar id="validTo"}
	{/err}
</div>

<div class="field">
	{err for="position"}
		{label {t DiscountCondition.position}}
		{textfield class="number"}
	{/err}
</div>

<script language="text/javascript">
	Backend.Discount.Editor.prototype.initDiscountForm('{$id}');
</script>

{language}
	<div class="field">
		<label>{t DiscountCondition.name}</label>
		{textfield name="name_`$lang.ID`"}
	</div>
{/language}