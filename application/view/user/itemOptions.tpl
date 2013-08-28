{if $options}
	<ul class="itemOptions">
	{foreach from=$options item=option}
		<li>
			[[option.Choice.Option.name_lang]]:
			{if 0 == $option.Choice.Option.type}
				{t _option_yes}
			{elseif 1 == $option.Choice.Option.type}
				[[option.Choice.name_lang]]
			{elseif 3 == $option.Choice.Option.type}
				<a href="{link controller=order action=downloadOptionFile id=$item.ID query="option=`$option.Choice.Option.ID`"}">[[option.fileName]]</a>
				{if $option.small_url}
					<div class="optionImage">
						<a href="{static url=$option.large_url}" rel="lightbox"><img src="{static url=$option.small_url}" /></a>
					</div>
				{/if}
			{else}
				{$option.optionText|@htmlspecialchars}
			{/if}

			{if $option.priceDiff != 0}
				<span class="optionPrice">([[option.formattedPrice]])</span>
			{/if}
		</li>
	{/foreach}
	</ul>
{/if}
