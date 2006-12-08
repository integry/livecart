<ul id="nav">
	{foreach from=$items item=item name=menu}
	<li{if $itemIndex == $smarty.foreach.menu.iteration} id="navSelected"{/if}> 
		<div>
			<div>
				<div>
					<a href="{link controller=$item.controller action=$item.action}">{t $item.title notranslate=true}</a>
					{if count($item.items) > 0}
						<ul>
							{foreach from=$item.items item=command name=submenu}
								<li{if ($subItemIndex == $smarty.foreach.submenu.iteration) && ($itemIndex == $smarty.foreach.menu.iteration)} id="navSubSelected"{/if}>
									<div><div><div>
										<a href="{link controller=$command.controller action=$command.action}">{t $command.title notranslate=true}</a>
									</div></div></div>
								</li>
							{/foreach}
						</ul>
					{/if}
				</div>
			</div>
		</div>
	</li>		
	{/foreach}
</ul>
!!!{$subItemIndex} !!!{$itemIndex}