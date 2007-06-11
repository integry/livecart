<div class="specField_group_form">
    <form action="{link controller=backend.specFieldGroup action=save}/" method="post" class="{denied role="category.update"}formReadonly{/denied} specField_group_form_node"> 
        <input type="hidden" name="categoryID" class="specField_group_categoryID" />
        <fieldset class="specField_group_translations specField_step_main">
			<div class="specField_group_default_language">
        		<label class="specField_group_name_label">{t _specField_group_title}</label>
                <fieldset class="error" style="display: block;">
                    <input type="text" name="name" class="specField_group_name_label" {denied role="category.update"}readonly="readonly"{/denied} />
            		<span class="errorText hidden"> </span>
                </fieldset>
        	</div>
        
        	{language}
                <fieldset class="error required">
        			<label>{t _specField_group_title}</label>
        			<input type="text" name="name_{$lang.ID}" {denied role="category.update"}readonly="readonly"{/denied} />
    			</fieldset>
            {/language}
        </fieldset>
        
        <fieldset class="specField_group_controls controls">
        	<span class="activeForm_progress"></span>
            <input type="submit" class="specField_save button submit" value="{translate text=_save}" />
            {t _or}
            <a href="#cancel" class="specField_cancel cancel">{t _cancel}</a>
        </fieldset>
    </form>
</div>
