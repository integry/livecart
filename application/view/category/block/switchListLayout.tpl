{assign var="layouts" value='ENABLED_LIST_LAYOUTS'|config}
{if 'ALLOW_SWITCH_LAYOUT'|@config && ($layouts|@count > 1)}
	<div class="categoryLayoutSwitch">
		{if $layouts.LIST}
			<a class="layoutSetList {if $layout == 'LIST'}active{/if}" href="{$layoutUrl}list" title="{tn _view_as_list}"></a>
		{/if}
		{if $layouts.GRID}
			<a class="layoutSetGrid {if $layout == 'GRID'}active{/if}" href="{$layoutUrl}grid" title="{tn _view_as_grid}"></a>
		{/if}
		{if $layouts.TABLE}
			<a class="layoutSetTable {if $layout == 'TABLE'}active{/if}" href="{$layoutUrl}table" title="{tn _view_as_table}"></a>
		{/if}
	</div>
{/if}
