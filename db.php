<?php
// logic.php

// 1. Das Design-Template einlesen
$template = file_get_contents('index.html');

// 2. 6 verschiedene Lottozahlen ziehen
$gezogeneZahlen = [];
while (count($gezogeneZahlen) < 6) {
    $zufall = rand(1, 42);
    // Verhindert doppelte Zahlen
    if (!in_array($zufall, $gezogeneZahlen)) {
        $gezogeneZahlen[] = $zufall;
    }
}
// Die Zahlen für die Anzeige aufsteigend sortieren
sort($gezogeneZahlen);

// 3. Eine Glückszahl (1-6) ziehen
$gluecksZahl = rand(1, 6);

// 4. Reine HTML-Elemente für die Zahlen generieren
$kugelnHTML = "";
foreach ($gezogeneZahlen as $zahl) {
    $kugelnHTML .= "<div class='ball'>$zahl</div>";
}

// 5. HTML für die Glückszahl generieren
$gluecksHTML = "<div class='ball glueck'>$gluecksZahl</div>";

// 6. Die Platzhalter in der HTML-Datei füllen
$output = str_replace('{{ZUG_ZAHLEN}}', $kugelnHTML, $template);
$output = str_replace('{{ZUG_GLUECK}}', $gluecksHTML, $output);

// 7. Das fertige Ergebnis ausgeben
echo $output;
?>