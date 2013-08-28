<h1>{t _sales}</h1>

<div class="chartMenu" id="menu_sales">
	<div class="typeSelector">
		<a href="#" id="number_orders">{t _num_orders}</a> | <a id="total_orders" href="#">{t _order_totals}</a> | <a id="number_items" href="#">{t _items_sold}</a> |
		<select class="moreTypes">
			<option value="">{t _more_reports}</option>
			<option value="avg_total">{t _avg_order_total}</option>
			<option value="avg_items">{t _avg_items_per_order}</option>
			<option value="payment_methods">{t _payment_methods}</option>
			<option value="currencies">{t _currencies}</option>
			<option value="status">{t _statuses}</option>
			<option value="cancelled">{t _cancelled_ratio}</option>
			<option value="unpaid">{t _unpaid_ratio}</option>
		</select>
	</div>

	[[ partial("backend/report/intervalSelect.tpl") ]]

	<div class="clear"></div>
</div>

{include file="backend/report/chart.tpl" activeMenu=$type width="100%"}