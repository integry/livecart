{if ($breadCrumb|@count > 1) && ('SHOW_BREADCRUMB'|config)}
	<div id="breadCrumb">

		{if 'SHOW_BREADCRUMB_CAPTION'|config}
		<div id="breadCrumbCaption">
			{t _you_are_here}:
		</div>
		{/if}

		<ul>
		{foreach from=$breadCrumb item="breadCrumbItem" name="breadCrumb"}
			<li class="{if $smarty.foreach.breadCrumb.first}first {/if}{if $smarty.foreach.breadCrumb.last}last{/if}">
				{if !$smarty.foreach.breadCrumb.last}
					<a href="{$breadCrumbItem.url}">{$breadCrumbItem.title}</a>
					<span class="separator">{'BREADCRUMB_SEPARATOR'|config}</span>
				{else}
					{$breadCrumbItem.title}
				{/if}
			</li>
		{/foreach}
		</ul>
	</div>
	<div class="clear"></div>
{/if}