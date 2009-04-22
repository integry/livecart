{capture assign="hlp"}{translate text="`$key`_help" eval=true noDefault=true}{/capture}
{if $hlp}
	<div class="sectionHelp">
		{$hlp}
	</div>
{/if}