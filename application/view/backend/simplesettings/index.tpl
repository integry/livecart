{%- macro imagefld(field, title, params) %}
    [[ startinput(field, 'textfld', params) ]]
        {% if empty(params) %}{% set params = ['size': ''] %}{% endif %}
        [[ label(t(title)) ]]
        <image-field ng-model="vals.[[field]]" size="[[ params['size'] ]]" single="true"></image-field>
    [[ endinput(field) ]]
{%- endmacro %}

<div ng-controller="SimpleSettingsController">
[[ form('', ['ng-submit': 'save()', 'ng-init': 'vals = ' ~ json(config) ]) ]] >
	
	<h2>Twitter Block</h2>
	[[ textfld('hashtag', 'Twitter username') ]]
	
	<h2>Instagram Block</h2>
	[[ textfld('instagram', 'Instagram hashtag') ]]

	<h2>Home Page Slider</h2>
	[[ imagefld('index_img_1', 'Home page image 1', ['size': '10000x10000']) ]]
	[[ textfld('index_img_link_1', 'Home page image 1 link') ]]
	[[ imagefld('index_img_2', 'Home page image 2', ['size': '10000x10000']) ]]
	[[ textfld('index_img_link_2', 'Home page image 2 link') ]]
	[[ imagefld('index_img_3', 'Home page image 3', ['size': '10000x10000']) ]]
	[[ textfld('index_img_link_3', 'Home page image 3 link') ]]

	<h2>Home Page Banners</h2>
	[[ imagefld('banner_1', 'Banner 1') ]]
	[[ textfld('banner_1_link', 'Banner 1 link') ]]
	[[ imagefld('banner_2', 'Banner 2') ]]
	[[ textfld('banner_2_link', 'Banner 2 link') ]]

	<h2>E-mails</h2>
	[[ textfld('notification_email', 'Send notifications to') ]]
	
	<h2>Description</h2>
	[[ textfld('INDEX_META_DESCRIPTION', 'Meta description') ]]

	<p>
		<submit>Save</submit>
	</p>

</form>
</div>
