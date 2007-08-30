<fieldset class="addForm productFileGroup_form"  style="display: none;">
    <legend>{t _add_new_file_group}</legend>
    <form action="{link controller=backend.productFileGroup action=save}" method="post" {denied role="product.update"}class="formReadonly"{/denied}>
    	<!-- STEP 1 -->
    	
    		<input type="hidden" name="ID" class="hidden productFileGroup_ID" />
    		<input type="hidden" name="productID" class="hidden productFileGroup_productID" />
    
    		<fieldset class="productFileGroup_main">
        		<label class="productFileGroup_name_label">{t _product_file_group_title}</label>
                <div class="error">
            		<input type="text" name="name" class="productFileGroup_name" {denied role="product.update"}readonly="readonly"{/denied} />
            		<span class="errorText hidden"> </span>
                </div>
    		</fieldset>
            
        	<!-- STEP 3 -->
        	{language}
                <fieldset class="error">
            		<label>{t _product_file_group_title}:</label>
            		<input type="text" value="" id="name_{$lang.ID}" name="name_{$lang.ID}"/>
            	</fieldset>
		    {/language}
    
        <fieldset class="productFileGroup_controls controls">
        	<span class="progressIndicator" style="display: none;"></span>
            <input type="submit" class="productFileGroup_save button submit" value="{t _save}" />
            {t _or}
            <a href="#cancel" class="productFileGroup_cancel cancel">{t _cancel}</a>
        </fieldset>
    </form>
</fieldset>