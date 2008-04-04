<?php

// Les tables : 
// 1 table descriptive des zones d'acces
// 2 tables de liens zones<->auteurs et  zones<->rubriques

global $tables_principales;
global $tables_auxiliaires;

$spip_paniers = array(
	"id_panier" => "bigint(21) NOT NULL",
	"id_auteur"	=> "bigint(21) NOT NULL",
	"cookie_panier" => "TEXT NOT NULL DEFAULT ''",
	"date_panier" => "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
	"maj" => "TIMESTAMP");

$spip_paniers_key = array(
	"PRIMARY KEY" 	=> "id_panier");

$tables_principales['spip_paniers'] = array(
	'field' => &$spip_paniers,
	'key' => &$spip_paniers_key);

$spip_forms_donnees_paniers = array(
	"id_donnee" 	=> "BIGINT (21) DEFAULT '0' NOT NULL",
	"id_panier" 	=> "BIGINT (21) DEFAULT '0' NOT NULL",
	"quantite" => "BIGINT (21) DEFAULT '1' NOT NULL",
	"rang" => "int NOT NULL"
	);

$spip_forms_donnees_paniers_key = array(
	"KEY id_donnee" 	=> "id_donnee",
	"KEY id_panier" => "id_panier");

$tables_auxiliaires['spip_forms_donnees_paniers'] = array(
	'field' => &$spip_forms_donnees_paniers,
	'key' => &$spip_forms_donnees_paniers_key);

//-- Relations ----------------------------------------------------

global $tables_jointures;
$tables_jointures['spip_paniers'][] = 'forms_donnees_paniers';
$tables_jointures['spip_forms_donnees'][] = 'forms_donnees_paniers';

//-- Table des tables ----------------------------------------------------

global $table_des_tables;
$table_des_tables['paniers']='paniers';
$table_des_tables['forms_donnees_paniers']='forms_donnees_paniers';

?>