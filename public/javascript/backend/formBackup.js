if (Form == undefined)
{
	var Form = {}
}

Form.Backup = {
    backups: [],
    counter: 0,

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

            var value = {}
            value.value = elements[i].value;
            value.selectedIndex = elements[i].selectedIndex;
            value.checked = elements[i].checked;

            if(elements[i].options)
            {
                value.options = [];
                for(var j = 0; j < elements[i].options.length; j++)
                {
                    value.options[value.options.length] = elements[i].options[j];
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

        var test = 'te';
        jsTrace.send(form.backupId);
    },

    restore: function(form)
    {
        if(form.backupId && this.backups[form.backupId])
        {
            var elements = Form.getElements(form);
            for(var i = 0; i < elements.length; i++)
            {
                var value = this.backups[form.backupId][elements[i].name][0];

                var test2 = elements[i].name;
                var test0 = this.backups[form.backupId][elements[i].name][0];
                var test1 = value;

                if(value)
                {
                    elements[i].value = value.value;
                    elements[i].checked = value.checked;

                    if(elements[i].options)
                    {
                        elements[i].options.length = 0;
                        for(var j = 0; j < value.options.length; j++)
                        {
                            elements[i].options[value.options.length] = elements[i].options[j];
                        }
                    }

                    elements[i].selectedIndex = value.selectedIndex;
                }
            }
        }
    }
}

Object.extend(Form, Form.Backup);