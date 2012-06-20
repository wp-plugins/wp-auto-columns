(function() {
	tinymce.create('tinymce.plugins.autocolumns', {
		init : function(ed, url) {
			ed.addCommand('mceAutoColumns', function() {
				ed.execCommand('mceInsertContent', 0, insertAutoColumns('visual', ''));
			});
			ed.addButton('auto-columns', {
				title : 'Auto Columns',
				cmd : 'mceAutoColumns',
				image : url + '/img/button.gif'
			});
		},
		createControl : function(n, cm) {
			return null;
		},
		getInfo : function() {
			return {
				longname : 'WP-Auto-Columns',
				author : 'Spectraweb s.r.o.',
				authorurl : 'http://www.spectraweb.cz/',
				infourl : 'http://www.spectraweb.cz/',
				version : '1.0.0'
			};
		}
	});
	tinymce.PluginManager.add('autocolumns', tinymce.plugins.autocolumns);
})();