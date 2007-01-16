<div class="box expandResults">
	<div class="title">
		<div>Expand Results</div>
	</div>

	<div class="content">
		<ul>
		{foreach from=$filters item=filter}		
			<li><a href="{categoryUrl data=$category filters=$filters removeFilter=$filter}">Remove {$filter.FilterGroup.name_lang} {$filter.name_lang}</a></li>
		{/foreach}
		</ul>
	</div>
</div>

<div class="box narrowResults">
	<div class="title">
		<div>Narrow Results</div>
	</div>

	<div class="content">
		{foreach from=$groups item=group}		
			<div class="group">{$group.name_lang}</div>
			<ul>
				{foreach from=$group.filters item=filter}
					<li> <a href="{categoryUrl data=$category filters=$filters addFilter=$filter}">{$filter.name_lang}</a></li>
				{/foreach}									
			</ul>
		{/foreach}
	</div>
</div>