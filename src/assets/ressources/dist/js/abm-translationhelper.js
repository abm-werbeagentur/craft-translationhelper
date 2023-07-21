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
		xhr.open("POST", "/"+Craft.cpTrigger+"/abm-translationhelper/element/fetch"); /* TODO: URL */
		xhr.setRequestHeader("Content-Type", "application/json");
		xhr.setRequestHeader('Accept', 'application/json');
		xhr.send(JSON.stringify(data));

		xhr.onload = function() {
			if (xhr.status === 200) {
				const data = JSON.parse(xhr.responseText);
				const abmtranslationhelperModalContent = '<div class="header">'+data.headline+'</div><div class="content">'+data.value+'</div><div class="footer"><div class="buttons right"><button class="btn copyClipboard">' + translations.abmtranslationhelper.copy_to_clipbard + '</button><button class="btn closer">' + translations.abmtranslationhelper.close + '</button></div></div>';
				const modalDiv = document.createElement("div");
				modalDiv.classList.add("modal");
				modalDiv.classList.add("abmtranslationhelper");
				modalDiv.innerHTML = abmtranslationhelperModalContent;
				
				var myModal = new Garnish.Modal(modalDiv);

				modalDiv.querySelector('div.footer button.closer').addEventListener("click", (evt) => {
					myModal.hide();
				});
				modalDiv.querySelector('div.footer button.copyClipboard').addEventListener("click", (evt) => {
					const contentDiv = modalDiv.querySelectorAll('div.content')[0];

					/* BEGIN HTML kopieren */
					/*
					const html = contentDiv.innerHTML;
					const textarea = document.createElement("textarea");
					textarea.textContent = html;
					//textarea.style.display = "none";
					document.body.appendChild(textarea);

					textarea.select();
					document.execCommand("copy");
					textarea.remove();
					*/
					/* END HTML kopieren */



					/* BEGIN Content kopieren */
					// Create a range object and select the text content of the div
					const range = document.createRange();
					range.selectNode(contentDiv);

					// Get the current selection and remove any existing ranges
					const selection = window.getSelection();
					selection.removeAllRanges();

					// Add the new range to the selection
					selection.addRange(range);

					// Execute the copy command
					document.execCommand('copy');

					// Clean up the selection
					selection.removeAllRanges();
					/* END Content kopieeren */
					
					alert(translations.abmtranslationhelper.copied_to_clipboard);
				});
			} else {
				console.error(xhr.statusText);
			}
		};
	}
};


document.addEventListener("DOMContentLoaded", () => {
	AbmTranslationHelperClass = new AbmTranslationHelperClass()
});