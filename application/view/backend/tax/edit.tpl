{include file="backend/tax/tax.tpl" tax=$tax taxForm=$taxForm}
<script type=text/javascript>
    console.info("tax_taxesList_{$tax.ID}");
    var newForm = Backend.Tax.prototype.getInstance($("tax_taxesList_{$tax.ID}").down('form'));
</script>