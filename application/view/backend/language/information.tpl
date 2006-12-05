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
				{if $key == '$locale->GetLanguages()' && !file_exists(implode('', array("image/localeflag/", $key2, ".png")))}

				<tr>
					<td>	
						{$key2}					
					</td>
					<td>
						{if $key == '$locale->GetLanguages()'}
							<img src="/lcart/public/image/unverified_flags/{$key2}.png">

							<a href="http://en.wikipedia.org/wiki/{$item2}_language" target="blank_">{$item2}</a>
						{else}
							{$item2}
						{/if}
					</td>
				</tr>
				
				{/if}
				
			{/foreach}					
			</table>		
		</td>		
		{/foreach}	
	</tr>
</table>

