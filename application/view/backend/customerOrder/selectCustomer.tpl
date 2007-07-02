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
        <div style="margin-bottom: 5px; text-align: center;">
            <a class="menu" href="#" onclick="window.close(); return false;">
                {t _done_choosing_costumer}
            </a>
        </div>
        
        
    	<div id="userGroupsBrowser" class="treeBrowser"></div>
    
    	<div id="relError" style="width: auto;"></div>
    	<div id="relConf" class="yellowMessage" style="display: none; width: auto;"><div>{t _msg_added}</div></div>
	</div>
    <span id="fromUsersPage">
       <div id="activeUserPath">asdsda</div>
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