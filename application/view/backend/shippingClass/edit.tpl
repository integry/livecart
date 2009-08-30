{include file="backend/shippingClass/class.tpl" class=$class classForm=$classForm}
<script type=text/javascript>
	var newForm = Backend.ShippingClass.prototype.getInstance($("class_classesList_{$class.ID}").down('form'));
</script>