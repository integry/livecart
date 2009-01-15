<script type="text/javascript">
	window.report.setActiveMenu('{$activeMenu}');
	window.report.setData({$chart});
	{if $chartType < 100}
		swfobject.embedSWF("javascript/library/openFlashChart/open-flash-chart.swf", "flashChart", "{$width|default:700}", "{$height|default:400}", "9.0.0");
	{/if}
</script>

{if ($chartType > 100) && $reportData}
	{include file="backend/report/table.tpl"}
{/if}

<div id="flashChart"></div>