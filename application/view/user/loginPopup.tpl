<dialog class="dialog-login dialog-signin">
	<dialog-header>{t _login}</dialog-header>
	<dialog-body>
		[[ form("", ["method": "POST", "ng-submit": "doLogin()", "ng-init": ";"]) ]]>

			[[ textfld('email', '_your_email', ['type': 'email', 'placeholder': t('_your_email')]) ]]

			[[ pwdfld('password', '_password', ['placeholder': t('_password')]) ]]
			<div ng-show="!passwordSent">
			<p class="smaller">
				<a ng-click="remindPassword()">{t _remind_pass}</a>
			</p>
			</div>

			<div class="alert alert-danger" ng-show="error">{t _login_failed}</div>
			
			<div class="alert alert-success" ng-show="success">{t _login_success}</div>
			
			<p>
				<submit class="btn btn-success">{t _login}</submit>
			</p>
			
			<div ng-show="passwordSent">
				<div class="alert alert-success">
					{t _password_emailed}
					<div><strong>
						{t _pass_security_warning}
					</strong></div>
				</div>
			</div>
			
			<hr />
			
			<a class="btn btn-primary register" ng-click="register(ret, noclose)">{t _register}</a>

		</form>
	</dialog-body>
</dialog>
