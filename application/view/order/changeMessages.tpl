{if $changes}
	{foreach from=$changes key=type item=items}
		<div style="clear: left;"></div>
		<div class="infoMessage message">
			{if $items|@count > 1}
				<div>{translate text="_order_auto_changes_`$type`"}:</div>
				<ul>
					{foreach from=$items item=item}
						<li>
							{$itemsById[$item.id].Product.name_lang}
							{if 'count' == $type}
								- {maketext text="_order_quantity_change" params="`$item.from`,`$item.to`"}
							{/if}
						</li>
					{/foreach}
				</ul>
			{else}
				{maketext text="_order_auto_changes_single_`$type`" params="`$itemsById[$items.0.id].Product.name_lang`,`$items.0.from`,`$items.0.to`"}
			{/if}
		</div>
	{/foreach}
{/if}
