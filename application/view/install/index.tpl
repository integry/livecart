<dl class="requirements">
	{foreach from=$requirements key=req item=result}
		<div class="{if 1 == $result}pass{else}fail{/if}">
			<dt>{translate text=$req}</dt>
			{if 1 == $result}
				<img src="image/silk/gif/tick.gif" />
			{else}
				<img src="image/silk/gif/delete.gif" />
				{if 0 == $result}
					<div class="reqError">
						{translate text="`$req`_error"}
					</div>
				{/if}
			{/if}
		</div>
	{/foreach}
</dl>