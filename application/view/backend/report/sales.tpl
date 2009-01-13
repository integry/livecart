<h1>{t _sales}</h1>

<div class="chartMenu" id="menu_sales">
	<div class="typeSelector">
		<a href="#" id="number_orders">Number of Orders</a> | <a id="total_orders" href="#">Order Totals</a> | <a id="number_items" href="#">Number of Items Sold</a> |
		<select class="moreTypes">
			<option value="">{t _more_reports}</option>
			<option value="avg_total">{t _avg_order_total}</option>
			<option value="avg_items">{t _avg_items_per_order}</option>
		</select>
	</div>
	<div class="intervalSelector">
		<span>{t _interval}:</span>
		<select class="intervalSelect">
			<option value="day">{t _daily}</option>
			<option value="month">{t _monthly}</option>
			<option value="year">{t _yearly}</option>
			<option value="hour">{t _hourly}</option>
			<option value="week">{t _weekly}</option>
		</select>
	</div>
	<div class="clear"></div>
</div>

{include file="backend/report/chart.tpl" activeMenu=$type width="100%"}