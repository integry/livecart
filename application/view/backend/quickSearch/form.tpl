<div id="[[formid]]Container">
	<form id="[[formid]]Form" method="post" action="[[ url("backend.quickSearch/search") ]]" onsubmit="return false;">
		<input
			id="[[formid]]Query"
			autocomplete="off"
			name="q"
			type="text"
			placeholder="{translate|escape:"html" text=$hint}"
			class="text quickSearchInput hasHint"
		/>
		<input type="hidden" value="[[limit]]" name="limit" />
		<input type="hidden" value="" name="class" id="[[formid]]Class" />
		<input type="hidden" value="" name="from" id="[[formid]]From" />
		<input type="hidden" value="" name="to" id="[[formid]]To" />
		<input type="hidden" value="" name="direction" id="[[formid]]Direction" />
		<input type="hidden" value='[[resultTemplates]]' name="resultTemplates" id="[[formid]]ResultTemplates" />

		<div id="[[formid]]ResultOuterContainer" class="QuickSearchResultOuterContainer" style="display: none;">
			<div id="[[formid]]Result" class="QuickSearchResult"></div>
		</div>
	</form>
</div>

<script type="text/javascript">
	Backend.QuickSearch.createInstance("[[formid]]", {cn:"[[classNames]]"});
</script>
