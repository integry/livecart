<input type="text" class="text number" value="{relationship.count|default:1}" /><span> x </span>
<script type="text/javascript">
	Backend.ProductBundle.quantityField('[[ownerID]]', '[[relationship.RelatedProduct.ID]]');
</script>