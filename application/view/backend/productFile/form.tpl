<div class="productFile_form"  style="display: none;">
    <form action="{link controller=backend.productFile action=update}" method="post" target="productFileUploadIFrame_" enctype="multipart/form-data" {denied role="product.update"}class="formReadonly"{/denied}>
    	<!-- STEP 1 -->
    	<fieldset>
    		<input type="hidden" name="ID" class="hidden productFile_ID" />
    		<input type="hidden" name="productID" class="hidden productFile_productID" />
    
    		<fieldset class="productFile_main">
        		<label class="productFile_title_label">{t _productFile_title}</label>
                <fieldset  class="error">
            		<input type="text" name="title" class="productFile_title" {denied role="product.update"}readonly="readonly"{/denied} />
            		<span class="errorText hidden"> </span>
                </fieldset >
                <fieldset class="productFile_fileName_div">
            		<label class="productFile_fileName_label">{t _productFile_change_fileName}</label>
                    <fieldset class="error">
                		<div>
                		    <input type="text" name="fileName" class="productFile_fileName" {denied role="product.update"}readonly="readonly"{/denied} />
                		    <span class="productFile_extension">.jpg</span>
                        </div>
                		<span class="errorText hidden"> </span>
                    </fieldset>
                </fieldset>
                
        		<label class="productFile_description_label">{t _productFile_description}</label>
                <fieldset class="error">
            		<textarea type="text" name="description" class="productFile_description" {denied role="product.update"}readonly="readonly"{/denied}></textarea>
            		<span class="errorText hidden"> </span>
                </fieldset>
                
        		<label class="productFile_allowDownloadDays_label">{t _productFile_allow_download_for}</label>
                <fieldset class="error">
            		<input type="text" name="allowDownloadDays" class="productFile_allowDownloadDays" {denied role="product.update"}readonly="readonly"{/denied} />
                    {t _days}
            		<span class="errorText hidden"> </span>
                </fieldset>
                
        		<label class="productFile_uploadFile_label">{t _productFile_uploadFile}</label>
                <fieldset class="error">
            		<input type="file" name="uploadFile" class="productFile_uploadFile" {denied role="product.update"}disabled="disabled"{/denied} />
                    <span {denied role='product.download'}style="display: none"{/denied}>
                    <a class="productFile_download_link" href="" target="_blank" style="display: none"></a>
            		</span>
                    <span class="errorText hidden"> </span>
                </fieldset>
    		</fieldset>
            
        	<!-- STEP 3 -->
        	<fieldset class="productFile_translations">
        		<fieldset class="dom_template productFile_translations_language expandingSection">
        			<legend class="productFile_translations_language_legend"></legend>
                    
                    <div class="expandingSectionContent">
                        <div class="productFile_translations_language_values">
                            <div>
                    			<label class="productFile_title_label">{t _productFile_title}</label>
                    			<input type="text" name="title" class="productFile_title" {denied role="product.update"}readonly="readonly"{/denied} />
                			</div>
                            <div>
                    			<label class="productFile_description_label">{t _productFile_description}</label>
                    			<input type="text" name="description" class="productFile_description" {denied role="product.update"}readonly="readonly"{/denied} />
                			</div>
                        </div>
                    </div>
        		</fieldset>
        	</fieldset>
    	</fieldset>
    
        <fieldset class="productFile_controls controls">
        	<span class="activeForm_progress"></span>
            <input type="submit" class="productFile_save button submit" value="{t _save}" />
            {t _or}
            <a href="#cancel" class="productFile_cancel cancel">{t _cancel}</a>
        </fieldset>
    </form>
</div>