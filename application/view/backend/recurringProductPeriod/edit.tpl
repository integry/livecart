{include file="backend/recurringProductPeriod/form.tpl" recurringProductPeriod=$recurringProductPeriod form=$form}
<script type=text/javascript>
	var newForm = Backend.RecurringProductPeriod.prototype.getInstance($("recurringProductPeriodForm_{$recurringProductPeriod.ID}"));
</script>