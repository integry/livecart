{if ($breadCrumb|@count > 1) && ('SHOW_BREADCRUMB'|config)}
	<ul class="breadcrumb">
		<style>
			.breadcrumb > li:after {ldelim} content: " {'BREADCRUMB_SEPARATOR'|config} "; {rdelim}
		</style>

		{if 'SHOW_BREADCRUMB_CAPTION'|config}
		<span id="breadCrumbCaption">
			{t _you_are_here}:
		</span>
		{/if}

		{foreach from=$breadCrumb item="breadCrumbItem" name="breadCrumb"}
			<li class="{if $smarty.foreach.breadCrumb.first}first {/if}{if $smarty.foreach.breadCrumb.last}last active{/if}">
				{if !$smarty.foreach.breadCrumb.last}
					<a href="{$breadCrumbItem.url}">{$breadCrumbItem.title}</a>
				{else}
					{$breadCrumbItem.title}
				{/if}
			</li>
		{/foreach}
	</ul>
{/if}
