{assign var="layouts" value='ENABLED_LIST_LAYOUTS'|config}
{if 'ALLOW_SWITCH_LAYOUT'|@config && ($layouts|@count > 1)}
	<div class="categoryLayoutSwitch btn-group">
		{if $layouts.LIST}
			<a class="btn btn-default layoutSetList {if $layout == 'LIST'}active{/if}" href="{$layoutUrl}list" title="{tn _view_as_list}"><span class="glyphicon glyphicon-list"></span></a>
		{/if}
		{if $layouts.GRID}
			<a class="btn btn-default layoutSetGrid {if $layout == 'GRID'}active{/if}" href="{$layoutUrl}grid" title="{tn _view_as_grid}"><span class="glyphicon glyphicon-th-large"></span></a>
		{/if}
		{if $layouts.TABLE}
			<a class="btn btn-default layoutSetTable {if $layout == 'TABLE'}active{/if}" href="{$layoutUrl}table" title="{tn _view_as_table}"><span class="glyphicon glyphicon-th-list"></span></a>
		{/if}
	</div>
{/if}
