{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="library/SectionExpander.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="library/ActiveList.css"}
{includeCss file="library/ActiveGrid.css"}

{includeCss file="backend/Backend.css"}

{includeJs file="backend/User.js"}
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
            <a id="userGroups_add" href="#add">{t _add}</a>
            <br />
            <a id="userGroups_delete" href="#delete">{t _delete}</a>
        </div>
	</div>
    
    {include file="backend/user/groupContainer.tpl"}
    {include file="backend/user/userContainer.tpl"}
</div>



<div id="activeUserPath"></div>



{literal}
<script type="text/javascript">
    Backend.UserGroup.prototype.Messages.confirmUserDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_user}{literal}';
    Backend.UserGroup.prototype.Messages.confirmUserGroupRemove = '{/literal}{t _are_you_sure_you_want_to_delete_this_user_group}{literal}';
    Backend.UserGroup.prototype.Messages.defaultUserName = '{/literal}{t _default_user}{literal}';
    Backend.UserGroup.prototype.Messages.youCanntoDeleteThisGroup = '{/literal}{t _you_cannot_delete_this_group}{literal}';
    Backend.User.Group.prototype.Messages.savedMessage = '{/literal}{t _form_has_been_successfully_saved}{literal}';

    Backend.User.Group.prototype.Links.save = '{/literal}{link controller=backend.userGroup action=save}{literal}';
    Backend.User.Group.prototype.Links.remove = '{/literal}{link controller=backend.userGroup action=delete}{literal}';
    Backend.User.Group.prototype.Links.createNewUserGroup = '{/literal}{link controller=backend.userGroup action=create}{literal}';
    Backend.User.Group.prototype.Links.removeNewGroup = '{/literal}{link controller=backend.userGroup action=remove}{literal}';
    Backend.User.Group.prototype.Links.create = '{/literal}{link controller=backend.userGroup action=create}{literal}';

    var users = new Backend.UserGroup({/literal}{json array=$userGroups}{literal});
    window.usersActiveGrid = {};
</script>
{/literal}


{include file="layout/backend/footer.tpl"}