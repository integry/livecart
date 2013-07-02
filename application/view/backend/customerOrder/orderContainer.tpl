<div id="orderManagerContainer" style="display: none;">
	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="tabOrderInfo" class="tab active">
				<a href="{link controller="backend.customerOrder" action=info id=_id_}"}">{t _order_info}</a>
				<span class="tabHelp">orders.edit</span>
			</li>
			<li id="tabOrderInvoices" class="tab active" style="display:none;">
				<a href="{link controller="backend.customerOrder" action=invoices id="_id_"}"}">{t _invoices}</a>
				<span class="tabHelp">orders.edit</span>
			</li>
			<li id="tabOrderPayments" class="tab active">
				<a href="{link controller="backend.payment" id=_id_}"}">{t _order_payments}</a>
				<span class="tabHelp">orders.edit</span>
			</li>
			<li id="tabOrderCommunication" class="tab active">
				<a href="{link controller="backend.orderNote" id=_id_}"}">{t _order_communication}</a>
				<span class="tabHelp">orders.edit</span>
			</li>
			<li id="tabOrderLog" class="tab active">
				<a href="{link controller="backend.orderLog" id=_id_}"}">{t _order_log}</a>
				<span class="tabHelp">orders.edit</span>
			</li>
			<li id="tabPreviousOrders" class="tab active">
				<a href="{link controller="backend.customerOrder" action=orders query="userOrderID=_id_"}"}">{t _previous_orders}</a>
				<span class="tabHelp">orders.edit</span>
			</li>
		</ul>
	</div>
	<div class="sectionContainer"></div>
</div>