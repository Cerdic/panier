<?php

function panier_insert_head($flux){
	$flux .= '<link rel="stylesheet" href="'.find_in_path('panier.css').'" type="text/css" media="projection, screen" />';
	$flux .= "<script type='text/javascript' src='".generer_url_public('panier.js','lang='.$GLOBALS["spip_lang"])."'></script>\n";
	return $flux;
}


function panier_bank_enregistre_reglement($flux){
	spip_log('vidange cookies panier');
	include_spip('panier_options');
	panier_delcookie('panier');
	// garder la reference au panier invalide qui permettra de le supprimer
	/*panier_delcookie('id_panier');
	panier_delcookie('id_panier_key');*/
	if (!$flux['args']['new']){
		return $flux;
	}

	$id_transaction = $flux['args']['id_transaction'];
	$id_facture = $flux['args']['id_facture'];
	// retrouver l'auteur
	$res = spip_query("SELECT id_auteur FROM spip_factures WHERE id_facture="._q($id_facture));
	if (($row = spip_fetch_array($res))
	 && ($id_auteur = $row['id_auteur'])){
		spip_log('vidange paniers en base');
		// recup tous les paniers de cet auteur
		$res = spip_query("SELECT id_panier FROM spip_paniers WHERE id_auteur="._q($id_auteur));
		while ($row = spip_fetch_array($res)){
			// supprimer tous les produits associes
			spip_query("DELETE FROM spip_forms_donnees_paniers WHERE id_panier="._q($row['id_panier']));
		}
	 	// supprimer tous les paniers de cet auteur
		spip_query("DELETE FROM spip_paniers WHERE id_auteur="._q($id_auteur));
	}
	return $flux;
}

?>
