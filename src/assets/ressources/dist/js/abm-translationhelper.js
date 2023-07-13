class AbmTranslationHelperClass {

	$buttons;

	constructor() {
		this.$buttons = document.querySelectorAll('button.abmtranslationhelper');
		this.$buttons.forEach( (btn,index) => {
			btn.addEventListener("click", (evt) => {
				this.get_entry_text(btn);
			});
		});
	}

	view_success_message() {

	}

	get_entry_text(btn) {
		var data = {
			elementid: btn.getAttribute('data-elementid'),
			elementuid: btn.getAttribute('data-elementuid'),
			originalsiteid: btn.getAttribute('data-originalsiteid'),
			elementcontext: btn.getAttribute('data-elementcontext'),
			handle: btn.getAttribute('data-handle'),
			CSRF_TOKEN: $('input[name="CSRF_TOKEN"]').val()
		};

		$.ajax({
			type: "POST",
			url: "/craft_admin/abm-translationhelper/element/fetch", /* TODO: URL */
			data: data,
			success: data =>{
				btn.replaceWith(data.value);
			},
			error:e => {
				console.error(e)
			},
			dataType:'json'
		});
	}
};


document.addEventListener("DOMContentLoaded", () => {
	AbmTranslationHelperClass = new AbmTranslationHelperClass()
});