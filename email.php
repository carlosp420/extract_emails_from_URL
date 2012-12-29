<?php

function get_from_URL($url){
	// use cURL, safer than simplexml_load_file
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

	$result = curl_exec($ch);

	curl_close($ch);

	return $result;
}

function get_email($url) {
	$email_pattern = "/(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))/i";
	
	$raw = get_from_URL($url);
	
	$newlines = array("\t","\n","\r","\x20\x20","\0","\x0B");
	$content = str_replace($newlines, "----", html_entity_decode($raw));
	$content2 = preg_replace("/\s/", "-", $content);
	$content2 = preg_replace("/\-+/", "|", $content2);
	$content2 = explode("|", $content2);
	
	$emails = array();
	foreach( $content2 as $line) {
		preg_match("/@/", $line, $match);
		if( count($match) > 0 ) {
			preg_match_all($email_pattern, $line, $email_match);
			if( count($email_match) > 0 ) {
				foreach($email_match[0] as $key=>$email) {
					$emails[] = $email;
				}
			}
		}
	}
	
	return $emails;
}

$my_found_emails = array();
if( count($_POST) > 0) {
	if( $_POST['urls'] != "" ) {
		$urls = explode("\n", $_POST['urls']);
		foreach($urls as $url) {
			$url = trim($url);
			$tmp = get_email($url);
			foreach($tmp as $item) {
				$my_found_emails[] = $item;
			}
		}
		$my_found_emails = array_unique($my_found_emails);

		if( count($my_found_emails) > 0 )  {
			echo "I found these emails:<br/><br/>";
			foreach( $my_found_emails as $i) {
				echo $i . "<br/>";
			}
		}
		else {
			echo "Sorry, couldn't find any email in those pages";
		}
	}
	else {
		echo "<html><body>";
		echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='post'>";
		echo "<table border='1' >";
		echo "<caption>Paste URL addresses, one in each line</caption";
		echo "<tr><td><textarea name='urls' rows='20' cols='60'></textarea></td></tr>";
		echo "</table>";
		echo "<input type='submit' name='get_emails' value='Get me emails!' />";
		echo "</form>";
		echo "</body></html>";
	}
}
else {
	echo "<html><body>";
	echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='post'>";
	echo "<table border='1' >";
	echo "<caption>Paste URL addresses, one in each line</caption";
	echo "<tr><td><textarea name='urls' rows='20' cols='60'></textarea></td></tr>";
	echo "</table>";
	echo "<input type='submit' name='get_emails' value='Get me emails!' />";
	echo "</form>";
	echo "</body></html>";
}
?>
