<?php
require_once 'classes/twitter.class.php';
require_once 'functions/functions.php';
require_once 'config/keys.php';
require_once 'config/config.php';

$Twitter = new Twitter($keys['consumer']['key'],
		       $keys['consumer']['secret'], 
		       $keys['access']['token'], 
		       $keys['access']['secret']);

//Get trending hashtags, package them up into an array.
try { 
	$data = $Twitter->getTrends(23424977); // I cant remember what these numbers mean for the life of me.
	$trends = array(); 
	$ii = 0; 
	while( $ii < 10) { 
		if (substr($data[0]->trends[$ii]->name, 0, 1) === '#') { 
			$trends[] = str_replace(' ', '', $data[0]->trends[$ii]->name); 
		} else { 
			$trends[] = $data[0]->trends[$ii]->name; 
		} 
		$ii++; 
	} 
} catch (TwitterException $e) {
	echo '<br />Error: ' . $e->getMessage(); 
}

//Set tags.
$Tags['Trending'] = array_random($trends);
$Tags['Static'] = "#" . array_random(file("data/hashtags.txt"));


//Get content.
/* //This is if you want to do a cleverbot like creation.
$Content = file_get_contents("http://virulent.pw/bot.php?SnoopStori=True"); 
if(empty($Content)) { 
	while(empty($Content)) { //If no content loop until there is. This is dirty.
		$Content = file_get_contents("http://virulent.pw/bot.php?SnoopStori=True"); 
	}
}

$ContentBackup = $Content; //Make a backup of our post for verbose.
$Content = snoopify($Content); // Use my unofficial API to change to snoop like text.
*/

$Content = array_random(file("data/sayings.txt"));
$ContentBackup = $Content; //Make a backup of our post for verbose.

/* * * Main Management * * */
$PostArray['Main'] = explode(" ", $Content); //Make an array of our post content.
$PostArray['Backup'] = $PostArray['Main'];   //Make a backup of our post for verbose.
$PostArray['Hashtaglets'] = array_random($PostArray['Main'], rand(1, round(count($PostArray['Main']) / 2))); //Choose 1 or 2 words from array for hashtagging

//Prevent errors if only 1 word is chosen, create array
if(!is_array($PostArray['Hashtaglets'])) { 
	$PostArray['Hashtaglets'] = array($PostArray['Hashtaglets']); 
} 

//Hashtag chosen content, as long as its greater than 2 characters long. 
//(This prevents Issues like the hashtag '#is' changing the word 'this' to 'th#is')
foreach ($PostArray['Hashtaglets'] as $c) { 
	if(strlen($c) > 2) { 
		$PostArray['Main'] = str_replace($c, "#" . $c, $PostArray['Main']); 
	}
} 

//Remove double hashtags.
foreach ($PostArray['Main'] as $a) { 
	//$a is unused for some stupid reason.
	$PostArray['Main'] = str_replace("##", "#", $PostArray['Main']); 
} 

// Random chance to add a non-related hashtag. 
//If true, 1/3 chance for a static tag, 2/3 chance for a trending tag.
if(rand(0,1) == 1) { 
	if(rand(0,2) == 0) { 
		array_push($PostArray['Main'], $Tags['Static']); 
		$RandomTag = $Tags['Static'];
	} else { 
		//Trending tags a 2/3 chance of being used
		array_push($PostArray['Main'], $Tags['Trending']); 
		$RandomTag = $Tags['Trending'];
	} 
}

//Reassign to $Content.
$Content = strip_tags(implode(" ", $PostArray['Main']));

//Make sure its not over 140 characters.
if(strlen($Content) > 140) { 
	$Content = substr($Content, 0, 140); 
	$Trim = True;
}

/* * * Management Done * * */

//Info
echo "<b>Post</b>:<br/>";
echo "<br/>Post Text: " . $Content . "<br />";
echo "<br />Hashtagged text: ";
print_r($chosen);
if(!empty($RandomTag) && isset($RandomTag)) { 
	echo "<br />A hashtag was added.<br />Hashtag: " . $RandomTag; 
}
if($Trim) {
	echo "Post was longer than 140 characters. It has been trimmed.<br/>";
}

echo "Result: ";
try {
	$tweet = $Twitter->send($Content);
	echo "<br />Posted: " . $Content;
} catch (TwitterException $e) {
	echo '<br />Error: ' . $e->getMessage();
}

?>
