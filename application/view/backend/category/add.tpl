<dialog cancel="cancel()">
	{form model="category" ng_submit="submit(form)" handle=$form}
		<dialog-header>{t _add_category}</dialog-header>
		<dialog-body>
			{input name="name"}
				{label}{t _category_name}{/label}
				{textfield}
			{/input}
		</dialog-body>
		<dialog-footer>
			<dialog-cancel>{t _cancel}</dialog-cancel>
			<submit>{t _add_category}</submit>
		</dialog-footer>
	{/form}
</dialog>