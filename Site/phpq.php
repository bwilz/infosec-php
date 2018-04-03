<?php
if(isset($_POST)){
	$term = $_POST['search'];
	
function get_data($search){
	curl_setopt($search, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($search, CURLOPT_HEADER, 0);
	$r = curl_exec($search);
	curl_close($search);
	$r = json_decode($r, true);
	
	return $r;
	
}

//checks if input is a number
if(is_numeric($term)){
	echo "Sorry, no numbers";
	return;
}else{
	if(strlen($term) <= 1){
		echo 'Search term too short';
		return;
	}elseif(strlen($term) <= 3){
		//search using code
		$name = false;
		$search = curl_init('https://restcountries.eu/rest/v2/alpha/' . $term . '?fields=name;alpha2Code;alpha3Code;flag;region;subregion;population;languages;');
		$results = get_data($search);
	}else{
		//search using full name
		$name = true;
		$search = curl_init('https://restcountries.eu/rest/v2/name/' . $term . '?fullText=true&fields=name;alpha2Code;alpha3Code;flag;region;subregion;population;languages;');
		$results = get_data($search);
	}
}


//check if results were returned
if(isset($results['status'])){
	//check if search was for fullname
	if($name == true){
		//if it was then try it again as a partial name
		$search = curl_init('https://restcountries.eu/rest/v2/name/' . $term . '?fields=name;alpha2Code;alpha3Code;flag;region;subregion;population;languages;');
		$results = get_data($search);
	}else{
		echo "Sorry, no results found";
		return;
	}
}

$c_total = 0;
$singleRegion = [];
$singleSubRegion = [];

$output = "<ul>
						<li class='col-1'><h2>Country Name</h2></li>
						<li class='col-2'><h2>Alpha Code 2</h2></li>
						<li class='col-3'><h2>Alpha Code 3</h2></li>
						<li class='col-4'><h2>Flag</h2></li>
						<li class='col-5'><h2>Region</h2></li>
						<li class='col-6'><h2>Subregion</h2></li>
						<li class='col-7'><h2>Population</h2></li>
						<li class='col-8'><h2>Languages</h2></li>
					</ul>";

if($name == true){
//sorting data by name and population
foreach ($results as $key => $row) {
    $c_name[$key]  = $row['name'];
    $c_pop[$key] = $row['population'];
}
array_multisort($c_name, SORT_ASC, $c_pop, SORT_ASC, $results);

	foreach($results as $c){
		//some regions and subregions were coming back blank, this replaces them with Unknown
		if($c['region'] == ''){
			$c['region'] = "Unkown";
		}
		if($c['subregion'] == ''){
			$c['subregion'] = "Unkown";
		}
		
		if(array_key_exists($c['region'], $singleRegion)){
			$singleRegion[$c['region']] ++;
		}else{
			$singleRegion[$c['region']] = 1;
		}
		
		if(array_key_exists($c['subregion'], $singleSubRegion)){
			$singleSubRegion[$c['subregion']] ++;
		}else{
			$singleSubRegion[$c['subregion']] = 1;
		}		
		
		
		$output .= "<ul>
								<li>" . $c['name'] . "</li>
								<li>" . $c['alpha2Code'] . "</li>
								<li>" . $c['alpha3Code'] . "</li>
								<li><img alt='flag' src='" . $c['flag'] . "'></li>
								<li>" . $c['region'] . "</li>
								<li>" . $c['subregion'] . "</li>
								<li>" . number_format($c['population']) . "</li>
								<li>";
								foreach($c['languages'] as $l){
									$output .= $l['name'] . "<br>";
								}
			$output .= "</li>
							</ul>";
		$c_total++;
		if($c_total == 50){
			break;
		}
	}

}else{
		$c_total++;
		$singleRegion[$results['region']] = 1;
		$singleSubRegion[$results['subregion']] = 1;
		
		$output .= "<ul>
								<li>" . $results['name'] . "</li>
								<li>" . $results['alpha2Code'] . "</li>
								<li>" . $results['alpha3Code'] . "</li>
								<li><img alt='flag' src='" . $results['flag'] . "'></li>
								<li>" . $results['region'] . "</li>
								<li>" . $results['subregion'] . "</li>
								<li>" . number_format($results['population']). "</li>
								<li>";
								foreach($results['languages'] as $l){
									$output .= $l['name'] . "<br>";
								}
			$output .= "</li>
							</ul>";
	
}

$output .= "<div class='totals'>
						<h2>Total Countries</h2>
						<p>" . $c_total . "</p>
				  </div>
				  <div class='totals'>
						<h2>Total Regions</h2>
						<ul>";
						foreach($singleRegion as $key => $value){
							$output .= "<li>" . $key . ": " . $value ."</li>";
						}
$output .= "</ul>
				</div>
				<div class='totals'>
					<h2>Total Subregions</h2>
						<ul>";
						foreach($singleSubRegion as $key => $value){
							$output .= "<li>" . $key . ": " . $value ."</li>";
						}
$output .= "</ul>
				</div>";						

echo $output;


}else{
	echo "No search term entered";
}

?>