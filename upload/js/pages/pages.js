Pages = function() { this.__construct(); };
Pages.prototype = {
	
	__construct: function(options)
	{
		this.initWysiwyg($(".pageContentEditor:visible"));
		
		$(".Menu.chooseLanguage a").click($.context(this.chooseLanguage, this));
	},
	
	initWysiwyg: function(elem)
	{
		$(elem).tinymce({
			script_url: 'js/pages/tiny_mce/tiny_mce.js',
			
			// General options
			theme : "advanced",
			plugins : "style,inlinepopups,contextmenu,paste,xhtmlxtras",
			
			// Theme options
			theme_advanced_buttons1 : "formatselect,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,image,|,help,code"
		});
	},
	
	chooseLanguage: function(e)
	{
		var link 		= $(e.currentTarget);
		var language 	= link.data('language');
		
		$(".Menu.chooseLanguage").hide();
		$(".PopupControl.PopupOpen").removeClass("PopupOpen").addClass("PopupClosed");
		
		$("fieldset.languageSpecific").hide();
		$("fieldset.languageSpecific.language_" + language).show();
		
		this.initWysiwyg($(".pageContentEditor:visible"));
		
		e.preventDefault();
		return false;
	}

};

$(document).ready(function() {
	new Pages;
});