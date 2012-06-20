function insertAutoColumns(where, myField)
{
	if(where == 'code') {
		edInsertContent(myField, '[auto_columns][/auto_columns]');
	} else {
		return '[auto_columns][/auto_columns]';
	}
	return '';
}

if(document.getElementById("ed_toolbar")){
	edButtons[edButtons.length] = new edButton("ed_autocolumns","auto_columns", "", "","");
	jQuery(document).ready(function($){
		$('#qt_content_ed_autocolumns').replaceWith('<input type="button" id="qt_content_ed_autocolumns" accesskey="" class="ed_button" onclick="insertAutoColumns(\'code\', edCanvas);" title="Insert Auto Columns" value="auto_columns" />');
	});
}