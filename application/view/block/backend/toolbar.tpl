
{$lastViewed|@ff}
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

		<li id="lastviewed" class="lastviewed"><a href="#" class="lastviewed">{t _last_viewed}</a>
			<div class="subpanel">
				<h3><span> &ndash; </span>{t _last_viewed}</h3>
				<ul>
					{* <li><span></span></li> *}
					<li><a href="#"><img src="image/silk/money.png" alt="" /> INT-12/32</a></li>
					<li><a href="#"><img src="image/silk/group.png" alt="" /> Foo Bar</a></li>
					<li><a href="#"><img src="image/silk/package.png" alt="" /> SKU-12345</a></li>
					<li><a href="#"><img src="image/silk/group.png" alt="" /> Jānis Kociņš</a></li>
					<li><a href="#"><img src="image/silk/money.png" alt="" /> INT-12/2</a></li>
					<li><a href="#"><img src="image/silk/package.png" alt="" /> SKU-Af3312</a></li>


					<li><a href="#"><img src="image/silk/money.png" alt="" /> INT-12/32</a></li>
					<li><a href="#"><img src="image/silk/group.png" alt="" /> Foo Bar</a></li>
					<li><a href="#"><img src="image/silk/package.png" alt="" /> SKU-12345</a></li>
					<li><a href="#"><img src="image/silk/group.png" alt="" /> Jānis Kociņš</a></li>
					<li><a href="#"><img src="image/silk/money.png" alt="" /> INT-12/2</a></li>
					<li><a href="#"><img src="image/silk/package.png" alt="" /> SKU-Af3312</a></li>
					<li><a href="#"><img src="image/silk/money.png" alt="" /> INT-12/32</a></li>
					<li><a href="#"><img src="image/silk/group.png" alt="" /> Foo Bar</a></li>
					<li><a href="#"><img src="image/silk/package.png" alt="" /> SKU-12345</a></li>
					<li><a href="#"><img src="image/silk/group.png" alt="" /> Jānis Kociņš</a></li>
					<li><a href="#"><img src="image/silk/money.png" alt="" /> INT-12/2</a></li>
					<li><a href="#"><img src="image/silk/package.png" alt="" /> SKU-Af3312</a></li>
					<li><a href="#"><img src="image/silk/money.png" alt="" /> INT-12/32</a></li>
					<li><a href="#"><img src="image/silk/group.png" alt="" /> Foo Bar</a></li>
					<li><a href="#"><img src="image/silk/package.png" alt="" /> SKU-12345</a></li>
					<li><a href="#"><img src="image/silk/group.png" alt="" /> Jānis Kociņš</a></li>
					<li><a href="#"><img src="image/silk/money.png" alt="" /> INT-12/2</a></li>
					<li><a href="#"><img src="image/silk/package.png" alt="" /> SKU-Af3312</a></li>
					<li><a href="#"><img src="image/silk/money.png" alt="" /> INT-12/32</a></li>
					<li><a href="#"><img src="image/silk/group.png" alt="" /> Foo Bar</a></li>
					<li><a href="#"><img src="image/silk/package.png" alt="" /> SKU-12345</a></li>
					<li><a href="#"><img src="image/silk/group.png" alt="" /> Jānis Kociņš</a></li>
					<li><a href="#"><img src="image/silk/money.png" alt="" /> INT-12/2</a></li>
					<li><a href="#"><img src="image/silk/package.png" alt="" /> SKU-Af3312</a></li>
					<li><a href="#"><img src="image/silk/money.png" alt="" /> INT-12/32</a></li>
					<li><a href="#"><img src="image/silk/group.png" alt="" /> Foo Bar</a></li>
					<li><a href="#"><img src="image/silk/package.png" alt="" /> SKU-12345</a></li>
					<li><a href="#"><img src="image/silk/group.png" alt="" /> Jānis Kociņš</a></li>
					<li><a href="#"><img src="image/silk/money.png" alt="" /> INT-12/2</a></li>
					<li><a href="#"><img src="image/silk/package.png" alt="" /> SKU-Af3312</a></li>
					<li><a href="#"><img src="image/silk/money.png" alt="" /> INT-12/32</a></li>
					<li><a href="#"><img src="image/silk/group.png" alt="" /> Foo Bar</a></li>
					<li><a href="#"><img src="image/silk/package.png" alt="" /> SKU-12345</a></li>
					<li><a href="#"><img src="image/silk/group.png" alt="" /> Jānis Kociņš</a></li>
					<li><a href="#"><img src="image/silk/money.png" alt="" /> INT-12/2</a></li>
					<li><a href="#"><img src="image/silk/package.png" alt="" /> SKU-Af3312</a></li>

				</ul>
			</div>
		</li>
	</ul>

	<li style="display:none;" class="uninitializedDropButton" id="dropButtonTemplate">
		<a onclick="return false;" href="">
			<small></small>
		</a>
	</li>

	<form style="display:none;" action="{link controller=backend.backendToolbar action=registerViewedItem}" method="post" id="backendToolbarForm">
		<input type="hidden" name="group" id="backendToolbarFormGroup" />
		<input type="hidden" name="id" id="backendToolbarFormID" />
		<input type="hidden" name="url" id="backendToolbarFormName" />
	</form>

	<div style="display:none;">
		<img src="image/silk/money.png" alt="" id="templateOrderIcon" />
		<img src="image/silk/group.png" alt="" id="templateUserIcon" />
		<img src="image/silk/package.png" alt="" id="templateProductIcon" />
	</div>

</div>


{literal}
<script type="text/javascript">
	footerToolbar = new BackendToolbar("footpanel", 
		{
			addIcon :  "{/literal}{link controller=backend.backendToolbar action=addIcon}?id=_id_&position=_position_{literal}",
			removeIcon: "{/literal}{link controller=backend.backendToolbar action=removeIcon}?id=_id_&position=_position_{literal}",
			sortIcons: "{/literal}{link controller=backend.backendToolbar action=sortIcons}?order=_order_{literal}",
		}
	);
</script>
{/literal}

