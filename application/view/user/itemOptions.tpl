{if $options}
	<ul class="itemOptions">
	{foreach from=$options item=option}
		<li>
			{$option.Choice.Option.name_lang}:
			{if 0 == $option.Choice.Option.type}
				{t _option_yes}
			{elseif 1 == $option.Choice.Option.type}
				{$option.Choice.name_lang}
			{else}
				{$option.optionText|@htmlspecialchars}
			{/if}

			{if $option.priceDiff != 0}
				<span class="optionPrice">({$option.formattedPrice})</span>
			{/if}
		</li>
	{/foreach}
	</ul>
{/if}
