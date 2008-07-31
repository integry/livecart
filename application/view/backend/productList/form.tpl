<div class="groupForm"  style="display: none;">
	<form action="{link controller=backend.productList action=save}" method="post" {denied role="product.update"}class="formReadonly"{/denied}>
		<!-- STEP 1 -->
		<fieldset>
			<legend>{t _add_list_title}</legend>
			<input type="hidden" name="ID" class="hidden" />
			<input type="hidden" name="ownerID" class="hidden" />

			<fieldset>
				<label>{t _list_name}</label>
				<fieldset class="error">
					<input type="text" name="name" class="text" {denied role="product.update"}readonly="readonly"{/denied} />
					<span class="errorText hidden"> </span>
				</fieldset>
			</fieldset>

			<!-- STEP 3 -->
			{language}
				<fieldset class="error">
					<label>{t _list_name}</label>
					<input type="text" name="name_{$lang.ID}" class="text" {denied role="product.update"}readonly="readonly"{/denied} />
				</fieldset>
			{/language}
		</fieldset>

		<fieldset class="controls">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" class="save button submit" value="{t _save}" />
			{t _or}
			<a href="#cancel" class="cancel">{t _cancel}</a>
		</fieldset>
	</form>
</div>