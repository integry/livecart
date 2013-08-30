[[ checkbox('isFinal', tip('_stop_processing')) ]]

[[ textfld('name', tip('DiscountCondition.name')) ]]

[[ textfld('couponCode', tip('DiscountCondition.couponCode')) ]]

[[ selectfld('couponLimitType', tip( 'DiscountCondition.couponLimitType'), couponLimitTypes) ]]

[[ selectfld('couponLimitCount', tip( 'DiscountCondition.couponLimitCount'), couponLimitTypes) ]]

[[ datefld('validFrom', tip('DiscountCondition.validFrom')) ]]

[[ datefld('validTo', tip('DiscountCondition.validTo')) ]]

[[ numberfld('position', tip('DiscountCondition.position')) ]]

<script language="text/javascript">
	Backend.Discount.Editor.prototype.initDiscountForm('[[id]]');
</script>

{language}
	[[ textfld('name_`$lang.ID`', tip('DiscountCondition.name')) ]]
{/language}