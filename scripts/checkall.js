function checkAllEvents() {
	var checkBoxForm = document.getElementById('eventsForm');
	var checkAllBox = document.getElementById('CheckAllBox');
	var booleanValue = checkAllBox.checked;
	for (i = 0; i < checkBoxForm.length; i++) {
		if(checkBoxForm[i].type == 'checkbox')
			checkBoxForm[i].checked = booleanValue ;
	}
}