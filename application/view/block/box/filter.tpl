<div class="box filters">
	<div class="title">
		<div>Filters</div>
	</div>

	<div class="content">
		{foreach from=$groups item=group}		
			<div class="group">{$group.name_lang}</div>
			<ul>
				{foreach from=$group.filters item=filter}
					<li> {$filter.name_lang}</li>
				{/foreach}									
			</ul>
		{/foreach}
	</div>
</div>