<?php require "db.php"; ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/keyboard.css">
<script src="js/keyboard.js"></script>
    <script src="js/app.js"></script>
</head>

<body>
    <img src="firmenlogo.png" class="logo">
    <div class="title">Besucheranmeldung</div>
    <button id="loginBtn" class="loginbutton" onclick="loginAction()">
        <?php echo isset($_SESSION['user']) ? "Logout" : "Login"; ?>
    </button>
    <?php if (!isset($_SESSION['user'])): ?>
    <button class="button" onclick="openRegister()">Anmelden</button>
    <div class="notice">
        <b><u>Datenschutzhinweis:</b></u><br><br> Mit Klick auf "Akzeptieren & Speichern" haben Sie die
        Sicherheitshinweise verstanden und akzeptieren die Verhaltensregeln unserer Firma.<br><br> Die Daten werden am
        übernächsten Werktag wieder gelöscht.<br>
    </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['user'])): ?>
    <h2>Anwesenheitsliste</h2>
    <div id="stats"></div>
    <br>
<button class="button" onclick="window.open('print_view.php')">Drucken</button>
    <div id="visitorList"></div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            loadVisitors();
            startIdleTimer();
        });
    </script>
    <?php endif ?>
    <!-- Besucher Formular -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRegister()">X</span>
            <h2>Besucher anmelden</h2>
            <form id="visitorForm">
                <div class="formgrid">
                    <div class="field">
                        <label>Vorname</label>
                        <input name="firstname" required>
                    </div>
                    <div class="field">
                        <label>Nachname</label>
                        <input name="lastname" required>
                    </div>
                    <div class="field">
                        <label>Firma (leer = Privatperson)</label>
                        <input name="company">
                    </div>
                    <div class="field persons">
                        <label>Anzahl Personen (gesamt)</label>
                        <div class="counter">
                            <button type="button" onclick="changePersons(-1)">-</button>
                            <input id="persons" name="persons" type="text" value="1" readonly inputmode="none">
                            <button type="button" onclick="changePersons(1)">+</button>
                        </div>
                    </div>
                    <div class="field full">
                        <label>Ansprechpartner</label>
                        <input name="contact">
                    </div>
                    <div class="field full center">
                        <button type="button" class="button" onclick="showSafety()">Weiter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Sicherheitshinweise Modal -->
    <div id="safetyModal" class="modal">
        <div class="modal-content fullscreen">
            <span class="close" onclick="closeSafety()">X</span>
            <iframe src="sicherheitshinweise.pdf#toolbar=0&navpanes=0&scrollbar=0&zoom=120"
                class="safetyframe"></iframe>
            <div class="safetyfooter">
                <button class="button" onclick="saveVisitor()">Akzeptieren & Speichern</button>
            </div>
        </div>
    </div>
    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLogin()">X</span>
            <h2>PIN eingeben</h2>
            <input id="pinInput" class="pinfield" readonly inputmode="none">
            <div id="pinpad" class="pinpad"></div>
        </div>
    </div>
    <!-- Status Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeStatusModal()">X</span>
            <h2>Status ändern?</h2>
            <button class="button" onclick="confirmStatus()">Ja</button>
            <button class="button" onclick="closeStatusModal()">Nein</button>
        </div>
    </div>
</body>

</html>