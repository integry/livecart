<div style="right:0; position:absolute; width:320px;" id="QuickSearchContainer">
	<form id="QuickSearchForm" method="post" action="{link controller=backend.quickSearch action=search}" onsubmit="return false;">
		<input
			id="QuickSearchQuery"
			autocomplete="off"
			name="q"
			type="text"
			value=""
			onkeyup="Backend.QuickSearch.onKeyUp(this);"
			style="width:300px;"
			class="text"
		/>

		<input type="hidden" value="" name="class" id="QuickSearchClass" />
		<input type="hidden" value="" name="from" id="QuickSearchFrom" />
		<input type="hidden" value="" name="to" id="QuickSearchTo" />
		<input type="hidden" value="" name="direction" id="QuickSearchDirection" />

		<div id="QuickSearchResultOuterContainer" style="position:absolute; left:0; top:38px; z-index:2; display: none;">
			<div id="QuickSearchResult"></div>
		</div>
	</form>
</div>
