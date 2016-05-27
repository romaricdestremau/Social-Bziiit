<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
// évènements ponctuels à paris
			$date=  "27/04/2016";
			$date = str_replace("/", "%2F", $date);

			$rue = $_POST["rue"];

			$code = $_POST["codePostal"];

			$ville = $_POST["ville"];

			$rayon = $_POST["rayon"];


			$geocoder = "http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false";

			
			$address = [
			    "Rue" => $rue,
			    "CodePostal" => $code,
			    "Ville"   => $ville,
			    "Lat" => null, 
			    "Lng" => null
			];

			       
			$adresse = $address["Rue"];
			$adresse .= ', '.$address["CodePostal"];
			$adresse .= ', '.$address["Ville"];
			

			// Requête envoyée à l'API Geocoding
			$query = sprintf($geocoder, urlencode(utf8_encode($adresse)));

			$result = json_decode(file_get_contents($query));
			$json = $result->results[0];

			$adress["Lat"]= (string) $json->geometry->location->lat;
			$adress["Lng"] = (string) $json->geometry->location->lng;

			echo $rue . " " . $code . " " . $ville . " " . $rayon;

			echo $adress["Lat"] . " " . $adress["Lng"];
			//$url = "http://datainfolocale.opendatasoft.com/api/records/1.0/search/?dataset=agenda_culturel&rows=15&facet=facette_debut&facet=rubrique&facet=nav_lieu&facet=organisme_nom&facet=organisme_sous_type&geofilter.distance=".$adress["Lat"]."%2C+".$adress["Lng"]."%2C+".$rayon;
			//$url = "https://datainfolocale.opendatasoft.com/api/records/1.0/search/?dataset=infolocale_evenements&rows=13&facet=rubrique&facet=nav_lieu&facet=organisme_sous_type&facet=tags&facet=groupes&geofilter.distance=".$adress["Lat"]."%2C+".$adress["Lng"]."%2C+".$rayon;
			$url = "https://datainfolocale.opendatasoft.com/api/records/1.0/search/?apikey=2cd2a00879adfce955bd9b361c9b030259bee3e8355dbd6c6359d3fc&dataset=infolocale_evenements&rows=300&facet=rubrique&facet=nav_lieu&facet=organisme_sous_type&facet=tags&refine.jour_1=2016-04&facet=groupes&geofilter.distance=".$adress["Lat"]."%2C+".$adress["Lng"]."%2C+".$rayon;
			//$url = "https://datainfolocale.opendatasoft.com/api/records/1.0/search/?apikey=2cd2a00879adfce955bd9b361c9b030259bee3e8355dbd6c6359d3fc&dataset=infolocale_evenements&facet=rubrique&facet=nav_lieu&facet=organisme_sous_type&facet=tags&facet=groupes&geofilter.distance=45.042374%2C+3.890836%2C+30000";
			//  Initiate curl
			$ch = curl_init();
			// Disable SSL verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			// Will return the response, if false it print the response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// Set the url
			curl_setopt($ch, CURLOPT_URL,$url);
			// Execute
			$result=curl_exec($ch);
			// Closing
			curl_close($ch);


			$result = file_get_contents($url);

			// Will dump a beauty json :3
			//var_dump(json_decode($result, true));
			
			$final = json_decode($result, true);
			

			$tablength = count($final["records"]);

			//(1) On inclut la classe de Google Maps pour générer ensuite la carte.
			require('GoogleMapAPI.class.php');

			//(2) On crée une nouvelle carte; Ici, notre carte sera $map.
			$map = new GoogleMapAPI('map');

			//(3) On ajoute la clef de Google Maps.
			$map->setAPIKey('AIzaSyA2ev1OsSiN63asXFC96SFyrqxtEa9y2mU');
			    
			//(4) On ajoute les caractéristiques que l'on désire à notre carte.
			$map->setWidth("800px");
			$map->setHeight("500px");
			$map->setCenterCoords ($adress["Lat"], $adress["Lng"]);
			$map->setZoomLevel (14); 

			for($i=0;$i<$tablength;$i++){
				$map->addMarkerByCoords( $final["records"][$i]["geometry"]["coordinates"][0],  $final["records"][$i]["geometry"]["coordinates"][1], "<titre de l'infobulle>", "<em>contenu</em> de l'infobulle", "<Title du pointeur>");
			}


			//(5) On applique la base XHTML avec les fonctions à appliquer ainsi que le onload du body.
?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="stylesheet.css" />
	<title>Tuto PHP API</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
	<script type="text/javascript" src="script.js"></script>
	<?php $map->printHeaderJS(); ?>
	<?php $map->printMapJS(); ?>
</head>
<body onload="onLoad();">
		<?php $map->printMap(); ?>
	<div id="ponctuels" > 
		Evenements publics : 

<?php
			 $compteur=0;
			for($i=0;$i<$tablength;$i++){
				$compteur+=1;
				echo $compteur . "</br>";
				$tab = $final["records"][$i]["fields"];
				//if(isset($tab['titre']))
				//{
					echo "<h1> nom de l'évènement : " . $tab["titre"] . " </h1> ";
				//}

				if(isset($tab['jour_1'])){
						echo "<h2> date : " . $tab["jour_1"] . "</h2> ";
				}

				if(isset($tab['texte_milieu'])){
							echo "<h3> description : " . $tab["texte_milieu"] . "</h3>";

				}
				if(isset($tab['nav_lieu'])){
					echo "<h3> lieu : " . $tab["nav_lieu"] . "</h3>";
				}
			
				echo "</br> </br>";
			}

?>

</div>

</body>
</html>