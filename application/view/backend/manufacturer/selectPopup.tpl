{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/TabControl.js"}

{includeCss file="library/TabControl.css"}
{includeCss file="library/ActiveList.css"}
{includeCss file="library/ActiveGrid.css"}

{includeJs file="backend/ObjectImage.js"}
{includeJs file="backend/Manufacturer.js"}
{includeCss file="backend/Manufacturer.css"}
{includeCss file="backend/CategoryImage.css"}

{includeJs file="library/ActiveList.js"}
{includeCss file="library/ActiveGrid.css"}
{includeCss file="backend/ProductRelatedSelectProduct.css"}

{% block title %}{t _select_manufacturer}{{% endblock %}

[[ partial("layout/backend/meta.tpl") ]]

<a id="help" href="#" target="_blank" style="display: none;">Help</a>

	<div style="margin-bottom: 5px; text-align: center;">
		<ul class="menu popup">
			<li class="done">
				<a class="menu" href="#" onclick="window.close(); return false;">
					{t _done_adding}
				</a>
			</li>
		</ul>
	</div>

<div class="manufacturerGrid" id="manufacturerGrid" class="maxHeight h--50">
	[[ partial("backend/manufacturer/grid.tpl") ]]
</div>

</body>
</html>