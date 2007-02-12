{includeCss file="form.css"}

{assign var="action" value="create"}

<div id="categoryMsg_{$categoryId}"></div>

{form id="categoryForm_$categoryId" handle=$catalogForm action="controller=backend.category action=update id=$categoryId" method="post" onsubmit="Backend.Category.updateBranch($('categoryForm_$categoryId')); return false;"}
	<fieldset class="container">

		<p class="checkbox">
			{checkbox name="isActive" id="isActive_$categoryId" class="checkbox"} <label class="checkbox" for="isActive_{$categoryId}">{t _active}</label>
		</p>

		<p>
			<label for="name_{$categoryId}">{t _category_name}:</label>
			{textfield name="name" id="name_$categoryId"}
		</p>

		<p>
			<label for="handle_{$categoryId}">{t _category_handle}:</label>
			{textfield name="handle" id="handle_$categoryId"}
		</p>
		
		<p>
			<label for="details_{$categoryId}">{t _descr}:</label>
			{textarea name="details" id="details_$categoryId"}
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
						{textarea name="details_$lang"}
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
		<p>
			<label for="submit"> </label>
			<input type="submit" class="submit" id="submit" value="{t _save}"/> or
			<a href="#" class="cancel" onClick="$('categoryForm_{$categoryId}').reset(); return false;">{t _cancel}</a>
		</p>
	</fieldset>		
{/form}
