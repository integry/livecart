<h3>
	<a href="{link controller=user action=viewOrder id=$order.ID}">[[order.formatted_dateCompleted.date_long]]</a>
</h3>

{% if $order.unreadMessageCount %}
	<p class="messages">
		<a href="{link controller=user action=viewOrder id=$order.ID}#msg" class="messages">
			{maketext text="_unread_messages" params=$order.unreadMessageCount}
		</a>
	</p>
{% endif %}

<div class="orderStatus">
	{t _status}:
	[[ partial('user/orderStatus.tpl', ['order': order]) ]]
</div>

<div class="orderDetails">

   <div class="orderMenu">

		<ul>
			<li><a href="{link controller=user action=viewOrder id=$order.ID}" class="viewOrder">{t _view_details}</a></li>
			{% if !$order.isCancelled && !'DISABLE_INVOICES'|config %}
				<li><a href="{link controller=user action=orderInvoice id=$order.ID}" class="invoice">{t _order_invoice}</a></li>
			{% endif %}
			<li><a href="{link controller=user action=reorder id=$order.ID}" class="reorder">{t _reorder}</a></li>
		</ul>

	   <div class="orderID">
		   {t _order_id}: [[order.invoiceNumber]]
	   </div>

	   {% if $order.ShippingAddress %}
		   <div class="orderRecipient">
			   {t _recipient}: [[order.ShippingAddress.fullName]]
		   </div>
	   {% endif %}

	   <div class="orderTotal">
		   {t _total}: <strong>{$order.formattedTotal[$order.Currency.ID]}</strong>
	   </div>

   </div>

   <div class="orderContent">

		<ul>
		{foreach from=$order.cartItems item="item"}
			<li>[[item.count]] x
				{% if $item.Product.isDownloadable %}
					<a href="{link controller=user action=item id=$item.ID}">[[item.Product.name_lang]]</a>
				{% else %}
					[[item.Product.name_lang]]
				{% endif %}

				{sect}
					{header}
						<ul>
					{/header}
					{content}
						{foreach $item.subItems as $subItem}
							{% if $subItem.Product.isDownloadable %}
								<li><a href="{link controller=user action=item id=$subItem.ID}">[[subItem.Product.name_lang]]</a></li>
							{% endif %}
						{/foreach}
					{/content}
					{footer}
						</ul>
					{/footer}
				{/sect}
			</li>
		{/foreach}
		</ul>
	</div>

</div>