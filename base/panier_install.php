<?php
/*
 * Panier
 * Gestion du panier
 *
 * Auteurs :
 * Cedric Morin, Yterium.com
 * (c) 2007 - Distribue sous licence GNU/GPL
 *
 */
	include_spip('inc/meta');
	include_spip('base/abstract_sql');
	include_spip('base/create');
	
	function panier_upgrade($nom_meta_base_version,$version_cible){
		$current_version = 0.0;
		if (   (!isset($GLOBALS['meta'][$nom_meta_base_version]) )
				|| (($current_version = $GLOBALS['meta'][$nom_meta_base_version])!=$version_cible)){
			if ($current_version==0.0){
				if (include_spip('base/panier')){
					creer_base();
					echo "Panier Install<br/>";
					ecrire_meta($nom_meta_base_version,$current_version=$version_cible,'non');
				}
				else return;
			}
			if (version_compare($current_version,'0.101','<')){
				spip_query("ALTER TABLE spip_forms_donnees_paniers ADD rang int NOT NULL DEFAULT 0 AFTER quantite");
				ecrire_meta($nom_meta_base_version,$current_version='0.101','non');
			}
			ecrire_metas();
		}
	}
	
	function panier_vider_tables($nom_meta_base_version) {
		spip_query("DROP TABLE spip_paniers");
		spip_query("DROP TABLE spip_forms_donnees_paniers");
		effacer_meta($nom_meta_base_version);
		ecrire_metas();
	}

?>
