<div id="userMenuContainer">
    <ul id="userMenu">
       <li id="homeMenu" class="{if "homeMenu" == $current}selected{/if}"><a href="{link controller=user}">{t Your Account Home}</a></li>

       <li id="orderMenu" class="{if "orderMenu" == $current}selected{/if}"><a href="{link controller=user action=orders}">{t Orders}</li>
       <li id="fileMenu" class="{if "fileMenu" == $current}selected{/if}"><a href="{link controller=user action=files}">{t Downloads}</li>

       <li id="addressMenu" class="{if "addressMenu" == $current}selected{/if}"><a href="{link controller=user action=addresses}">{t Addresses}</li>
       <li id="emailMenu" class="{if "emailMenu" == $current}selected{/if}"><a href="{link controller=user action=changeEmail}">{t Change E-mail}</a></li>
       <li id="passwordMenu" class="{if "passwordMenu" == $current}selected{/if}"><a href="{link controller=user action=changePassword}">{t Change Password}</a></li>

       <li id="signOutMenu" class="{if "signoutMenu" == $current}selected{/if}"><a href="{link controller=user action=logout}">{t Sign Out}</a></li>
    </ul>
</div>