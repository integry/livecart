<div id="userMenuContainer" style="border-bottom: 2px solid #DDDDDD; padding: 6px">

<ul id="userMenu">
   <li id="homeMenu" class="{if "homeMenu" == $current}selected{/if}"><a href="{link controller=user}">{t Your Account Home}</a></li>
{*   <li id="ordersMenu">Orders</li> *}
   <li id="addressMenu" class="{if "addressMenu" == $current}selected{/if}"><a href="{link controller=user action=addresses}">{t Addresses}</li>
   <li id="emailMenu" class="{if "emailMenu" == $current}selected{/if}"><a href="{link controller=user action=changeEmail}">{t Change E-mail}</a></li>
   <li id="passwordMenu" class="{if "passwordMenu" == $current}selected{/if}"><a href="{link controller=user action=changePassword}">{t Change Password}</a></li>
   <li id="signOutMenu" class="{if "signoutMenu" == $current}selected{/if}"><a href="{link controller=user action=logout}">{t Sign Out}</a></li>
</ul>
<div class="clear"></div>

</div>