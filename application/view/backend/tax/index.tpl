{includeJs file="library/ActiveList.js"}
{includeJs file="backend/Tax.js"}

{includeJs file="library/form/State.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="library/form/Validator.js"}

{includeCss file="backend/Tax.css"}
{includeCss file="library/ActiveList.css"}
{includeCss file="backend/Tab.css"}
{includeCss file="library/TabControl.css"}

{pageTitle}{t _taxes}{/pageTitle}
{include file="layout/backend/header.tpl"}

<script type="text/javascript">
    Backend.Tax.prototype.Links.update = "{link controller=backend.tax action=update}";
    Backend.Tax.prototype.Links.create = "{link controller=backend.tax action=create}";
    Backend.Tax.prototype.Links.edit = "{link controller=backend.tax action=edit}";
    Backend.Tax.prototype.Links.remove = "{link controller=backend.tax action=delete}";
    Backend.Tax.prototype.Messages.enabled = "{t _enabled}";
    Backend.Tax.prototype.Messages.disabled = "{t _disabled}";
    Backend.Tax.prototype.Messages.confirmRemove = "{t _are_you_sure_you_want_to_remove_ths_tax}";
</script>

<div id="confirmations"></div>
{include file="backend/tax/taxes.tpl"}

{include file="layout/backend/footer.tpl"}