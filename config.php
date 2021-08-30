<?php
/**
 * Fichier de configuration. Il est appelé par index.php
 * Il contient notamment l'autoloader
 * @author Jonathan Martel
 * @version 1.0
 * @update 2016-03-31
 * @license MIT
 * 
 */
	
	include_once('db_info.php');
	
	/* function mon_autoloader($class) 
	{
		$dossierClasse = array('modeles/', 'vues/', 'lib/', 'lib/mysql/', '' );	// Ajouter les dossiers au besoin
		
		foreach ($dossierClasse as $dossier) 
		{
			//var_dump('./'.$dossier.$class.'.class.php');
			if(file_exists('./'.$dossier.$class.'.class.php'))
			{
				require_once('./'.$dossier.$class.'.class.php');
			}
		}
		
	  
	} */

	function autoloader($class) {
		$appNamespace = "TodoWS";

		// Retirer le namespace de l'application pour la recherche du fichier
		$class = str_replace($appNamespace, "", $class);

		$class = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.class.php';

		$path = __DIR__ . $class;

		if (!file_exists($path)) {
			var_dump($path);
			return;
		}

		require($path);
	}
	
	spl_autoload_register('autoloader');