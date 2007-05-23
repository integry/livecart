<div class="productFileGroup_form"  style="display: none;">
    <form action="{link controller=backend.productFileGroup action=save}" method="post">
    	<!-- STEP 1 -->
    	<fieldset>
    		<input type="hidden" name="ID" class="hidden productFileGroup_ID" />
    		<input type="hidden" name="productID" class="hidden productFileGroup_productID" />
    
    		<fieldset class="productFileGroup_main">
        		<label class="productFileGroup_name_label">{t _product_file_group_title}</label>
                <div class="error">
            		<input type="text" name="name" class="productFileGroup_name" />
            		<span class="errorText hidden"> </span>
                </div>
    		</fieldset>
            
        	<!-- STEP 3 -->
        	<fieldset class="productFileGroup_translations">
        		<fieldset class="dom_template expandingSection productFileGroup_translations_language">
        			<legend class="productFileGroup_translations_language_legend"></legend>
                    <div class="productFileGroup_translations_language_values expandingSectionContent">
                        <div>
                			<label class="productFileGroup_name_label">{t _productFileGroup_title}</label>
                			<input type="text" name="name" class="productFileGroup_name" />
            			</div>
                    </div>
                    
        		</fieldset>
        	</fieldset>
    	</fieldset>
    
        <fieldset class="productFileGroup_controls">
        	<span class="activeForm_progress"></span>
            <input type="submit" class="productFileGroup_save button submit" value="{t _save}" />
            {t _or}
            <a href="#cancel" class="productFileGroup_cancel cancel">{t _cancel}</a>
        </fieldset>
    </form>
</div>