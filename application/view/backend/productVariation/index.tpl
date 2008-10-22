<table id="productVariationTemplate">
	<thead>
		<tr>
			<th class="variationType"></th>
			<th class="sku">{t _sku}</th>
			<th class="price">{t _price}</th>
			<th class="weight">{t _weight}</th>
			<th class="inventory">{t _inventory}</th>
			<th class="image">{t _image}</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="variation"></td>
			<td class="sku"><input type="text" class="text" name="sku" /></td>
			<td class="price">
				<select name="priceType">
					<option value="0">{t _add}</option>
					<option value="1">{t _substract}</option>
					<option value="2">{t _fixed}</option>
				</select>
				<input type="text" class="text" name="price" />
			</td>
			<td class="weight">
				<select name="weightType">
					<option value="0">{t _add}</option>
					<option value="1">{t _substract}</option>
					<option value="2">{t _fixed}</option>
				</select>
				<input type="text" class="text" name="shippingWeight" />
			</td>
			<td class="inventory"><input type="text" class="text" name="inventory" /></td>
			<td class="image"><input type="file" class="text" name="image" /></td>
		</tr>
	</tbody>
</table>

<form action="{link controller=backend.productVariation action=save id=$parent.ID}" method="POST" enctype="multipart/form-data">

</form>

<script type="text/javascript">
	Backend.ProductVariation.getInstance({$parent.ID}, {json array=$params});
</script>