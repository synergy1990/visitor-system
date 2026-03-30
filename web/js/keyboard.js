let keyboardTarget = null
let shift = false
let caps = false

let holdInterval = null
let holdTimeout = null

const layout = [
    ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "backspace"],
    ["tab", "q", "w", "e", "r", "t", "z", "u", "i", "o", "p", "ü", "enter"],
    ["caps", "a", "s", "d", "f", "g", "h", "j", "k", "l", "ö", "ä"],
    ["shift", "y", "x", "c", "v", "b", "n", "m", ",", ".", "-", "left", "right"],
    ["space"]
]


/* ========================= */

function createKeyboard() {

    if (document.getElementById("keyboard")) return

    const container = document.createElement("div")
    container.id = "keyboard"


    /* X Button */

    const closeBtn = document.createElement("button")
    closeBtn.id = "kb-close"
    closeBtn.innerText = "X"
    closeBtn.onclick = hideKeyboard
    container.appendChild(closeBtn)


    layout.forEach(row => {
        const rowDiv = document.createElement("div")
        rowDiv.className = "kb-row"

        row.forEach(key => {

            const btn = document.createElement("button")
            btn.className = "kb-key"
            btn.innerText = getLabel(key)
            btn.dataset.key = key

            if (key === "backspace") btn.classList.add("kb-wide2")
            if (key === "space") btn.classList.add("kb-space")
            if (key === "enter") btn.classList.add("kb-wide2")
            if (key === "tab") btn.classList.add("kb-wide2")

            btn.addEventListener("mousedown", function (e) {
                e.preventDefault()
                startHold(key)
            })

            btn.addEventListener("mouseup", stopHold)
            btn.addEventListener("mouseleave", stopHold)

            rowDiv.appendChild(btn)
        })

        container.appendChild(rowDiv)
    })

    document.body.appendChild(container)
}


/* ========================= */

function startHold(key) {

    pressKey(key)

    /* Vibration */

    if (navigator.vibrate) {
        navigator.vibrate(10)
    }

    if (!["backspace", "left", "right"].includes(key)) return

    holdTimeout = setTimeout(() => {
        holdInterval = setInterval(() => {
            pressKey(key)
        }, 80)
    }, 400)

}

function stopHold() {
    clearTimeout(holdTimeout)
    clearInterval(holdInterval)
}


/* ========================= */

function showKeyboard() {
    createKeyboard()
    document.getElementById("keyboard").style.display = "block"
    document.getElementById("registerModal").classList.add("keyboard-open")
}

function hideKeyboard() {
    let kb = document.getElementById("keyboard")
    if (kb) kb.style.display = "none"
    document.getElementById("registerModal").classList.remove("keyboard-open")
}


/* ========================= */

function getLabel(key) {

    if (key === "space") return ""
    if (key === "backspace") return "⌫"
    if (key === "shift") return "Shift"
    if (key === "caps") return "Caps"
    if (key === "enter") return "Enter"
    if (key === "tab") return "Tab"
    if (key === "left") return "←"
    if (key === "right") return "→"

    return key
}


/* ========================= */

function pressKey(key) {

    if (!keyboardTarget) return

    let start = keyboardTarget.selectionStart
    let end = keyboardTarget.selectionEnd
    let value = keyboardTarget.value


    if (key === "shift") {
        shift = !shift
        updateKeys()
        return
    }

    if (key === "caps") {
        caps = !caps
        updateKeys()
        return
    }


    /* Backspace */

    if (key === "backspace") {
        if (start > 0) {
            keyboardTarget.value = value.slice(0, start - 1) + value.slice(end)
            keyboardTarget.selectionStart = keyboardTarget.selectionEnd = start - 1
        }
        keyboardTarget.focus()
        return
    }


    /* TAB / ENTER */

    if (key === "tab" || key === "enter") {
        focusNextInput()
        return
    }


    /* Pfeile */

    if (key === "left") {
        let pos = Math.max(0, start - 1)
        keyboardTarget.selectionStart = keyboardTarget.selectionEnd = pos
        keyboardTarget.focus()
        return
    }

    if (key === "right") {
        let pos = start + 1
        keyboardTarget.selectionStart = keyboardTarget.selectionEnd = pos
        keyboardTarget.focus()
        return
    }


    /* Space */

    if (key === "space") {
        insertText(" ")
        return
    }


    /* Sonderzeichen */

    let specialMap = {
        ",": [";", ","],
        ".": [":", "."],
        "-": ["_", "-"]
    }

    if (specialMap[key]) {

        let upper = (shift && !caps) || (!shift && caps)
        let char = upper ? specialMap[key][0] : specialMap[key][1]

        insertText(char)

        /* 🔥 Shift zurücksetzen wie bei Buchstaben */

        if (shift) {
            shift = false
            updateKeys()
        }

        return
    }


    /* Buchstaben + Umlaute */

    let upper = (shift && !caps) || (!shift && caps)
    let char = upper ? key.toUpperCase() : key.toLowerCase()


    /* Namen */

    if (keyboardTarget.name === "firstname" || keyboardTarget.name === "lastname") {
        let prev = value.slice(0, start)
        if (start === 0 || prev.endsWith(" ")) {
            char = char.toUpperCase()
        }
    }

    insertText(char)

    if (shift) {
        shift = false
        updateKeys()
    }

}


/* ========================= */

function insertText(char) {

    let start = keyboardTarget.selectionStart
    let end = keyboardTarget.selectionEnd
    let value = keyboardTarget.value

    keyboardTarget.value = value.slice(0, start) + char + value.slice(end)
    keyboardTarget.selectionStart = keyboardTarget.selectionEnd = start + char.length

    keyboardTarget.focus()
}


/* ========================= */

function focusNextInput() {

    const inputs = Array.from(document.querySelectorAll("#visitorForm input:not([name='persons'])"))

    let index = inputs.indexOf(keyboardTarget)

    if (index >= 0 && index < inputs.length - 1) {
        inputs[index + 1].focus()
    }

}


/* ========================= */

function updateKeys() {

    document.querySelectorAll(".kb-key").forEach(btn => {

        let key = btn.dataset.key

        /* Reset aktive Styles */

        btn.classList.remove("kb-active")


        /* aktive Shift / Caps hervorheben */

        if (key === "shift" && shift) {
            btn.classList.add("kb-active")
        }

        if (key === "caps" && caps) {
            btn.classList.add("kb-active")
        }


        /* Sonderzeichen */

        if (key === "," || key === "." || key === "-") {

            let upper = (shift && !caps) || (!shift && caps)

            if (key === ",") btn.innerText = upper ? ";" : ","
            if (key === ".") btn.innerText = upper ? ":" : "."
            if (key === "-") btn.innerText = upper ? "_" : "-"

            return
        }


        /* normale Zeichen */

        if (key.length === 1) {

            let upper = (shift && !caps) || (!shift && caps)

            btn.innerText = upper ? key.toUpperCase() : key.toLowerCase()

        }

    })
}

/* ========================= */

document.addEventListener("focusin", e => {

    if (e.target.tagName === "INPUT") {

        /* Felder ohne Tastatur */

        if (e.target.name === "persons" ||
            e.target.id === "pinInput") {
            hideKeyboard()
            return
        }

        /* Highlight */

        document.querySelectorAll("#visitorForm input").forEach(i => {
            i.classList.remove("active-input")
        })

        e.target.classList.add("active-input")

        keyboardTarget = e.target
        showKeyboard()

        /* Scroll */

        setTimeout(() => {
            e.target.scrollIntoView({ behavior: "smooth", block: "center" })
        }, 200)

    }

})
