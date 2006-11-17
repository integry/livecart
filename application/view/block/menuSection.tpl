{includeJs file=backend/menu/menu.js}

<ul id="nav" tabIndex=1>
	<li style="background-image: url(image/backend/menu/menu_left.jpg);width: 13px; background-repeat: no-repeat; background-position: 0px 0px;padding: 0;height:48px;"></li>

	{foreach from=$items item=item}
	<li>
		<div>
			<div>
				<div>
					<a href="{link controller=$item.controller action=$item.action}">{t $item.title}</a>
					<ul>
						{foreach from=$item.items item=command}
						<li><a href="{link controller=$command.controller action=$command.action}">{t $command.title}</a></li>
						{/foreach}
					</ul>
				</div>
			</div>
		</div>
	</li>		
	{/foreach}

	<li style="background-image: url(image/backend/menu/menu_right.jpg);width: 13px; background-repeat: no-repeat; background-position: 0px 0px;"></li>	
</ul>