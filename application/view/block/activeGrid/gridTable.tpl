<div style="width: 100%; position: relative;">
	<div style="display: none;" class="activeGrid_loadIndicator" id="{$prefix}LoadIndicator_{$id}">
		<div>
			{t _loading}<span class="progressIndicator"></span>
		</div>
	</div>
</div>

<div style="width: 100%;height: 100%;">
    <table class="activeGrid {$prefix}List {denied role=$role}readonlyGrid{/denied}" id="{$prefix}_{$id}" style="height: 100%;">

<thead>
	<tr class="headRow">
		
        <th class="cell_cb"><input type="checkbox" class="checkbox" /></th>
		
        {foreach from=$displayedColumns item=type key=column name="columns"}
			{if !$smarty.foreach.columns.first}
				<th class="first cellt_{$type} cell_{$column|replace:'.':'_'}">
					<div style="position: relative;">
                    <span class="fieldName">{$column}</span>
					
                    {if 'bool' == $type}
			    		
                        <select style="width: auto;" id="filter_{$column}_{$id}">
							<option value="">{tn $column}</option>
							<option value="1">{tn _yes}</option>
							<option value="0">{tn _no}</option>
						</select>	
                        				
					{elseif 'numeric' == $type}
                                                
                        <div class="filterMenuContainer">
                        
                            {img src="image/silk/zoom.png" class="filterIcon"}
                            
                            <div class="filterMenu">
                                
                                <ul onclick="$('filter_{$column}_{$id}').filter.initFilter(event);">
                                    <li symbol="">
                                        <span class="sign">&nbsp;</span>
                                        All
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

					   <input type="text" class="text {$type}" id="filter_{$column}_{$id}" value="{$availableColumns.$column.name|escape}" style="float: left;" />

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

<script type="text/javascript">
	grid = new ActiveGrid($('{$prefix}_{$id}'), '{$url}{$filters}', {$totalCount}, $("{$prefix}LoadIndicator_{$id}"), {$rowCount});
	console.log(grid);
	{foreach from=$displayedColumns item=index key=column name="columns"}
		{if !$smarty.foreach.columns.first}
		    new ActiveGridFilter($('filter_{$column}_{$id}'), grid);
		{/if}
	{/foreach}
	
</script>