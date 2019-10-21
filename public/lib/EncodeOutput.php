<?php
	function render_json($data) {
		$encoded = json_encode($data);
		if(FALSE !== $encoded) {
			header('Content-Type: application/json');
			echo $encoded;
		} else {
			http_response_code(500);
			die(json_last_error_msg());
		}
	}
?>