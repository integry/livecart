<h1>{t _conversion}</h1>

<div class="chartMenu" id="menu_conversion">
	<div class="typeSelector">
		<a href="#" id="ratio">{t _conversion_ratio}</a> | <a id="checkout" href="#">{t _checkout_steps}</a> | <a id="created" href="#">{t _carts_created}</a>
	</div>

	{include file="backend/report/intervalSelect.tpl"}

	<div class="clear"></div>
</div>

{include file="backend/report/chart.tpl" activeMenu=$type width="100%"}