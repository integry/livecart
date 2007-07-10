{if $filters}		
<div class="box expandResults">
	<div class="title">
		<div>{t _expand}</div>
	</div>

	<div class="content filterGroup">
		<h4>{t _remove_filter}:</h4>
		<ul>
		{foreach from=$filters item=filter}		
			<li><a href="{categoryUrl data=$category filters=$filters removeFilter=$filter}">{$filter.filterGroup.name_lang} {$filter.name_lang}</a></li>
		{/foreach}
		</ul>
	</div>
</div>
{/if}

{if $groups || $manGroup || $priceGroup}
<div class="box narrowResults">
	<div class="title">
		<div>{t _narrow_results}</div>
	</div>

	<div class="content">
		
        {if $manGroup}		
			<div class="filterGroup">
				<h4>{t _by_brand}</h4>
				<ul>
					{foreach from=$manGroup.filters item="filter"}
						<li> 
							<a href="{categoryUrl data=$category filters=$filters addFilter=$filter}">{$filter.name_lang}</a> 
        					{if 'DISPLAY_NUM_FILTER'|config}
							     <span class="count">({$filter.count})</span>
							{/if}							 
						</li>
					{/foreach}	
					
					{if $allManufacturers}
						<li class="showAll"><a href="{$allManufacturers}">{t _show_all_brands}</a></li>
					{/if}					
				</ul>
			</div>
		{/if}

		{if $priceGroup}		
			<div class="filterGroup">
				<h4>{t _by_price}</h4>
				<ul>
					{foreach from=$priceGroup.filters item="filter"}
						<li> 
							<a href="{categoryUrl data=$category filters=$filters addFilter=$filter}">{$filter.name_lang}</a> 
        					{if 'DISPLAY_NUM_FILTER'|config}
							    <span class="count">({$filter.count})</span>
							{/if}
						</li>
					{/foreach}									
				</ul>
			</div>
		{/if}
		
		{foreach from=$groups item="group"}		
			<div class="filterGroup">
				<h4>{$group.name_lang}</h4>
				<ul>
					{foreach from=$group.filters item="filter"}
						<li> 
							<a href="{categoryUrl data=$category filters=$filters addFilter=$filter}">{$filter.name_lang}</a> 
        					{if 'DISPLAY_NUM_FILTER'|config}
							     <span class="count">({$filter.count})</span>
							{/if}
						</li>
					{/foreach}				
					{if $group.more}
						<li class="showAll"><a href="{$group.more}">{t _show_all}</a></li>
					{/if}					
				</ul>
			</div>
		{/foreach}
	</div>
</div>
{/if}