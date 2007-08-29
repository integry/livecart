<div style="position: relative;">
	<div style="display: none;" class="activeGrid_loadIndicator" id="{$prefix}LoadIndicator_{$id}">
		<div>
			{t _loading}<span class="progressIndicator"></span>
		</div>
	</div>
</div>

<div>
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
							<option value="">{tn $column}</option>
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
                                        Show All
                                    </li>
                                    <li symbol="=">
                                        <span class="sign">=</span>
                                        Equals
                                    </li>
                                    <li symbol="<>">
                                        <span class="sign">&ne;</span>
                                        Does Not Equal
                                    </li>
                                    <li symbol=">">
                                        <span class="sign">&gt;</span>
                                        Greater Than
                                    </li>
                                    <li symbol="<">
                                        <span class="sign">&lt;</span>
                                        Less Than
                                    </li>
                                    <li symbol=">=">
                                        <span class="sign">&ge;</span>
                                        Greater Than or Equal To
                                    </li>
                                    <li symbol="<=">
                                        <span class="sign">&le;</span>
                                        Less Than or Equal To
                                    </li>
                                    <li symbol="><">
                                        <span class="sign">&gt;&lt;</span>
                                        Range
                                    </li>
                                </ul>
                            
                            </div>
                        
                        </div>

                        <input type="text" class="text {$type}" id="filter_{$column}_{$id}" value="{$availableColumns.$column.name|escape}" onkeyup="RegexFilter(this, {ldelim} regex : '[^=<>.0-9 ]' {rdelim});" />

                        <div class="rangeFilter" style="display: none;">
                            <input type="text" class="text numeric min" onclick="event.stopPropagation();" onchange="$('filter_{$column}_{$id}').filter.updateRangeFilter(event);" onkeyup="RegexFilter(this, {ldelim} regex : '[^.0-9]' {rdelim});" />
                            <span class="rangeTo">-</span>
                            <input type="text" class="text numeric max" onclick="event.stopPropagation();" onchange="$('filter_{$column}_{$id}').filter.updateRangeFilter(event);" onkeyup="RegexFilter(this, {ldelim} regex : '[^.0-9]' {rdelim});" />
                        </div>
        
        
					{* elseif 'date' == $type}

					   {calendar noform="true" class="text `$type`" id="filter_`$column`_`$id`" value=$availableColumns.$column.name|escape style="float: left;"}

                    *}
                    
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
	<a href="#" onclick="Element.show($('{$prefix}ColumnMenu_{$id}')); return false;">{t _columns}</a>
</div>

<div id="{$prefix}ColumnMenu_{$id}" class="activeGridColumnsRoot" style="display: none; position: relative;">
  <form action="{link controller=$controller action=changeColumns}" onsubmit="new LiveCart.AjaxUpdater(this, this.up('.{$container}'), document.getElementsByClassName('progressIndicator', this)[0]); return false;" method="POST">
	
	<input type="hidden" name="id" value="{$id}" />
	
	<div class="activeGridColumnsSelect">
		<div class="activeGridColumnsSelectControls">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" class="submit" name="sm" value="{tn Change columns}" /> {t _or} <a class="cancel" onclick="Element.hide($('{$prefix}ColumnMenu_{$id}')); return false;" href="#cancel">{t _cancel}</a>
		</div>
	    <div class="activeGridColumnsList">
			{foreach from=$availableColumns item=item key=column}
			<p>
				<input type="checkbox" name="col[{$column}]" class="checkbox" id="column_{$column}"{if $displayedColumns.$column}checked="checked"{/if} />
				<label for="column_{$column}" class="checkbox" id="column_{$column}_label">
					{$item.name}
				</label>
			</p>
			{/foreach}
		</div>
	</div>
  </form>
</div>

<script type="text/javascript">
	grid = new ActiveGrid($('{$prefix}_{$id}'), '{$url}{$filters}', {$totalCount}, $("{$prefix}LoadIndicator_{$id}"), {$rowCount});
	console.log(grid);
	{foreach from=$displayedColumns item=index key=column name="columns"}
		{if !$smarty.foreach.columns.first}
		    new ActiveGridFilter($('filter_{$column}_{$id}'), grid);
		{/if}
	{/foreach}
	
</script>