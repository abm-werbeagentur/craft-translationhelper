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
			originalsiteid: btn.getAttribute('data-originalsiteid'),
			handle: btn.getAttribute('data-handle'),
			CSRF_TOKEN: $('input[name="CSRF_TOKEN"]').val()
		};

		$.ajax({
			type: "POST",
			url: "/craft_admin/abm-translationhelper/element/fetch", /* TODO: URL */
			data: data,
			success: data =>{
				btn.replaceWith(data.value);
				/*
				if(data.res){
					this.view_success_message(data.msg)
				}else{
					this.errors.push(data.msg);
					this.view_error_message()
				}
				$("#buddy_generate_entry").prop('disabled',false).removeClass('loading')
				*/
			},
			error:e => {
				console.log(e)
				/*
				this.errors.push(e.statusText);
				this.view_error_message()
				$("#buddy_generate_entry").prop('disabled',false).removeClass('loading')
				*/
			},
			dataType:'json'
		});
	}
};


document.addEventListener("DOMContentLoaded", () => {
	AbmTranslationHelperClass = new AbmTranslationHelperClass()
});