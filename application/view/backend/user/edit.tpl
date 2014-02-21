<dialog fullHeight=true class="" cancel="cancel()">
	<dialog-header>{{vals.firstName}}</dialog-header>
	<dialog-body>
		[[ form('', ['ng-submit': 'save()', 'ng-init': ';']) ]] >
			
			[[ checkbox('isEnabled', '_is_enabled') ]]
			
			<div ng-show="vals.image">
				[[ checkbox('photoReviewed', 'Profile photo reviewed') ]]
				<img ng-src="upload/user/{{ vals.image }}" />
				<a ng-click="vals.image = ''; vals.photoReviewed = 1;">Delete photo</a>
				<br /><br />
			</div>

			[[ selectfld('UserGroup', '_user_group', availableUserGroups) ]]

			[[ textfld('firstName', '_first_name') ]]

{#
			[[ textfld('lastName', '_last_name') ]]
			[[ textfld('companyName', '_company_name') ]]
#}

			[[ textfld('email', '_email', ['type': 'email']) ]]
			
			[[ textfld('password', '_password', ['type': 'password']) ]]
			
			<eav-fields config="eav"></eav-fields>
			
		</form>
	</dialog-body>
	<dialog-footer>
		<dialog-cancel>{t _cancel}</dialog-cancel>
		<submit tabform="main">{t _save_user}</submit>
	</dialog-footer>
</dialog>
