<div style="position: relative;">
	<div style="display: none;" class="activeGrid_loadIndicator" id="{$prefix}LoadIndicator_{$id}">
		<div>
			{t _loading}<span class="progressIndicator"></span>
		</div>
	</div>

	<div class="activeGrid_massActionProgress" id="{$prefix}MassActionProgress_{$id}" style="display: none;">
		<fieldset class="container">
			<div class="progressBarIndicator"></div>
			<div class="progressBar">
				<span class="progressCount"></span>
				<span class="progressSeparator"> / </span>
				<span class="progressTotal"></span>
			</div>
			<a class="cancel" href="{link controller=$controller action=isMassCancelled}">{t _cancel}</a>
		</fieldset>
	</div>
</div>

{if $advancedSearch}
	<div id="{$prefix}_{$id}_AdvancedSearch">
		<a href="javascript:void(0);" class="cancel advancedSearchLink">
			{t _advanced_search}
		</a>
		<div id="{$prefix}_{$id}_QueryContainer" class="advancedSearchQueryContainer">
			<form class="advancedSearchForm" method="post" action=" ??? ">
				<ul class="advancedQueryItems">
				</ul>
			</form>
		</div>
	</div>
{/if}

<div class="activeGridContainer">

<div class="activeGridCellContent" style="display: none; position:absolute;"></div>

<div class="quickEditContainer"></div>
<table class="activeGrid {$prefix}List {denied role=$role}readonlyGrid{/denied}" id="{$prefix}_{$id}">

<thead>
	<tr class="headRow">

		<th class="cell_cb"><input type="checkbox" class="checkbox" /></th>

		{foreach from=$displayedColumns item=type key=column name="columns"}
			{if !$smarty.foreach.columns.first}
				<th class="first cellt_{$type} cell_{$column|replace:'.':'_'}">
					<div style="position: relative;">
					<span class="fieldName">{$column}</span>

					{if 'bool' == $type}

						<select id="filter_{$column}_{$id}">
							<option value="">{$availableColumns.$column.name|escape}</option>
							<option value="1">{tn _yes}</option>
							<option value="0">{tn _no}</option>
						</select>

					{elseif 'numeric' == $type}

						<div class="filterMenuContainer">

							{img src="image/silk/zoom.png" class="filterIcon" onclick="Event.stop(event);"}

							<div class="filterMenu">

								<ul onclick="$('filter_{$column}_{$id}').filter.initFilter(event);">
									<li class="rangeFilterReset" symbol="">
										<span class="sign">&nbsp;</span>
										<span class="signLabel">{t _grid_show_all}</span>
									</li>
									<li symbol="=">
										<span class="sign">=</span>
										<span class="signLabel">{t _grid_equals}</span>
									</li>
									<li symbol="<>">
										<span class="sign">&ne;</span>
										<span class="signLabel">{t _grid_not_equal}</span>
									</li>
									<li symbol=">">
										<span class="sign">&gt;</span>
										<span class="signLabel">{t _grid_greater}</span>
									</li>
									<li symbol="<">
										<span class="sign">&lt;</span>
										<span class="signLabel">{t _grid_less}</span>
									</li>
									<li symbol=">=">
										<span class="sign">&ge;</span>
										<span class="signLabel">{t _grid_greater_or_equal}</span>
									</li>
									<li symbol="<=">
										<span class="sign">&le;</span>
										<span class="signLabel">{t _grid_less_or_equal}</span>
									</li>
									<li symbol="><">
										<span class="sign">&#8812;</span>
										<span class="signLabel">{t _grid_range}</span>
									</li>
								</ul>

							</div>

						</div>

						<input type="text" class="text {$type}" id="filter_{$column}_{$id}" value="{$availableColumns.$column.name|escape}" onkeyup="RegexFilter(this, {ldelim} regex : '[^=<>.0-9]' {rdelim});" />

						<div class="rangeFilter" style="display: none;">
							<input type="text" class="text numeric min" onclick="event.stopPropagation();" onchange="$('filter_{$column}_{$id}').filter.updateRangeFilter(event);" onkeyup="RegexFilter(this, {ldelim} regex : '[^.0-9]' {rdelim});" />
							<span class="rangeTo">-</span>
							<input type="text" class="text numeric max" onclick="event.stopPropagation();" onchange="$('filter_{$column}_{$id}').filter.updateRangeFilter(event);" onkeyup="RegexFilter(this, {ldelim} regex : '[^.0-9]' {rdelim});" />
						</div>


					{elseif 'date' == $type}

						<select id="filter_{$column}_{$id}">
							<option value="">{$availableColumns.$column.name|escape}</option>
							<option value="today | now">{tn _today}</option>
							<option value="yesterday | today">{tn _yesterday}</option>
							<option value="-7 days | now">{tn _last_7_days}</option>
							<option value="{$thisMonth}/1 | now">{tn _this_month}</option>
							<option value="{$lastMonth}-1 | {$thisMonth}/1">{tn _last_month}</option>
							{* <option value="range">{tn _grid_date_range}</option> *}
						</select>

						<div style="display: none;">
							{calendar noform="true" class="text `$type`" id="filter_`$column`_`$id`" value=$availableColumns.$column.name|escape style="float: left;"}
						</div>

					{else}

					   <input type="text" class="text {$type}" id="filter_{$column}_{$id}" value="{$availableColumns.$column.name|escape}"  />

					{/if}

					{img src="image/silk/bullet_arrow_up.png" class="sortPreview" }

					</div>

				</th>
			{/if}
		{/foreach}
	</tr>
