{foreach from=$lastViewed item=item}
	<li>
		{if $item.type == 'user'}
			<a href="{link controller=backend.userGroup query="rt=`$randomToken`"}#user_{$item.User.ID}__">
				<img src="image/silk/group.png" alt="" />
				{$item.User.firstName|escape} {$item.User.lastName|escape}
			</a>
		{/if}
		
		{if $item.type == 'product'}
			<a href="{link controller=backend.category query="rt=`$randomToken`"}#product_{$item.Product.ID}__">
				<img src="image/silk/package.png" alt="" />
				{$item.Product.name_lang|escape}
			</a>
		{/if}
		
		{if $item.type == 'order'}
			<a href="{link controller=backend.customerOrder query="rt=`$randomToken`"}#order_{$item.CustomerOrder.ID}#tabOrderInfo__">
				<img src="image/silk/money.png" alt="" />
				{$item.CustomerOrder.invoiceNumber|escape}
			</a>
		{/if}
	</li>
{/foreach}