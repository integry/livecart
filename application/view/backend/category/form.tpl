{includeCss file="form.css"}

{assign var="action" value="create"}

{form handle=$catalogForm action="controller=backend.category action=update id=$categoryId" method="post" onsubmit="Backend.Category.updateBranch(); return false;"}
	<fieldset id="mainFieldset">
		<legend>Category details</legend>
		<p>
			<label for="name_{$categoryId}">Category name:</label>
			{textfield name="name" id="name_$categoryId"}
		</p>
		<p>
			<label for="details_{$categoryId}">Details:</label>
			{textarea name="details" id="details_$categoryId"}
		</p>
		<p>
			<label for="keywords_{$categoryId}">Keywords:</label>
			{textarea name="keywords" id="keywords_$categoryId" style="height: 3em;"}
		</p>
		<p>
			<label> </label>
			{checkbox name="isActive" style="width: auto;"} Active
		</p>

		<p>
			{foreach from=$languageList item=lang}
			<fieldset class="expandingSection">
				<legend>Translate to: {$lang}</legend>
				<div class="expandingSectionContent">
					<p>
						<label>Category name:</label>
						{textfield name="name_$lang"}
					</p>
					<p>
						<label>Details:</label>
						{textarea name="details_$lang"}
					</p>
					<p>
						<label>Keywords:</label>
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
			<input type="submit" class="submit" id="submit" value="save"/> or
			<input type="reset" class="reset" value="Cancel"/>
		</p>

	</fieldset>
{/form}