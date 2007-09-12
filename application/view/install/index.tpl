<h1>Verifying Environment and System Requirements</h1>

<p>
	Some problems were detected that need to be resolved before the installation may continue:
</p>

<dl class="requirements">
	{foreach from=$requirements key=req item=result}
		{if 1 != $result}
			<div class="{if 1 == $result}pass{else}fail{/if}">
				<dt>{translate text=$req}</dt>
				<dd>
				{if 1 == $result}
					<img src="image/silk/gif/tick.gif" />
				{else}
					{if 'checkWritePermissions' == $req}
						The following directories are not writable:
						<ul id="notWritable">
						{foreach from=$result item=dir}
							<li>{$dir}</li>
						{/foreach}
						</ul>		
						<p>
							<strong>Recommended action:</strong> using an FTP client, change the access permissions (chmod) for these directories to 777 (full write access).
						</p>				
					{else}
						<img src="image/silk/gif/delete.gif" />
						{if 0 == $result}
							<div class="reqError">
								{translate text="`$req`_error"}
							</div>
						{/if}
					{/if}
				{/if}
				</dd>
			</div>
		{/if}
	{/foreach}
</dl>

<p>
	Please reload this page when the issues listed above have been resolved.
</p>