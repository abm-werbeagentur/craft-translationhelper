class AbmTranslationHelperClass {

    constructor () {
    }

    get_entry_text (btn, hud) {
        if (typeof hud !== 'object')
            return

        const hudTarget = hud.target

        // plaintext fields?
        const plaintext = Boolean(btn.getAttribute('data-plaintext'))

        const data = {
            // title fields
            titlefield: Boolean(btn.getAttribute('data-titlefield')),
            namespace: btn.getAttribute('data-namespace'),
            // other elements
            elementid: btn.getAttribute('data-elementid'),
            elementuid: btn.getAttribute('data-elementuid'),
            originalsiteid: btn.getAttribute('data-originalsiteid'),
            elementcontext: btn.getAttribute('data-elementcontext'),
            handle: btn.getAttribute('data-handle'),
            plaintext: plaintext,
        }

        data[Craft.csrfTokenName] = Craft.csrfTokenValue

        Craft.postActionRequest('abm-translationhelper/element/fetch', data, $.proxy(function (response, textStatus) {
            if (textStatus === 'success') {
                // const data = JSON.parse(response);
                const abmtranslationhelperModalContent = '<div class="hud-header">' + response.headline + '</div><div class="abmtranslationhelperHudBody body" style="white-space: pre-line">' + response.value + '</div><div class="hud-footer"><div class="flex"><button class="btn copyClipboard">' + translations.abmtranslationhelper.copy_to_clipbard + '</button><button class="btn cancel abm-hud-closer">' + translations.abmtranslationhelper.close + '</button></div></div>'
                hudTarget.updateBody(abmtranslationhelperModalContent)
                hudTarget.updateSizeAndPosition(true)

                hud.target.$body[0].querySelector('button.abm-hud-closer').addEventListener('click', (evt) => {
                    evt.preventDefault()
                    hud.target.hide()
                })

                hud.target.$body[0].querySelector('div.hud-footer button.copyClipboard').addEventListener('click', async () => {

                    const copyTextarea = hud.target.$body[0].querySelector('div.abmtranslationhelperHudBody.body')
                    const copyText = plaintext ? copyTextarea.innerText : copyTextarea.innerHTML
                    const type = plaintext ? 'text/plain' : 'text/html'
                    const clipboardItemData = {
                        [type]: copyText
                    }
                    const clipboardItem = new ClipboardItem(clipboardItemData)
                    await navigator.clipboard.write([clipboardItem])

                    alert(translations.abmtranslationhelper.copied_to_clipboard)

                })
            } else {
                console.error(xhr.statusText)
            }
        }, this))
    }
}

var AbmTranslationHelperObject = new AbmTranslationHelperClass()

// Function to attach event handlers to translation helper buttons
function attachTranslationHelperHandlers () {

    const $translationHelperButtons = document.querySelectorAll('button.abmtranslationhelper:not([data-initialized="true"])')
    $translationHelperButtons.forEach((btn) => {
        btn.dataset.initialized = 'true'
        btn.addEventListener('click', (evt) => {
            const btn = evt.target
            const $inner = btn.querySelector('#abmTranslationHud-' + btn.getAttribute('data-elementid'))
            let hud = new Garnish.HUD(btn, $inner, {
                orientations: ['top', 'bottom', 'right', 'left'],
                onHide: function () {
                    return false
                },
                onShow: function (hud) {
                    var btn = hud.target.$trigger[0]
                    AbmTranslationHelperObject.get_entry_text(btn, hud)
                }
            })
            return false
        })

    })
}

// Watch for DOM changes to handle dynamically added buttons
const observer = new MutationObserver(() => {
    attachTranslationHelperHandlers()
})

observer.observe(document.body, {
    childList: true,
    subtree: true
})

attachTranslationHelperHandlers()