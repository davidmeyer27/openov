<!DOCTYPE html>
<html dir="ltr" lang="nl">
<head>
<meta name="viewport" content="width=device-width,initial-scale=1.0;">
<style type="text/css">
body {
	padding: 1em;
	font-family: Georgia, Times, serif;
	background-color: #fff;
	color:#333;
	font-size:1em;
}

.hoelaat {
	padding-bottom:1em;
}

.schema {
	padding:2% 0;
}

@media (min-width: 50em) {
.schema {
	width: 48%;
	float:left;
	padding:0 2% 0 0;
}
}

h1 {
	font:bold 120%/1.3 Arial, Helvetica, sans-serif;
	text-transform:uppercase;
	letter-spacing:.5px;
}

h1 em {
	font-style:normal;
	color:red;
}

h2 {
	font-size:150%;
	font-weight:normal;
}

h2 strong {
	font-weight:normal;
	background-color:#FF3;
}

.bijzaak {
	color:#999;
}
</style>
<title>Komt de bus al?</title>
</head>
<body>
<?php 
// Haal huidige datum en tijd op
$nu2 = date('H:i');
echo("<div class=\"hoelaat\"><em>Het is nu " . $nu2 . " uur</em></div>\n");
?>
<div class="schema">
	<h1>Halte Juliana van Stolberglaan <br><em>richting Scheveningen</em></h1>
	<?php	

function ophalen($halteNummer, $lijnNummer) {
	// De lijninformatie wordt binnengehaald met curl
	$service_url = 'http://v0.ovapi.nl/tpc/' . $halteNummer . '/departures';
	$curl = curl_init($service_url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$curl_response = curl_exec($curl);
	if ($curl_response === false) {
		$info = curl_getinfo($curl);
		curl_close($curl);
		die('error occured during curl exec. Additional info: ' . var_export($info));
	}
	curl_close($curl);
	// Het JSON-bestand wordt omgezet naar een PHP array
	$decoded = json_decode($curl_response,true);
	
	if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
		die('Er ging iets fout: ' . $decoded->response->errormessage);
	}
	// Hierin zit DE informatie
	$alletrams = $decoded[$halteNummer]["Passes"];
	// De array opnieuw sorteren op ExpectedArrivalTime
	$tijdstippen = array();
	foreach ($alletrams as $tram) {
		$tijdstippen[] = $tram['ExpectedArrivalTime'];
	}
	array_multisort($tijdstippen, SORT_ASC, $alletrams); 
	echo("<h2>Lijn <strong>" . $lijnNummer . "</strong> wordt verwacht om: </h2>\n<ul>");
	// Door de array lopen en de tijdstippen tonen
foreach ($alletrams as $row) {
		// Deze gebruik je om de aankomsttijd weer te geven
		$tijd = date_parse($row['ExpectedArrivalTime']);
		// Hiermee bereken je het aantal minuten
		$rekentijd = strtotime($row['ExpectedArrivalTime']);
		$nu = strtotime(date("Y-m-d\TH:i:s"));
		if ($nu<$rekentijd) {
			$verschil = round(abs($nu - $rekentijd) / 60,0);
		};		// Toon alle trams die binnen 3 kwartier komen
		if($row['LinePlanningNumber']== $lijnNummer && $verschil <= 59) {
			if ($verschil == 0) {
			echo("<li><strong>NU!</strong></li>");
			} else if ($verschil == 1) {
			echo("<li>" . $tijd[hour] . ":" . sprintf("%'.02d", $tijd[minute]) . " uur <em>(over <strong>" . $verschil . "</strong> minuut. Rennen!)</em></li>\n");
			} else {
				echo("<li>" . $tijd[hour] . ":" . sprintf("%'.02d", $tijd[minute]) . " uur <em>(over <strong>" . $verschil . "</strong> minuten)</em></li>\n");
			}
		};
	};

echo("</ul>\n");
};

ophalen("32002649", "23");

?>
</div>
<div class="schema">
	<h1>Halte Juliana van Stolberglaan <br><em>richting Kijkduin</em></h1>
	
	<?php	
$alletrams = ophalen("32002650", "23");

?>
</div>
</body>
</html>