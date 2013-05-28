{assign var="layoutspanLeft" value=3 scope="global"}

<div class="col col-lg-{$layoutspanLeft}">

<div id="userMenuContainer">
	<ul id="userMenu" class="list-group">
		<li id="homeMenu" class="list-group-item {if "homeMenu" == $current}active{/if}"><a href="{link controller=user}"><span class="glyphicon glyphicon-home"></span> {t _acc_home}</a></li>
		<li id="orderMenu" class="list-group-item {if "orderMenu" == $current}active{/if}"><a href="{link controller=user action=orders}"><span class="glyphicon glyphicon-tags"></span> {t _orders}</a></li>

		{block INVOICES_MENU}

		<li id="fileMenu" class="list-group-item {if "fileMenu" == $current}active{/if}"><a href="{link controller=user action=files}"><span class="glyphicon glyphicon-hdd"></span> {t _downloads}</a></li>
		<li id="personalMenu" class="list-group-item {if "personalMenu" == $current}active{/if}"><a href="{link controller=user action=personal}"><span class="glyphicon glyphicon-pencil"></span> {t _personal_info}</a></li>
		<li id="addressMenu" class="list-group-item {if "addressMenu" == $current}active{/if}"><a href="{link controller=user action=addresses}"><span class="glyphicon glyphicon-road"></span> {t _addresses}</a></li>
		<li id="emailMenu" class="list-group-item {if "emailMenu" == $current}active{/if}"><a href="{link controller=user action=changeEmail}"><span class="glyphicon glyphicon-envelope"></span> {t _change_email_address}</a></li>
		<li id="passwordMenu" class="list-group-item {if "passwordMenu" == $current}active{/if}"><a href="{link controller=user action=changePassword}"><span class="glyphicon glyphicon-lock"></span> {t _change_pass}</a></li>

		{block USER_MENU_ITEMS}

		<li id="signOutMenu" class="list-group-item {if "signoutMenu" == $current}active{/if}"><a href="{link controller=user action=logout}"><span class="glyphicon glyphicon-arrow-right"></span> {t _sign_out}</a></li>
	</ul>
</div>

</div>