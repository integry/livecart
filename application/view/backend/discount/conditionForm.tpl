<div class="field">
	<label></label>
	{checkbox name="isFinal" class="checkbox" id="isFinal_`$condition.ID`"}
	<label for="isFinal_{$condition.ID}" class="checkbox">{tip _stop_processing}</label>
</div>

<div class="required field">
	{err for="name"}
		{label {tip DiscountCondition.name}}
		{textfield}
	{/err}
</div>

<div class="couponCode field">
	{err for="couponCode"}
		{label {tip DiscountCondition.couponCode}}
		{textfield}
	{/err}
</div>

<div class="couponLimitType field">
	{err for="couponLimitType"}
		{label {tip DiscountCondition.couponLimitType}}
		{selectfield options=$couponLimitTypes}
	{/err}
</div>

<div class="couponLimitCount field">
	{err for="couponLimitCount"}
		{label {tip DiscountCondition.couponLimitCount}}
		{textfield class="number"}
	{/err}
</div>

<div class="field">
	{err for="validFrom"}
		{label {tip DiscountCondition.validFrom}}
		{calendar id="validFrom"}
	{/err}
</div>

<div class="field">
	{err for="validTo"}
		{label {tip DiscountCondition.validTo}}
		{calendar id="validTo"}
	{/err}
</div>

<div class="field">
	{err for="position"}
		{label {tip DiscountCondition.position}}
		{textfield class="number"}
	{/err}
</div>

<script language="text/javascript">
	Backend.Discount.Editor.prototype.initDiscountForm('{$id}');
</script>

{language}
	<div class="field">
		<label>{tip DiscountCondition.name}</label>
		{textfield name="name_`$lang.ID`"}
	</div>
{/language}