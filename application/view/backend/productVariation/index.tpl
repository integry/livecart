<table id="productVariationTemplate" class="productVariationTable">
	<thead>
		<tr>
			<th class="variationType">
				<a class="deleteVariationType" href="#deleteVariationType"></a>
				<input class="text" name="variationType[]" />
				<div class="addVariationContainer">
					<a href="#addVariation" class="addVariation">{t _add_variation}</a>
				</div>
			</th>
			<th class="isEnabled"><input type="checkbox" class="checkbox" /></th>
			<th class="sku">{tip _sku}</th>
			<th class="price">{tip _price} ([[params.currency]])</th>
			<th class="shippingWeight">{tip _weight} (kg)</th>
			<th class="stockCount">{tip _inventory}</th>
			<th class="image">{tip _image}</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="variation" rowspan="1">
				<div class="variationInput">
					<a class="deleteVariation" href="#deleteVariation"></a>
					<input type="text" class="text" name="variation[]" />
				</div>
				<span class="name"></span>
			</td>
			<td class="isEnabled"><input type="checkbox" class="checkbox" name="isEnabled[]" /></td>
			<td class="sku"><input type="text" class="text" name="sku[]" /></td>
			<td class="price">
				<select name="priceType[]">
					<option value="">{t _no_change}</option>
					<option value="1">{t _add}</option>
					<option value="2">{t _substract}</option>
					<option value="0">{t _fixed}</option>
				</select>
				<input type="text" class="text" name="price[]" />
			</td>
			<td class="shippingWeight">
				<select name="shippingWeightType[]">
					<option value="">{t _no_change}</option>
					<option value="1">{t _add}</option>
					<option value="2">{t _substract}</option>
					<option value="0">{t _fixed}</option>
				</select>
				<input type="text" class="text" name="shippingWeight[]" />
			</td>
			<td class="stockCount"><input type="text" class="text" name="stockCount[]" /></td>
			<td class="image"><div class="imgContainer"><a href="#" rel="lightbox"><img style="display: none;" /></a></div><input type="file" class="text" name="image[]" /></td>
		</tr>
	</tbody>
</table>

<ul class="menu">
	<li class="addType"><a href="#addType">{t _add_variation_type}</a></li>
</ul>

<form class="variationForm" action="[[ url("backend.productVariation/save/" ~ parent.ID) ]]" method="post" enctype="multipart/form-data" target="{uniqid}">
	<input type="hidden" name="items" />
	<input type="hidden" name="types" />
	<input type="hidden" name="variations" />
	<div class="tableContainer"></div>

	<p class="dom_template langTemplate">
		<label></label>
		<input type="text" class="text" />
	</p>

	{language}
		<div class="types"></div>
		<div class="variations"></div>
	{/language}

	<fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" value="{t _save}" >
		{t _or}
		<a class="cancel" href="#">{t _cancel}</a>
	</fieldset>
	<iframe name="{uniqid last=true}" style="width: 100%; height: 300px; display: none;"></iframe>
</form>

<script type="text/javascript">
	new Backend.ProductVariation.Editor([[parent.ID]], {json array=$params});
</script>

{block TRANSLATIONS}