<div id="footpanel">
	<ul id="mainpanel">
		<li><a href="{link controller=index}" class="storeFrontend" target="_blank">{t _store_frontend}{*<small>{t _store_frontend}</small>*}</a></li>
		<li><a href="{link controller=backend.index}" class="storeBackend">{t _admin_dashboard}{*<small>{t _admin_dashboard}</small>*}</a></li>

		{foreach from=$dropButtons item=item}
			<li class="uninitializedDropButton" style="" id="button{$item.menuID}">
				<a onclick="return false;" href="">
					<small></small>
				</a>
			</li>
		{/foreach}

	{*
		<li><a href="#" class="editprofile">Edit Profile <small>Edit Profile</small></a></li>
		<li><a href="#" class="contacts">Contacts <small>Contacts</small></a></li>
		<li><a href="#" class="messages">Messages (10) <small>Messages</small></a></li>
		<li><a href="#" class="playlist">Play List <small>Play List</small></a></li>
		<li><a href="#" class="videos">Videos <small>Videos</small></a></li>
		<li id="alertpanel"><a href="#" class="alerts">Alerts</a></li>
		<li id="chatpanel"><a href="#" class="chat">Friends (<strong>18</strong>)</a></li>
	*}
	</ul>
</div>

<li class="uninitializedDropButton" style="display:none" id="dropButtonTemplate">
	<a onclick="return false;" href="">
		<small></small>
	</a>
</li>



{*json array=$dropButtons*}

{literal}
<script type="text/javascript">
	btb = new BackendToolbar("footpanel", 
	{
		addIcon :  "{/literal}{link controller=backend.backendToolbar action=addIcon}?id=_id_&position=_position_{literal}",
		removeIcon: "{/literal}{link controller=backend.backendToolbar action=removeIcon}?id=_id_&position=_position_{literal}",
		sortIcons: "{/literal}{link controller=backend.backendToolbar action=sortIcons}?order=_order_{literal}"
	}
	);
</script>
{/literal}