{if $item.Product.type == 3} {* Product::TYPE_RECURRING *}
	<div class="recurringitem">
		<span class="progressIndicator" style="display:none;"></span>
		<select 
			id="recurringBillingPlanFor[[item.ID]]"
			name="recurringBillingPlanFor[[item.ID]]"
			onchange="new Product.ChangeRecurringPlanAction('{link controller="order"
			action=changeRecurringProductPeriod id=$item.ID}', this)"
		}
			{foreach $recurringItemsByItem[$item.ID] as $period}
				<option
					value="[[period.ID]]"
					{if $item.recurringID == $period.ID} selected="selected"{/if}
				>
					{$period.name_lang|escape} ({$period.ProductPrice_period.formated_price.$currency} 
					{t _every} 
					{if $period.periodLength == 1}{t `$periodTypesSingle[$period.periodType]`}{else}[[period.periodLength]] {t `$periodTypesPlural[$period.periodType]`}{/if}{math equation="a * b" a=$period.periodLength|default:0 b=$period.rebillCount|default:0 assign="x"}{if $x > 0} {t _for}  [[x]] {t `$periodTypesPlural[$period.periodType]`}, [[period.rebillCount]] {t _rebill_times}{/if})
				</option>
			{/foreach}
		</select>
	</div>
{/if}