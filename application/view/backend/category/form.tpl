{includeCss file="form.css"}

{assign var="action" value="create"}

{form handle=$catalogForm action="controller=backend.category action=update id=$categoryId" method="post"}
	<fieldset id="mainFieldset">
		<legend>Category details</legend>
		<p>
			<label for="name">Category name:</label>
			{textfield name="name" id="name"}
		</p>
		<p>
			<label for="details">Details:</label>
			{textarea name="details" id="details"}
		</p>
		<p>
			<label for="keywords">Keywords:</label>
			{textarea name="keywords" id="keywords" style="height: 3em;"}
		</p>
		<p>
			<label for="isActive"> </label>
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
						{textarea name="keywords_$lang"  style="height: 3em;"}
					</p>
				</div>
			</fieldset>
			{/foreach}
			<script type="text/javascript">
				var expander = new SectionExpander();
			</script>
		</p>
		<p>
			<label for="submit"> </label>
			<input type="submit" class="submit" id="submit" value="save"/> or <input type="reset" class="reset" value="Cancel"/>
		</p>

	</fieldset>
{/form}