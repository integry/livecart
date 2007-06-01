{includeCss file="form.css"}

{assign var="action" value="create"}

{form id="categoryForm_$categoryId" handle=$catalogForm action="controller=backend.category action=update id=$categoryId" method="post" onsubmit="Backend.Category.updateBranch(this); return false;"}
	<fieldset class="container">

		<p class="checkbox">
			{checkbox name="isEnabled" id="isEnabled_$categoryId" class="checkbox"} <label class="checkbox" for="isEnabled_{$categoryId}">{t _active}</label>
		</p>

		<p class="required">
			<label for="name_{$categoryId}">{t _category_name}:</label>
			{textfield name="name" id="name_$categoryId"}
		</p>

		<p>
			<label for="details_{$categoryId}">{t _descr}:</label>
			{textarea name="description" id="details_$categoryId"}
		</p>
		
		<p>
			<label for="keywords_{$categoryId}">{t _keywords}:</label>
			{textarea name="keywords" id="keywords_$categoryId" style="height: 3em;"}
		</p>

		<br /><br />

		<p>
			{foreach from=$languageList key=lang item=langName}
			<fieldset class="expandingSection">
				<legend>Translate to: {$langName}</legend>
				<div class="expandingSectionContent">
					<p>
						<label>{t _category_name}:</label>
						{textfield name="name_$lang"}
					</p>
					<p>
						<label>{t _descr}:</label>
						{textarea name="description_$lang"}
					</p>
					<p>
						<label>{t _keywords}:</label>
						{textarea name="keywords_$lang" style="height: 3em;"}
					</p>
				</div>
			</fieldset>
			{/foreach}
			<script type="text/javascript">
				var expander = new SectionExpander();
				$('name_{$categoryId}').focus();
			</script>
		</p>
	</fieldset>		
    
    <fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
        <input type="submit" class="submit" id="submit" value="{t _save}"/> or
		<a href="#" class="cancel" onClick="$('categoryForm_{$categoryId}').reset(); return false;">{t _cancel}</a>
        <div class="clear"></div>
    </fieldset>

{/form}
