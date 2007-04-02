<form action="{link controller=backend.productRelationshipGroup action=save}" method="post" style="display: none;">
	<!-- STEP 1 -->
	<fieldset>
		<input type="hidden" name="ID" class="hidden productRelationshipGroup_ID" />
		<input type="hidden" name="productID" class="hidden productRelationshipGroup_productID" />

		<fieldset class="productRelationshipGroup_main">
    		<label class="productRelationshipGroup_name_label">{t _productRelationshipGroup_title}</label>
            <div class="error">
        		<input type="text" name="name" class="productRelationshipGroup_name" />
        		<span class="errorText hidden"> </span>
            </div>
		</fieldset>
        
    	<!-- STEP 3 -->
    	<fieldset class="productRelationshipGroup_translations">
    		<fieldset class="dom_template productRelationshipGroup_translations_language">
    			<legend class="productRelationshipGroup_translations_language_legend"></legend>
    
                <div class="productRelationshipGroup_translations_language_values">
                    <div>
            			<label class="productRelationshipGroup_name_label">{t _productRelationshipGroup_title}</label>
            			<input type="text" name="name" class="productRelationshipGroup_name" />
        			</div>
                </div>
    		</fieldset>
    	</fieldset>
	</fieldset>

    <fieldset class="productRelationshipGroup_controls">
    	<span class="activeForm_progress"></span>
        <input type="submit" class="productRelationshipGroup_save button submit" value="{t _save}" />
        {t _or}
        <a href="#cancel" class="productRelationshipGroup_cancel cancel">{t _cancel}</a>
    </fieldset>
</form>