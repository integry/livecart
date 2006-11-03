var TabControll = {
	
	test: function() {
		Event.observe('tabFields', 'click', TabControll.msg, false);
		Event.observe('tabFields', 'click', TabControll.notice, false);
	},
	
	msg: function() {
		alert('this is TabControll.msg');
	},
	
	notice: function() {
		alert('This is TabControll.notice');
	}
}