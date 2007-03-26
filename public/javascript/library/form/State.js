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
    backup: function(form)
    {
        if(!this.hasBackup(form))
        {
            form.backupId = this.getNewId();
        }

        this.backups[form.backupId] = {};

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
    restore: function(form, ignoreFields)
    {
        if(!ignoreFields) ignoreFields = [];
        ignoreFields = $A(ignoreFields);
        if(!this.hasBackup(form)) return;
        self = this;

        var occurencies = {};
        var elements = $A(Form.getElements(form));
        try
        {
            $A(Form.getElements(form)).each(function(element)
            {
                if(ignoreFields.member(element.name)) return;
                if(element.name == '' || !self.backups[form.backupId][element.name]) return;

                occurencies[element.name] = (occurencies[element.name] == undefined) ? 0 : occurencies[element.name] + 1;

                var value = self.backups[form.backupId][element.name][occurencies[element.name]];

                if(value)
                {
                    element.value = value.value;
                    element.checked = value.checked;

                    if(element.options && value.options)
                    {
                        element.options.length = 0;
                        $H(value.options).each(function(option) {
                            element.options[element.options.length] = new Option(option.value, option.key);
                        });
                    }

                    element.selectedIndex = value.selectedIndex;
                }
            });
        }
        catch(e)
        {
            console.info(e);
        }
    }
}

Object.extend(Form, Form.State);