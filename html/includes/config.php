	<?php

	define('BASE_PATH', $_SERVER['DOCUMENT_ROOT']);

	$xml_path = BASE_PATH.'/config.xml';

	if (!file_exists($xml_path)) 
	{
		echo "Error en lectura xml config";
		exit();	
	}

	$Gd_xml_config = simplexml_load_file($xml_path);

	// Detras de Traefik: siempre HTTPS hacia el cliente
	$base_home = 'https://' . $_SERVER['HTTP_HOST'] . '/';
	
	?>