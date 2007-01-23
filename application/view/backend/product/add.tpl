{form handle=$productForm action="controller=backend.product action=save"}
	<fieldset>
		<legend>Main product information</legend>

		<p>
			<label>Product name</label>
			{textfield name="name"}
		</p>
		<p>
			<label>Short description:</label>
			{textarea name="shortDescription"}
		</p>
		<p>
			<label>Short description:</label>
			{textarea name="longDescription"}
		</p>
		<p>
			<label>SKU:</label>
			{textfield name="SKU"}
		</p>
		<p>
			<label>Status</label>
			{selectfield name="status"}
		</p>
		<p>
			Is bestseller
			{checkbox name="isBestseller"}
		</p>
		<p>

		<fieldset>
			<legend>Shipping Info</legend>
			<p>
				<label>Height:</label>
				{textfield name="shippingHeight"}
			</p>
			<p>
			</p>
		</fieldset>

		<hr/>
		{include file="backend/product/specificationForm.tpl"}

	</fieldset>

	{foreach from=$languageList key=lang item=langName}
	<fieldset class="expandingSection">
		<legend>Translate to: {$langName}</legend>
		<div class="expandingSectionContent">
			<p>
				<label>Product name:</label>
				{textfield name="name_$lang"}
			</p>
			<p>
				<label>Short description:</label>
				{textarea name="shortDescription_$lang"}
			</p>
			<p>
				<label>Long description:</label>
				{textarea name="longDescription_$lang"}
			</p>
		</div>
	</fieldset>
	{/foreach}
{/form}