</thead>
<tbody>
	{section name="createRows" start=0 loop=$rowCount}
		<tr class="{if $smarty.section.createRows.index is even}even{else}odd{/if}">
			<td class="cell_cb"></td>
		{foreach from=$displayedColumns key=column item=type name="columns"}
		 	{if !$smarty.foreach.columns.first}
				<td class="cellt_{$type} cell_{$column|replace:'.':'_'}"></td>
			{/if}
		{/foreach}
		</tr>
	{/section}
</tbody>

	</table>
</div>

<div class="activeGridColumns" >
	<ul class="menu" style="float: left;">
		<li class="reload">
			<a href="#" onclick="window.activeGrids['{$prefix}_{$id}'].reloadGrid(); return false;">{t _grid_reload}</a>
		</li>
	</ul>

	<ul class="menu" style="float: right;">
		<li class="export">
			<a href="#" onclick="var grid = window.activeGrids['{$prefix}_{$id}']; window.location.href='{link controller=$controller action=export}?' + grid.ricoGrid.getQueryString() + '&selectedIDs=' + grid.getSelectedIDs().toJSON() + '&isInverse=' + (grid.isInverseSelection() ? 1 : 0); return false;">{t _grid_export}</a>
		</li>
		<li class="selectColumns">
		   <a href="#" onclick="Element.show($('{$prefix}ColumnMenu_{$id}')); return false;">{t _columns}</a>
		</li>
	</ul>

	<div class="clear"></div>
</div>

<div id="{$prefix}ColumnMenu_{$id}" class="activeGridColumnsRoot" style="display: none; position: relative;">
  <form action="{link controller=$controller action=changeColumns}" onsubmit="new LiveCart.AjaxUpdater(this, this.up('.{$container}'), document.getElementsByClassName('progressIndicator', this)[0]); return false;" method="post">

	<input type="hidden" name="id" value="{$id}" />

	<div class="activeGridColumnsSelect">
		<div class="activeGridColumnsSelectControls">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" class="submit" name="sm" value="{tn _change_columns}" /> {t _or} <a class="cancel" onclick="Element.hide($('{$prefix}ColumnMenu_{$id}')); return false;" href="#cancel">{t _cancel}</a>
		</div>
		<div class="activeGridColumnsList">
			{foreach from=$availableColumns item=item key=column}
			<p class="activeGridcolumn_{$column|replace:'.':'_'}">
				<input type="checkbox" name="col[{$column}]" class="checkbox" id="column_{$id}_{$column}"{if $displayedColumns.$column}checked="checked"{/if} />
				<label for="column_{$id}_{$column}" class="checkbox" id="column_{$id}_{$column}_label">
					{$item.name}
				</label>
			</p>
			{/foreach}
		</div>
	</div>
  </form>
</div>

{literal}
<script type="text/javascript">
	if(!window.activeGrids) window.activeGrids = {};
{/literal};
	window.activeGrids['{$prefix}_{$id}'] = new ActiveGrid($('{$prefix}_{$id}'), '{$url}', {$totalCount}, $("{$prefix}LoadIndicator_{$id}"), {$rowCount}, {json array=$filters});
	{if $dataFormatter}
		window.activeGrids['{$prefix}_{$id}'].setDataFormatter({$dataFormatter});
	{/if}
	window.activeGrids['{$prefix}_{$id}'].setInitialData({json array=$data});
	{if $isQuickEdit}
		window.activeGrids['{$prefix}_{$id}'].initQuickEdit("{$quickEditUrl}", "{$quickEditUrlIdentificatorToken}");
	{/if}
	{foreach from=$displayedColumns item=index key=column name="columns"}
		{if !$smarty.foreach.columns.first}
			new ActiveGridFilter($('filter_{$column}_{$id}'), window.activeGrids['{$prefix}_{$id}']);
		{/if}
	{/foreach}
	{if $advancedSearch}
		window.activeGrids['{$prefix}_{$id}'].initAdvancedSearch(
			"{$prefix}_{$id}",
			{json array=$availableColumns},

			/* misc properties */
			{literal}
			{
				dateFilterValues:
				{
			{/literal}
					_today:"today | now",
					_yesterday:"yesterday | today",
					_last_7_days:"-7 days | now",
					_this_month:"{$thisMonth}/1 | now",
					_last_month:"{$lastMonth}-1 | {$thisMonth}/1"
			{literal}
				}
			}
			{/literal}
		);
	{/if}

	// register translations
	$T("_yes","{t _yes}");
	$T("_no","{t _no}");
	$T("_grid_show_all","{t _grid_show_all}");
	$T("_grid_equals","{t _grid_equals}");
	$T("_grid_not_equal","{t _grid_not_equal}");
	$T("_grid_greater","{t _grid_greater}");
	$T("_grid_less","{t _grid_less}");
	$T("_grid_greater_or_equal","{t _grid_greater_or_equal}");
	$T("_grid_less_or_equal","{t _grid_less_or_equal}");
	$T("_grid_range","{t _grid_range}");
	$T("_today","{t _today}");
	$T("_yesterday","{t _yesterday}");
	$T("_last_7_days","{t _last_7_days}");
	$T("_this_month","{t _this_month}");
	$T("_last_month","{t _last_month}");
	$T("_grid_date_range","{t _grid_date_range}");

{literal}
</script>
{/literal}