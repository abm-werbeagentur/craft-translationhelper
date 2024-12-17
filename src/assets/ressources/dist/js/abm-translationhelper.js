class AbmTranslationHelperClass {

    constructor() {
    }

    get_entry_text(btn, hud) {
        if(typeof hud !== 'object')
            return;

        var hudTarget = hud.target;
        
        var data = {
            elementid: btn.getAttribute('data-elementid'),
            elementuid: btn.getAttribute('data-elementuid'),
            originalsiteid: btn.getAttribute('data-originalsiteid'),
            elementcontext: btn.getAttribute('data-elementcontext'),
            handle: btn.getAttribute('data-handle')
        };
        data[Craft.csrfTokenName] = Craft.csrfTokenValue;

        Craft.postActionRequest("abm-translationhelper/element/fetch",data,$.proxy(function(response, textStatus) {
            if (textStatus === 'success') {
                // const data = JSON.parse(response);
                const abmtranslationhelperModalContent = '<div class="hud-header">'+response.headline+'</div><textarea class="copyText" style="display: none;">'+response.value+'</textarea><div class="abmtranslationhelperHudBody body">'+response.value+'</div><div class="hud-footer"><div class="flex"><button class="btn copyClipboard">' + translations.abmtranslationhelper.copy_to_clipbard + '</button><button class="btn cancel abm-hud-closer">' + translations.abmtranslationhelper.close + '</button></div></div>';
                hudTarget.updateBody(abmtranslationhelperModalContent);
                hudTarget.updateSizeAndPosition(true);

                hud.target.$body[0].querySelector('button.abm-hud-closer').addEventListener("click", (evt) => {
                    evt.preventDefault();
                    hud.target.hide();
                });

                hud.target.$body[0].querySelector('div.hud-footer button.copyClipboard').addEventListener("click", (evt) => {
                    const copyTextarea = hud.target.$body[0].querySelector('textarea.copyText');
                    copyTextarea.style.display = '';
                    copyTextarea.select();
                    document.execCommand('copy');
                    copyTextarea.style.display = 'none';
                    
                    //evt.target.textContent = translations.abmtranslationhelper.copied;
                    alert(translations.abmtranslationhelper.copied_to_clipboard);
                });
            } else {
                console.error(xhr.statusText);
            }
        }, this));
    }
};

var AbmTranslationHelperObject = new AbmTranslationHelperClass();


var $translationHelperButtons = document.querySelectorAll('button.abmtranslationhelper');
$translationHelperButtons.forEach( (btn,index) => {
    btn.addEventListener("click", (evt) => {
        var btn = evt.target;
        var $inner = btn.querySelector('#abmTranslationHud-'+btn.getAttribute('data-elementid'));
        var hud = false;
        hud = new Garnish.HUD(btn, $inner, {
            orientations: ['top', 'bottom', 'right', 'left'],
            onHide: function() {
                return false;
            },
            onShow: function(hud) {
                var btn = hud.target.$trigger[0];
                AbmTranslationHelperObject.get_entry_text(btn, hud);
            }
        });
        return false;
        //
    });
});