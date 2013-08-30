<h1>{t _search}</h1>

<div class="chartMenu" id="menu_bestsellers">
	<div class="typeSelector">
		{* <a href="#" id="number_items">{t _num_items}</a> | <a id="total_items" href="#">{t _item_totals}</a> *}
	</div>

	[[ partial("backend/report/intervalSelect.tpl") ]]

	<div class="clear"></div>
</div>

[[ partial('backend/report/chart.tpl', ['activeMenu': type, 'width': "100%"]) ]]