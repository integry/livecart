{if "OFFLINE_LOGO_`$method`"|config}
	<p class="offlineMethodLogo">
		<img src="{"OFFLINE_LOGO_`$method`"|config}" />
	</p>
{/if}

{if "OFFLINE_DESCR_`$method`"|config}
	<p class="offlineMethodDescr">
		{"OFFLINE_DESCR_`$method`"|config}
	</p>
{/if}

{if "OFFLINE_INSTR_`$method`"|config}
	<p class="offlineMethodInstr">
		{"OFFLINE_INSTR_`$method`"|config}
	</p>
{/if}
