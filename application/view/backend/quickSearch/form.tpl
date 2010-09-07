<div id="{$formid}Container">
	<form id="{$formid}Form" method="post" action="{link controller=backend.quickSearch action=search}" onsubmit="return false;">
		<input
			id="{$formid}Query"
			autocomplete="off"
			name="q"
			type="text"
			value=""
			style="width:300px;"
			class="text"
		/>
		<input type="hidden" value="" name="class" id="{$formid}Class" />
		<input type="hidden" value="" name="from" id="{$formid}From" />
		<input type="hidden" value="" name="to" id="{$formid}To" />
		<input type="hidden" value="" name="direction" id="{$formid}Direction" />
		<div id="{$formid}Result"></div>
	</form>
</div>

<script type="text/javascript">
	Backend.QuickSearch.createInstance("{$formid}", {literal}{{/literal}cn:"{$classNames}"{literal}}{/literal});
</script>
