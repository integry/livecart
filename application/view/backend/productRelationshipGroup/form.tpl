<div class="groupForm"  style="display: none;">
	<form action="[[ url("backend.productRelationshipGroup/save") ]]" method="post" {denied role="product.update"}class="formReadonly"{/denied}>
		<!-- STEP 1 -->
		<fieldset>
			<legend>{t _add_group_title}</legend>
			<input type="hidden" name="ID" class="hidden" />
			<input type="hidden" name="ownerID" class="hidden" />

			{input name="name"}
				{label}{t _product_relationship_group_title}:{/label}
				<input type="text" name="name" class="text" {denied role="product.update"}readonly="readonly"{/denied} />
			{/input}

			<!-- STEP 3 -->
			{language}
				{input name="name_`$lang.ID`"}
					{label}{t _product_relationship_group_title}:{/label}
					<input type="text" name="name_[[lang.ID]]" class="text" {denied role="product.update"}readonly="readonly"{/denied} />
				{/input}
			{/language}
		</fieldset>

		<fieldset class="controls">
			<input type="hidden" name="type" value="[[type]]" />
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" class="save button submit" value="{t _save}" />
			{t _or}
			<a href="#cancel" class="cancel">{t _cancel}</a>
		</fieldset>
	</form>
</div>