{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/TabControl.js"}

{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}

{includeJs file="library/ActiveGrid.js"}
{includeJs file="backend/User.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="library/ActiveList.css"}
{includeCss file="library/ActiveGrid.css"}
{includeCss file="backend/User.css"}

{pageTitle help="userGroups"}{t _livecart_delivery_users}{/pageTitle}
{include file="layout/backend/header.tpl"}

<script type="text/javascript">
    Backend.UserGroup.userGroups = {$userGroups};
</script>

<div id="userGroupsWrapper" class="maxHeight h--50">
	<div id="userGroupsBrowserWithControlls">
    	<div id="userGroupsBrowser" class="treeBrowser"></div>
        <div id="userGroupsBrowserControls">
            <a id="userGroups_delete" href="#add">{t _add}</a>
            <br />
            <a id="userGroups_delete" href="#delete">{t _delete}</a>
        </div>
	</div>
    
    <div id="userGroupsManagerContainer" class="managerContainer">
    	<div class="tabContainer">
    		<ul class="tabList tabs">
    			<li id="tabUserGroup" class="tab active">
    				<a href="{link controller=backend.userGroup action=index}?id=_id_">{t _user_group}</a>
    				<span class="tabHelp">userGroup.index</span>
    			</li>
    			
    			<li id="tabUsers" class="tab inactive">
    				<a href="{link controller=backend.user action=users}?id=_id_">{t _group_users}</a>
    				<span class="tabHelp">userGroups.shippingRates</span>
    			</li>
			</ul>
    	</div>
    	<div class="sectionContainer maxHeight h--50"></div>
    </div>
</div>

<div id="activeUserPath"></div>

{literal}
<script type="text/javascript">
    Backend.UserGroup.prototype.Messages.confirmUserDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_user}{literal}';
    Backend.UserGroup.prototype.Messages.defaultUserName = '{/literal}{t _default_user}{literal}';
    Backend.User.Group.prototype.Messages.savedMessage = '{/literal}{t _form_has_been_successfully_saved}{literal}';

    Backend.User.Group.prototype.Links.save = '{/literal}{link controller=backend.userGroup action=save}{literal}';
    Backend.User.Group.prototype.Links.remove = '{/literal}{link controller=backend.userGroup action=delete}{literal}';

    var users = new Backend.UserGroup({/literal}{json array=$userGroups}{literal});
</script>
{/literal}

{include file="layout/backend/footer.tpl"}