{include file="backend/taxClass/class.tpl" class=$class classForm=$classForm}
<script type=text/javascript>
	var newForm = Backend.TaxClass.prototype.getInstance($("class_classesList_{$class.ID}").down('form'));
</script>