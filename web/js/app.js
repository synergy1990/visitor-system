/* Besucherformular öffnen */

function openRegister() {
    document.getElementById("registerModal").style.display = "block"

    /* Fokus auf Vorname */

    setTimeout(() => {
        let first = document.querySelector('input[name="firstname"]')
        if (first) {
            first.focus()
        }
    }, 100)

    showKeyboard()
}

function closeRegister() {
    document.getElementById("registerModal").style.display = "none"
    hideKeyboard()
}


/* Sicherheitshinweise anzeigen */

function showSafety() {
    hideKeyboard()
    document.getElementById("safetyModal").style.display = "block"

}

function closeSafety() {

    document.getElementById("safetyModal").style.display = "none"

}


/* Besucher speichern */

function saveVisitor() {

    let form = document.getElementById("visitorForm")

    fetch("save_visitor.php", {
        method: "POST",
        body: new FormData(form)
    })
        .then(() => {

            alert("Besucher erfolgreich angemeldet")

            closeSafety()
            closeRegister()

            form.reset()

            if (document.getElementById("visitorList")) {
                loadVisitors()
            }

        })
    hideKeyboard()
}


/* Besucheranzahl */

function changePersons(val) {

    let input = document.getElementById("persons")

    let n = parseInt(input.value)

    n += val

    if (n < 1) n = 1

    input.value = n

}


/* Login / Logout */

function loginAction() {

    let btn = document.getElementById("loginBtn")

    if (btn.innerText === "Logout") {

        fetch("logout.php").then(() => location.reload())

    } else {

        document.getElementById("loginModal").style.display = "block"
        createPinPad()

    }

}

function closeLogin() {
    document.getElementById("loginModal").style.display = "none"
}


/* PIN PAD */

let pin = ""

function createPinPad() {

    let pad = document.getElementById("pinpad")

    pad.innerHTML = ""

    let layout = [
        1, 2, 3,
        4, 5, 6,
        7, 8, 9,
        "⌫", 0, "C"
    ]

    layout.forEach(key => {

        let b = document.createElement("button")

        b.className = "pinbtn"
        b.innerText = key

        if (key === "⌫") {

            b.onclick = () => {
                pin = pin.slice(0, -1)
                document.getElementById("pinInput").value = pin
            }

        } else if (key === "C") {

            b.onclick = () => {
                pin = ""
                document.getElementById("pinInput").value = ""
            }

        } else {

            b.onclick = () => addPin(key)

        }

        pad.appendChild(b)

    })

}


function addPin(n) {

    pin += n

    document.getElementById("pinInput").value = pin

    if (pin.length === 4) {

        fetch("login.php", {
            method: "POST",
            body: new URLSearchParams({ pin: pin })
        })
            .then(r => r.text())
            .then(res => {

                if (res === "ok") {

                    location.reload()

                } else {

                    alert("Falsche PIN")

                    pin = ""
                    document.getElementById("pinInput").value = ""

                }

            })

    }

}


/* Anwesenheitsliste laden */

function loadVisitors() {

    fetch("get_visitors.php")
        .then(r => r.json())
        .then(data => {

            let html = ""

            data.visitors.forEach(v => {

                let color = v.status === "present" ? "green" : "red"

                html += `
<div class="row" onclick="openStatusModal(${v.id})">

<div class="status">
<span style="color:${color};font-size:40px;">●</span>
</div>

<div class="info">

<div><b>Vorname:</b> ${v.firstname}</div>
<div><b>Nachname:</b> ${v.lastname}</div>
<div><b>Firma:</b> ${v.company}</div>
<div><b>Ansprechpartner:</b> ${v.contact}</div>
<div><b>Anzahl Personen:</b> ${v.persons}</div>
<div><b>Check-In:</b> ${v.checkin}</div>
<div><b>Check-Out:</b> ${v.checkout ?? "-"}</div>

</div>

</div>
`

            })

            document.getElementById("visitorList").innerHTML = html


            /* Statistik */

            let s = data.stats

            document.getElementById("stats").innerHTML =
                "<b>Gesamtbesucher:</b> " + s.total +
                " &nbsp;&nbsp; <b>Anwesend:</b> " + s.present +
                " &nbsp;&nbsp; <b>Abwesend:</b> " + s.absent

        })

}


/* Status ändern */

let selectedVisitor = null

function openStatusModal(id) {

    selectedVisitor = id
    document.getElementById("statusModal").style.display = "block"

}

function closeStatusModal() {

    document.getElementById("statusModal").style.display = "none"

}

function confirmStatus() {

    fetch("change_status.php", {
        method: "POST",
        body: new URLSearchParams({ id: selectedVisitor })
    })
        .then(() => {

            closeStatusModal()
            loadVisitors()

        })

}


/* Auto Logout */

let idleTimer

function startIdleTimer() {

    document.addEventListener("mousemove", resetTimer)
    document.addEventListener("click", resetTimer)
    document.addEventListener("keypress", resetTimer)

    resetTimer()

}

function resetTimer() {

    clearTimeout(idleTimer)

    idleTimer = setTimeout(() => {
        fetch("logout.php").then(() => location.reload())
    }, 30000)

}
