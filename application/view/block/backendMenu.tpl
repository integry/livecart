<ul id="nav">
	{foreach from=$items item=item name=menu}
	<li{if $itemIndex == $smarty.foreach.menu.iteration} id="navSelected"{/if}> 
		<div>
			<div>
				<div>
					<a href="{link controller=$item.controller action=$item.action}">{t $item.title notranslate=true}</a>
					{if count($item.items) > 0}
						<ul>
							{foreach from=$item.items item=command}
								<li><a href="{link controller=$command.controller action=$command.action}">{t $command.title notranslate=true}</a></li>
							{/foreach}
						</ul>
					{/if}
				</div>
			</div>
		</div>
	</li>		
	{/foreach}
</ul>