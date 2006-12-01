/**
 * Form.State - is used to remember last valid form state.
 *
 * @example Assume you have changed original form values and then saved it with ajax request.
 *          Now if you hit the reset button form fields will be set to original values which are
 *          out of date because you have saved form with ajax.
 *
 *          The solution is to save form (Form.backup(form);) state (all form values) when you click save and get success
 *          response. Later when you click reset button you should prevent it's default action and restore last valid
 *          form values (Form.restore(form);)
 *
 *
 * This class etends "Prototype" framework's Form class with these static methods:
 *     Form.backup(form)     - Create form's backup copy
 *     Form.restore(form)    - Restore form's backup copy
 *     Form.hasBackup(form)  - Check if form has a backup copy
 *
 * Be aware as the backup does not store all form elemens (only values), so if you dinamically removed form field
 * after backup was done there is no way to restore.
 *
 * @version 1.0
 * @author Sergej Andrejev
 *
 */
if (Form == undefined)
{
	var Form = {}
}

Form.State = {
    /**
     * Hash table of all backups. Every backed up form should store it's backup id (this is done in backup method)
     * Also fields are indexed by field name and not the id, s therefore there is no need to add id to every field
     *
     * @var array
     *
     * @access private
     * @static
     */
    backups: [],

    /**
     * Backup id autoincrementing value
     *
     * @var int
     *
     * @access private
     * @static
     */
    counter: 0,


    /**
     * Backup form values
     *
     * @param HtmlFormElement form Form node
     *
     * @access public
     * @static
     */
    backup: function(form)
    {
        if(!form.backupId)
        {
            form.backupId = this.counter;
        }

        this.backups[++form.backupId] = {};

        var elements = Form.getElements(form);
        for(var i = 0; i < elements.length; i++)
        {
            if(elements[i].name == '') continue;

            var name = elements[i].name;

            var value = {}
            value.value = elements[i].value;
            value.selectedIndex = elements[i].selectedIndex;
            value.checked = elements[i].checked;

            if(elements[i].options)
            {
                value.options = {};
                for(var j = 0; j < elements[i].options.length; j++)
                {
                    var oval = elements[i].options[j].firstChild ? elements[i].options[j].firstChild.nodeValue : elements[i].options[j].value;
                    value.options[elements[i].options[j].value] = oval;
                }

                var test = value.options[value.options.length];
            }

            if(!this.backups[form.backupId][elements[i].name])
            {
                this.backups[form.backupId][elements[i].name] = [];
                this.backups[form.backupId][elements[i].name][0] = value;
            }
            else
            {
                this.backups[form.backupId][elements[i].name][this.backups[form.backupId][elements[i].name].length] = value;
            }
        }
    },


    /**
     * Check if form has a backup
     *
     * @param HtmlFormElement form Form node
     * @return bool
     *
     * @access public
     * @static
     */
    hasBackup: function(form)
    {
        return form.backupId && this.backups[form.backupId];
    },


    /**
     * Restore form values
     *
     * @param HtmlFormElement form Form node
     *
     * @access public
     * @static
     */
    restore: function(form)
    {
        if(!this.hasBackup(form)) return;

        var occurencies = {};
        var elements = Form.getElements(form);
        for(var i = 0; i < elements.length; i++)
        {
            if(elements[i].name == '' || !this.backups[form.backupId][elements[i].name]) continue;

            occurencies[elements[i].name] = (occurencies[elements[i].name] == undefined) ? 0 : occurencies[elements[i].name] + 1;

            var value = this.backups[form.backupId][elements[i].name][occurencies[elements[i].name]];

            if(value)
            {
                elements[i].value = value.value;
                elements[i].checked = value.checked;

                if(elements[i].options && value.options)
                {
                    elements[i].options.length = 0;
                    for(var oval in value.options)
                    {
                        elements[i].options[elements[i].options.length] = new Option(value.options[oval], oval);
                    }

                    var test = elements[i].options;
                }

                elements[i].selectedIndex = value.selectedIndex;
            }
        }
    }
}


Object.extend(Form, Form.State);