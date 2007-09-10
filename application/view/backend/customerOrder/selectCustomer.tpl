{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree_start.js"}
{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="backend/User.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="library/ActiveList.css"}
{includeCss file="library/ActiveGrid.css"}

{includeCss file="backend/User.css"}
{includeCss file="backend/SelectCustomerPopup.css"}


{pageTitle help="userGroups"}{t _livecart_users}{/pageTitle}
{include file="layout/backend/meta.tpl"}


<a id="help" href="#" target="_blank" style="display: none;">Help</a>

<div id="userGroupsWrapper" class="maxHeight h--50">
	<div id="userGroupsBrowserWithControlls" class="treeContainer maxHeight">
        <div>
            <a class="menu" href="#" onclick="window.close(); return false;">
                {t _cancel}
            </a>
        </div>
    	<div id="userGroupsBrowser" class="treeBrowser"></div>        
        <ul id="userGroupsBrowserControls" class="verticalMenu">
            <li class="addTreeNode" style="display: none;"><a id="userGroups_add" href="#add">{t _add}</a></li>
            <li class="removeTreeNode" style="display: none;"><a id="userGroups_delete" href="#delete">{t _delete}</a></li>
        </ul>
        
        <div id="confirmations"></div>
        
        <div class="yellowMessage" id="orderCreatedConfirmation" style="display: none;"><div>{t _new_order_has_been_successfully_created}</div></div>
        <div class="redMessage" id="userHasNoAddressError" style="display: none;"><div>{t _err_user_has_no_billing_or_shipping_address}</div></div>
	</div>
    
    <span id="fromUsersPage">
        {include file="backend/userGroup/groupContainer.tpl"}
    </span>
</div>

{literal}
<script type="text/javascript">
    window.ordersActiveGrid = {};


    var users = new Backend.UserGroup({/literal}{json array=$userGroups}{literal});
    window.usersActiveGrid = {};
</script>
{/literal}

</body>
</html>