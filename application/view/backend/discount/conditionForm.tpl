{input name="isFinal"}
	{checkbox}
	{label}{tip _stop_processing}:{/label}
{/input}

{input name="name"}
	{label}{tip DiscountCondition.name}:{/label}
	{textfield}
{/input}

{input name="couponCode"}
	{label}{tip DiscountCondition.couponCode}:{/label}
	{textfield}
{/input}

{input name="couponLimitType"}
	{label}{tip DiscountCondition.couponLimitType}:{/label}
	{selectfield options=$couponLimitTypes}
{/input}

{input name="couponLimitCount"}
	{label}{tip DiscountCondition.couponLimitCount}:{/label}
	{selectfield options=$couponLimitTypes}
{/input}

{input name="validFrom"}
	{label}{tip DiscountCondition.validFrom}:{/label}
	{calendar}
{/input}

{input name="validTo"}
	{label}{tip DiscountCondition.validTo}:{/label}
	{calendar}
{/input}

{input name="position"}
	{label}{tip DiscountCondition.position}:{/label}
	{textfield class="number"}
{/input}

<script language="text/javascript">
	Backend.Discount.Editor.prototype.initDiscountForm('[[id]]');
</script>

{language}
	{input name="name_`$lang.ID`"}
		{label}{tip DiscountCondition.name}:{/label}
		{textfield}
	{/input}
{/language}