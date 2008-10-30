<table id="productVariationTemplate" class="productVariationTable">
	<thead>
		<tr>
			<th class="variationType">
				<input class="text" />
				<span class="typeName"></span>
				<a href="#addVariation" class="addVariation">{t _add_variation}</a>
			</th>
			<th class="sku">{t _sku}</th>
			<th class="price">{t _price}</th>
			<th class="weight">{t _weight}</th>
			<th class="inventory">{t _inventory}</th>
			<th class="image">{t _image}</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="variation" rowspan="1">
				<input type="text" class="text" name="name" />
				<span class="name"></span>
			</td>
			<td class="sku"><input type="text" class="text" name="sku" /></td>
			<td class="price">
				<select name="priceType">
					<option value="">{t _no_change}</option>
					<option value="0">{t _add}</option>
					<option value="1">{t _substract}</option>
					<option value="2">{t _fixed}</option>
				</select>
				<br />
				<input type="text" class="text" name="price" />
			</td>
			<td class="weight">
				<select name="weightType">
					<option value="">{t _no_change}</option>
					<option value="0">{t _add}</option>
					<option value="1">{t _substract}</option>
					<option value="2">{t _fixed}</option>
				</select>
				<br />
				<input type="text" class="text" name="shippingWeight" />
			</td>
			<td class="inventory"><input type="text" class="text" name="inventory" /></td>
			<td class="image"><input type="file" class="text" name="image" /></td>
		</tr>
	</tbody>
</table>

<ul class="menu">
	<li class="addType"><a href="#addType">{t _add_variation_type}</a></li>
</ul>

<form action="{link controller=backend.productVariation action=addType id=$parent.ID}" method="POST" enctype="multipart/form-data">
	<p>
{*
		{{err for="name"}}
			{{label {t _type_name}:}}
			{textfield class="text"}
		{/err}
*}
	</p>
</form>

<form class="variationForm" action="{link controller=backend.productVariation action=save id=$parent.ID}" method="POST" enctype="multipart/form-data">

</form>

<script type="text/javascript">
	new Backend.ProductVariation.Editor({$parent.ID}, {json array=$params});
</script>