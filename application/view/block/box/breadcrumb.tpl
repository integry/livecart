<div id="breadCrumb">
	<ul>			
	{foreach from=$breadCrumb item="item" name="breadCrumb"}		
		<li class="{if $smarty.foreach.breadCrumb.first}first {/if}{if $smarty.foreach.breadCrumb.last}last{/if}">
			{if !$smarty.foreach.breadCrumb.last}
				<a href="{$item.url}">{$item.title}</a> 
				<span class="separator">&gt;</span>
			{else}
				{$item.title}
			{/if}
		</li>	
	{/foreach}
	</ul>
</div>