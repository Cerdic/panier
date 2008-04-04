<?php
/*
 * Catalogue
 * Gestion du catalogue produits
 *
 * Auteurs :
 * Cedric Morin, Yterium.com
 * (c) 2007 - Distribue sous licence GNU/GPL
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;	#securite

// Pas besoin de contexte de compilation
global $balise_FORMULAIRE_DETAIL_PANIER_collecte;
$balise_FORMULAIRE_DETAIL_PANIER_collecte = array('id_form','id_article');

function balise_FORMULAIRE_DETAIL_PANIER ($p) {
	return calculer_balise_dynamique($p,'FORMULAIRE_DETAIL_PANIER', array('id_form', 'id_article'));
}

function balise_FORMULAIRE_DETAIL_PANIER_stat($args, $filtres) {
	return $args;
}

function balise_FORMULAIRE_DETAIL_PANIER_dyn($id_form = 0, $id_article = 0) {
	include_spip('inc/autoriser');
	include_spip('base/forms_base_api');
	//$decrire_panier = charger_fonction('decrire_panier','inc');

	$url = self();
	// nettoyer l'url qui est passee par htmlentities pour raison de securites
	$url = str_replace("&amp;","&",$url);
	if ($retour=='') $retour = $url;

	/*if (!$GLOBALS['auteur_session'])
		return '';*/
		
	$liste_table = Forms_liste_tables('catalogue');
	//$produit = $decrire_panier();
	
	return array('formulaires/detail_panier', 0, 
		array(
			'retour'=>_request('retour')
		));
}

?>
