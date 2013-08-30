<h1>{t _customers}</h1>

<div class="chartMenu" id="menu_customers">
	<div class="typeSelector">
		<a href="#" id="register_date">{t _registrations}</a> | <a id="top_cust" href="#">{t _top_customers}</a> | <a id="countries" href="#">{t _countries}</a>
	</div>

	[[ partial("backend/report/intervalSelect.tpl") ]]

	<div class="clear"></div>
</div>

[[ partial('backend/report/chart.tpl', ['activeMenu': type, 'width': "100%", 'format': fullName, 'template': "backend/report/userLink.tpl"]) ]]