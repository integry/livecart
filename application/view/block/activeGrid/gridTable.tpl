<thead>
	<tr class="headRow">
		
        <th class="cell_cb"><input type="checkbox" class="checkbox" /></th>
		
        {foreach from=$displayedColumns item=type key=column name="columns"}
			{if !$smarty.foreach.columns.first}
				<th class="first cellt_{$type} cell_{$column|replace:'.':'_'}">
					<span class="fieldName">{$column}</span>
					
                    {if 'bool' == $type}
			    		
                        <select style="width: auto;" id="filter_{$column}_{$categoryID}">
							<option value="">{tn $column}</option>
							<option value="1">{tn _yes}</option>
							<option value="0">{tn _no}</option>
						</select>	
                        				
					{elseif 'numeric' == $type}
                                                
                        <div class="filterMenuContainer">
                        
                            <img src="image/silk/zoom.png" class="filterIcon" />
                            
                            <div class="filterMenu">
                                
                                <ul onclick="$('filter_{$column}_{$categoryID}').filter.initFilter(event);">
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

                        <input type="text" class="text {$type}" id="filter_{$column}_{$categoryID}" value="{$availableColumns.$column.name|escape}" onkeyup="RegexFilter(this, {ldelim} regex : '[^ =<>.0-9]' {rdelim});" />

                        <div class="rangeFilter" style="display: none;">
                            <input type="text" class="text numeric min" onclick="event.stopPropagation();" onchange="$('filter_{$column}_{$categoryID}').filter.updateRangeFilter(event);" />
                            <span class="rangeTo">-</span>
                            <input type="text" class="text numeric max" onclick="event.stopPropagation();" onchange="$('filter_{$column}_{$categoryID}').filter.updateRangeFilter(event);" />
                        </div>
        
					{else}

					   <input type="text" class="text {$type}" id="filter_{$column}_{$categoryID}" value="{$availableColumns.$column.name|escape}" style="float: left;" />

					{/if}
					
					<img src="image/silk/bullet_arrow_up.png" class="sortPreview" />
					
				</th>		
			{/if}
		{/foreach}
		
	</tr>
</thead>	
<tbody>
	{section name="createRows" start=0 loop=15}
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