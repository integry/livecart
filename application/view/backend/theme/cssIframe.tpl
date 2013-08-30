<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<base href="{baseUrl}"></base>
	<link href="upload/css/[[file]]?{math equation='rand(1,100000)'}" rel="Stylesheet" type="text/css" />
	<link href="stylesheet/frontend/Frontend.css?{math equation='rand(1,100000)'}" rel="Stylesheet" type="text/css" />
</head>

<body>
<script type="text/javascript">
	new this.parent.Backend.ThemeColor('[[theme]]');
	{if req('saved')}
		this.parent.Backend.SaveConfirmationMessage.prototype.showMessage('{tn _colors_saved}');
		this.parent.Backend.Theme.prototype.styleTabNotChanged('[[theme]]');
		this.parent.TabControl.prototype.getInstance("tabContainer").reloadTabContent(this.parent.$("tabCss"));
		this.parent.Backend.Theme.prototype.cssTabNotChanged('[[theme]]');
	{/if}
</script>
</body>

</html>