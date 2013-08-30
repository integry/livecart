{includeJs file="library/ActiveList.js"}
{includeJs file="backend/Currency.js"}
{includeJs file="backend/Tax.js"}

{includeJs file="library/form/State.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="library/form/Validator.js"}

{includeJs file="backend/TaxClass.js"}
{includeCss file="backend/TaxClass.css"}

{includeCss file="backend/Tax.css"}
{includeCss file="library/ActiveList.css"}
{includeCss file="library/TabControl.css"}

{pageTitle help="settings.taxes"}{t _taxes}{/pageTitle}
[[ partial("layout/backend/header.tpl") ]]

<script type="text/javascript">
	Backend.Tax.prototype.Links.update = "[[ url("backend.tax/update") ]]";
	Backend.Tax.prototype.Links.create = "[[ url("backend.tax/create") ]]";
	Backend.Tax.prototype.Links.edit = "[[ url("backend.tax/edit") ]]";
	Backend.Tax.prototype.Links.remove = "[[ url("backend.tax/delete") ]]";
	Backend.Tax.prototype.Links.sort = "[[ url("backend.tax/sort") ]]";
	Backend.Tax.prototype.Messages.enabled = "{t _enabled}";
	Backend.Tax.prototype.Messages.disabled = "{t _disabled}";
	Backend.Tax.prototype.Messages.confirmRemove = "{t _are_you_sure_you_want_to_remove_ths_tax}";
</script>

<div id="tabContainer" class="tabContainer maxHeight h--20">
	<ul class="tabList tabs">
		<li id="tabManage" class="tab active"><a href="">{t _taxes}</a></li>
		<li id="tabClasses" class="tab inactive"><a href="[[ url("backend.taxClass") ]]">{t _tax_classes}</a></li>
	</ul>
	<div class="sectionContainer maxHeight h--95">
		<div id="tabManageContent" class="maxHeight tabPageContainer">
			[[ partial("backend/tax/taxes.tpl") ]]
		</div>
	</div>
</div>

<script type="text/javascript">
	TabControl.prototype.getInstance('tabContainer', Backend.Currency.prototype.getTabUrl, Backend.Currency.prototype.getContentTabId);
</script>

[[ partial("layout/backend/footer.tpl") ]]