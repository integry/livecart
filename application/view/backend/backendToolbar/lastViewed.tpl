{if $itemsByType.order && $itemsByType.order|@count}
	{assign var="something" value=true}
	<li><span>{t _orders}</span></li>
	{foreach $itemsByType.order as $item}
		<li>
			<a href="{link controller="backend.customerOrder query="rt=`$randomToken`"}#order_{$item.CustomerOrder.ID}#tabOrderInfo__" onclick="return footerToolbar.tryToOpenItemWithoutReload({$item.CustomerOrder.ID}, 'order');">
				<img src="image/silk/money.png" alt="" />
				{$item.CustomerOrder.invoiceNumber|escape}
			</a>
		</li>
	{/foreach}
{/if}

{if $itemsByType.product && $itemsByType.product|@count}
	{assign var="something" value=true}
	<li><span>{t _products}</span></li>
	{foreach $itemsByType.product as $item}
		<li>
			<a href="{link controller="backend.category query="rt=`$randomToken`"}#product_{$item.Product.ID}__" onclick="return footerToolbar.tryToOpenItemWithoutReload({$item.Product.ID}, 'product');">
				<img src="image/silk/package.png" alt="" />
				{$item.Product.name_lang|escape}
			</a>

		</li>
	{/foreach}
{/if}

{if $itemsByType.user && $itemsByType.user|@count}
	{assign var="something" value=true}
	<li><span>{t _users}</span></li>
	{foreach $itemsByType.user as $item}
		<li>

			<a href="{link controller="backend.userGroup query="rt=`$randomToken`"}#user_{$item.User.ID}__" onclick="return footerToolbar.tryToOpenItemWithoutReload({$item.User.ID}, 'user');">
				<img src="image/silk/group.png" alt="" />
				{$item.User.firstName|escape} {$item.User.lastName|escape}
			</a>
		</li>
	{/foreach}
{/if}

{if !$something}
	<li><span>{t _history_is_empty}</span></li>
{/if}