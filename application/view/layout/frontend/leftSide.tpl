[[ global('layoutspanLeft', 3) ]]

<div class="col col-lg-[[ global('layoutspanLeft') ]]" id="leftSide">

	{block LEFT_SIDE}

	{if 'CATEGORY_MENU_TYPE'|config == 'CAT_MENU_FLYOUT'}
		{block DYNAMIC_CATEGORIES}
	{elseif 'CATEGORY_MENU_TYPE'|config == 'CAT_MENU_STANDARD'}
		{block CATEGORY_BOX}
	{/if}

	{block COMPARE}
	{block FILTER_BOX}
	{block INFORMATION}
	{block NEWSLETTER}
	{block QUICKNAV}
</div>
