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

		const xhr = new XMLHttpRequest();
		xhr.open("POST", "/craft_admin/abm-translationhelper/element/fetch"); /* TODO: URL */
		xhr.setRequestHeader("Content-Type", "application/json");
		xhr.setRequestHeader('Accept', 'application/json');
		xhr.send(JSON.stringify(data));

		xhr.onload = function() {
			if (xhr.status === 200) {
				const data = JSON.parse(xhr.responseText);

				const div = document.createElement("div");
				const header = document.createElement("h3");
				const p = document.createElement("p");

				header.innerHTML = data.headline;
				p.innerHTML = data.value;
				div.appendChild(header);
				div.appendChild(p);
				div.classList.add('hasText');

				//btn.replaceWith("<div class='headline'>" + data.headline + "</div>" + data.value);
				btn.replaceWith(div);
			} else {
				console.error(xhr.statusText);
			}
		};
	}
};


document.addEventListener("DOMContentLoaded", () => {
	AbmTranslationHelperClass = new AbmTranslationHelperClass()
});