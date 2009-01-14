<h1>{t _customers}</h1>

<div class="chartMenu" id="menu_customers">
	<div class="typeSelector">
		<a href="#" id="register_date">{t _registrations}</a> | <a id="countries" href="#">Countries</a>{* | <a id="number_items" href="#">Number of Items Sold</a> |
		<select class="moreTypes">
			<option value="">{t _more_reports}</option>
			<option value="avg_total">{t _avg_order_total}</option>
			<option value="avg_items">{t _avg_items_per_order}</option>
		</select> *}
	</div>

	{include file="backend/report/intervalSelect.tpl"}

	<div class="clear"></div>
</div>

{include file="backend/report/chart.tpl" activeMenu=$type width="100%"}