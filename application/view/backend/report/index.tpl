{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="backend/Report.js"}
{includeJs file="library/openFlashChart/js/swfobject.js"}

{includeCss file="backend/Report.css"}

{pageTitle help="content.pages"}{t _reports}{/pageTitle}
[[ partial("layout/backend/header.tpl") ]]

<div id="staticPageContainer">
	<div class="treeContainer">
		<ul class="verticalMenu" id="reportTypeSelector">
			<li id="menuSales">
				<a href="{link controller="backend.report" action=sales}">{t _sales}</a>
			</li>
			<li id="menuBest">
				<a href="{link controller="backend.report" action=bestsellers}">{t _bestsellers}</a>
			</li>
			<li id="menuCarts">
				<a href="{link controller="backend.report" action=conversion}">{t _conversion}</a>
			</li>
			<li id="menuCustomers">
				<a href="{link controller="backend.report" action=customers}">{t _customers}</a>
			</li>
			<li id="menuSearch">
				<a href="{link controller="backend.report" action=search}">{t _search}</a>
			</li>
			{*
			<li id="menuPages">
				<a href="{link controller="backend.report" action=pages}">{t _pages}</a>
			</li>
			*}
		</ul>

	</div>

	<div class="treeManagerContainer maxHeight h--100">
		<div id="reportDateRange">
			Report period:
			{renderBlock block="backend.components/DATE_RANGE_SELECTOR" class="reportDateSelector"}
			<select class="reportDateSelector">
				<option value="-30 days | now">{maketext text=_last_days params=30}</option>
				<option value="-24 hours | now">{maketext text=_last_hours params=24}</option>
				<option value="today | now">{tn _today}</option>
				<option value="yesterday | today">{tn _yesterday}</option>
				<option value="-7 days | now">{tn _last_7_days}</option>
				<option value="[[thisMonth]]/1 | now">{tn _this_month}</option>
				<option value="[[lastMonth]]-1 | [[thisMonth]]/1">{tn _last_month}</option>
				<option value="January 1 | now">{t _this_year}</option>
				<option value="all">{t _all_time}</option>
				{* <option value="range">{tn _grid_date_range}</option> *}
			</select>
		</div>

		<div id="reportIndicator" style="display: none;">
			<span class="progressIndicator"></span> Loading report
		</div>

		<div id="reportContent" class="maxHeight">

		</div>
	</div>
</div>

<script type="text/javascript">
	window.report = new Backend.Report.Controller();
</script>

[[ partial("layout/backend/footer.tpl") ]]