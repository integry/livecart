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
 * @author   Integry Systems
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
    counter: 1,

    /**
     * Get new ID for the form
     */
    getNewId: function()
    {
        return this.counter++;
    },


    /**
     * Backup form values
     *
     * @param HtmlFormElement form Form node
     *
     * @access public
     * @static
     */
    backup: function(form, ignoreFields, backupOptions)
    {
		ignoreFields = $A(ignoreFields || []);
		backupOptions = backupOptions === undefined ? true : backupOptions;
		
		
        if(!this.hasBackup(form))
        {
            form.backupId = this.getNewId();
        }

        this.backups[form.backupId] = {};

        Form.getElements(form).each(function(form, ignoreFields, element)
        {
            if(element.name == '') return;
            if(ignoreFields.member(element.name)) return;
			
            var name = element.name;

            var value = {}
            value.value = element.value;
            value.selectedIndex = element.selectedIndex;
            value.checked = element.checked;
			value.style = {}
            value.style.display = element.style.display;
            value.style.visibility = element.style.visibility;

            if(element.options && backupOptions)
            {
                value.options = $H({});
				var size = 0;
				$A(element.options).each(function(value, option)
				{
                    value.options[option.value + "_marker_" + (size++)] = option.text ? option.text : option.value;
				}.bind(this, value));
            }

            if(!this.backups[form.backupId][element.name])
            {
                this.backups[form.backupId][element.name] = [];
                this.backups[form.backupId][element.name][0] = value;
            }
            else
            {
                this.backups[form.backupId][element.name][this.backups[form.backupId][element.name].length] = value;
            }
        }.bind(this, form, ignoreFields));
    },


    /**
     * Create form backup from json object.
     *
     * @param HTMLElementForm Form node
     * @param Object Backup data. This object should be organized so that keys would be form fields names (not ids)
     *        and values vould be arrays of field values
     *
     * @example
     *        json = {
     *              id: [{ value: 15}],
     *            name: [{ value: test}],
     *           radio: [
     *                      {value: 1, checked: false },
     *                      {value: 2, checked: true },
     *                      {value: 3, checked: false },
     *                  ],
     *          select: [
     *                      {
     *                                value: 5,
     *                        selectedIndex: 2, // you should precalculate it yourself
     *                              options: { // keys here are values and values are the text which appears in dropdown box
     *                                 3: "text",
     *                                 4: "processor",
     *                                 5: "selector",
     *                                 6: "date"
     *                               }
     *                      }
     *                  ]
     *             }
     *
     */
    backupFromJson: function(form, json)
    {
        if(!this.hasBackup(form))
        {
            form.backupId = this.getNewId();
        }

        this.backups[form.backupId] = {};
        this.backups[form.backupId] = json;
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
    restore: function(form, ignoreFields, restoreOptions)
    {
        ignoreFields = $A(ignoreFields || []);
        backupOptions = restoreOptions === undefined ? true : restoreOptions;
		
		
        if(!this.hasBackup(form)) return;
        var occurencies = {};
		
        $A(Form.getElements(form)).each(function(form, ignoreFields, occurencies, element)
        {
            if(ignoreFields.member(element.name)) return;
            if(element.name == '' || !this.backups[form.backupId][element.name]) return;

            occurencies[element.name] = (occurencies[element.name] == undefined) ? 0 : occurencies[element.name] + 1;

            var value = this.backups[form.backupId][element.name][occurencies[element.name]];

            if(value)
            {
                element.value = value.value;
                element.checked = value.checked;
	            element.style.display = value.style.display;
	            element.style.visibility = value.style.visibility;

                if(element.options && value.options && restoreOptions)
                {
                    element.options.length = 0;
                    $H(value.options).each(function(element, option) {
						var key = option.key.match(/([\w\W]*)_marker_\d+/)[1];
                        element.options[element.options.length] = new Option(option.value, key);
                    }.bind(this, element));
                }

                element.selectedIndex = value.selectedIndex;
            }
        }.bind(this, form, ignoreFields, occurencies));
    }
}

Object.extend(Form, Form.State);