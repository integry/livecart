<div id="leftSide">
	<div id="contentWrapperLeft"></div>

	{block LEFT_SIDE}

	{if 'CATEGORY_MENU_TYPE'|config == 'CAT_MENU_FLYOUT'}
		{block DYNAMIC_CATEGORIES}
	{else}
		{block CATEGORY_BOX}
	{/if}

	{block COMPARE}
	{block FILTER_BOX}
	{block INFORMATION}
	{block NEWSLETTER}
	{block QUICKNAV}

	<div class="clear"></div>
</div>