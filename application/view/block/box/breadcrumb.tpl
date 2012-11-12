{if ($breadCrumb|@count > 1) && ('SHOW_BREADCRUMB'|config)}
	<ul class="breadcrumb">

		{if 'SHOW_BREADCRUMB_CAPTION'|config}
		<div id="breadCrumbCaption">
			{t _you_are_here}:
		</div>
		{/if}

		{foreach from=$breadCrumb item="breadCrumbItem" name="breadCrumb"}
			<li class="{if $smarty.foreach.breadCrumb.first}first {/if}{if $smarty.foreach.breadCrumb.last}last active{/if}">
				{if !$smarty.foreach.breadCrumb.last}
					<a href="{$breadCrumbItem.url}">{$breadCrumbItem.title}</a>
					<span class="divider">{'BREADCRUMB_SEPARATOR'|config}</span>
				{else}
					{$breadCrumbItem.title}
				{/if}
			</li>
		{/foreach}
	</ul>
{/if}