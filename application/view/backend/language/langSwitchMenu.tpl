<div id="langMenu">
	<div>
		{foreach from=$languages item=item}
		<div onMouseOver="this.addClassName('langMenuHover');" onMouseOut="this.removeClassName('langMenuHover');" onClick="window.location.href = '{link controller=backend.language action=changeLanguage id=$item.ID query="returnRoute=$returnRoute"}'">
			<img src="image/localeflag/{$item.ID}.png" />
			<span class="enabled_{$item.isEnabled}">{$item.name}</span> 
		</div>
		{/foreach}
	</div>
	
	<a href="#" onClick="showLangMenu(false); return false;">{t _cancel}</a>
</div>