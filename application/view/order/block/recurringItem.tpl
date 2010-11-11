{if $item.Product.type == 3} {* Product::TYPE_RECURRING *}
	<div class="recurringitem">
		<span class="progressIndicator" style="display:none;"></span>
		<select onchange="new Product.ChangeRecurringPlanAction('{link controller="order" action=changeRecurringProductPeriod id=$item.ID}', this)">
			{foreach $recurringItemsByItem[$item.ID] as $period}
				<option
					value="{$period.ID}"
					{if $item.recurringID == $period.ID} selected="selected"{/if}
				>{$period.name|escape} ({$period.ProductPrice_period.formated_price.$currency} {t _every} {t `$periodTypesSingle[$period.periodType]`} {t _for} {$period.rebillCount} {t `$periodTypesPlural[$period.periodType]`})
			{/foreach}
		</select>
	</div>
{/if}