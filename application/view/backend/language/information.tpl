<table>		
	<tr>
		<td>
		{foreach from=$lang item=item key=key}	
			<a href="{link controller=backend.language id=$key}">{$key}</a>
		{/foreach}
		</td>
	</tr>
</table>
<table>
	<tr valign="top">
		{foreach from=$masyvas item=item key=key}	
		<td>		
			<table border=1>
				<tr>
					<td colspan=2>
						<b>{$key}</b>
					</td>
				</tr>
				{foreach from=$item item=item2 key=key2}	
				<tr>
					<td>	
						{$key2}					
					</td>
					<td>
						{$item2}
					</td>
				</tr>
			{/foreach}					
			</table>		
		</td>		
		{/foreach}	
	</tr>
</table>

