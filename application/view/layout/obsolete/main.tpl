{include file='layout/header.tpl'}
	<div id="menu" style="height: 50px; background-color: #fafafa">
		{$MENU.0}
	</div>
	<div id="page">
		<div id="nav">
		{foreach from=$NAV item=block}
			{$block}
		{/foreach}		
		</div>
		<div id="content">
			{$ACTION_VIEW}
		</div>
	</div>
{include file='layout/footer.tpl'}