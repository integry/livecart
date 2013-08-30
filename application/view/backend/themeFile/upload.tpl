var
	result = [[result]],
	container = parent.$('filesList_[[theme]]'),
	tf;

container.innerHTML = '';
tf = new parent.Backend.ThemeFile(
	parent.$A(result),
	container,
	parent.$('filesList_template_[[theme]]')
);
tf.cancelOpened(container, "[[theme]]");

{% if $highlightFileName %}
	parent.ActiveList.prototype.highlight(parent.$('filesList_[[theme]]_[[highlightFileName]]'), 'yellow');
{% endif %}
