<dialog>
	<dialog-header>{t _add_category}</dialog-header>
	[[ form('', ['ng-submit': "submit(form)", "ng-init": ";"]) ]] >
		<dialog-body>
			[[ textfld('name', '_category_name') ]]
		</dialog-body>
		<dialog-footer>
			<dialog-cancel>{t _cancel}</dialog-cancel>
			<submit>{t _add_category}</submit>
		</dialog-footer>
	</form>
</dialog>
