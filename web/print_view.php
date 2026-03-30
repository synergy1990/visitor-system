<?php

require "db.php";

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit;
}

$data = $db->query("
SELECT id, firstname, lastname, company, contact, persons, checkin, checkout, status
FROM visitors
WHERE date(checkin) >= date('now','-1 day')
ORDER BY checkin DESC
")->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
$present = 0;

foreach ($data as $v) {
    if (date("Y-m-d", strtotime($v['checkin'])) == date("Y-m-d")) {
        $total += (int)$v['persons'];
        if ($v['status'] == "present") {
            $present += (int)$v['persons'];
        }
    }
}

$absent = $total - $present;
$generated = date("d.m.Y H:i");

?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 1.2cm 1.5cm;
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: black;
            background: white;
        }

        /* Kopfzeile */

        .print-header {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            grid-template-rows: auto auto;
            align-items: center;
            margin-bottom: 16px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .print-logo {
            width: 20%;
            height: auto;
            grid-column: 1;
            grid-row: 1;
            justify-self: start;
        }

        .print-title {
            font-size: 28pt;
            font-weight: bold;
            grid-column: 2;
            grid-row: 1;
            text-align: center;
        }

        .print-date {
            font-size: 9pt;
            color: #555;
            grid-column: 3;
            grid-row: 1;
            text-align: right;
            align-self: end;
        }

        .print-stats {
            font-size: 10pt;
            font-weight: bold;
            grid-column: 1 / -1;
            grid-row: 2;
            margin-top: 8px;
        }

        /* Tabelle */

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        th {
            background: #444;
            color: white;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #333;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        td {
            padding: 5px 8px;
            border: 1px solid #aaa;
            vertical-align: middle;
            background: white;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        tr.alt-row td {
            background: #d8d8d8;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .status-dot {
            font-size: 14pt;
        }
    </style>
</head>
<body>

<div class="print-header">
    <img src="firmenlogo.png" class="print-logo">
    <div class="print-title">Besucherliste</div>
    <div class="print-date">Erstellt: <?php echo $generated; ?></div>
    <div class="print-stats">
        Gesamtbesucher: <?php echo $total; ?>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Anwesend: <?php echo $present; ?>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Abwesend: <?php echo $absent; ?>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Status</th>
            <th>Name</th>
            <th>Firma</th>
            <th>Personen</th>
            <th>Ansprechpartner</th>
            <th>Check-In</th>
            <th>Check-Out</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $i => $v): ?>
        <tr <?php echo $i % 2 !== 0 ? 'class="alt-row"' : ''; ?>>
            <td>
                <span class="status-dot" style="color:<?php echo $v['status'] === 'present' ? 'green' : 'red'; ?>">●</span>
            </td>
            <td><?php echo htmlspecialchars($v['lastname'] . ', ' . $v['firstname']); ?></td>
            <td><?php echo htmlspecialchars($v['company'] ?? ''); ?></td>
            <td><?php echo (int)$v['persons']; ?></td>
            <td><?php echo htmlspecialchars($v['contact'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($v['checkin']); ?></td>
            <td><?php echo htmlspecialchars($v['checkout'] ?? '-'); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
