<div class="productFile_form"  style="display: none;">
    <form action="{link controller=backend.productFile action=save}" method="post">
    	<!-- STEP 1 -->
    	<fieldset>
    		<input type="hidden" name="ID" class="hidden productFile_ID" />
    		<input type="hidden" name="productID" class="hidden productFile_productID" />
    
    		<fieldset class="productFile_main">
            		<label class="productFile_description_label">{t _productFile_description}</label>
                    <div class="error">
                		<input type="text" name="name" class="productFile_description" />
                		<span class="errorText hidden"> </span>
                    </div>
                    
            		<label class="productFile_allowDownloadDays_label">{t _productFile_allow_download_for}</label>
                    <div class="error">
                		<input type="text" name="allowDownloadDays" class="productFile_allowDownloadDays" />
                        {t _days}
                		<span class="errorText hidden"> </span>
                    </div>
                    
            		<label class="productFile_uploadFile_label">{t _productFile_uploadFile}</label>
                    <div class="error">
                		<input type="file" name="uploadFile" class="productFile_uploadFile" />
                		<span class="errorText hidden"> </span>
                    </div>
    		</fieldset>
            
        	<!-- STEP 3 -->
        	<fieldset class="productFile_translations">
        		<fieldset class="dom_template productFile_translations_language">
        			<legend class="productFile_translations_language_legend"></legend>
        
                    <div class="productFile_translations_language_values">
                        <div>
                			<label class="productFile_description_label">{t _productFile_description}</label>
                			<input type="text" name="name" class="productFile_description" />
            			</div>
                    </div>
        		</fieldset>
        	</fieldset>
    	</fieldset>
    
        <fieldset class="productFile_controls">
        	<span class="activeForm_progress"></span>
            <input type="submit" class="productFile_save button submit" value="{t _save}" />
            {t _or}
            <a href="#cancel" class="productFile_cancel cancel">{t _cancel}</a>
        </fieldset>
    </form>
</div>