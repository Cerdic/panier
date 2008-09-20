<?php
define('_PANIER_EPHEMERE_TTL',24*3600); // duree des paniers pour les visiteurs non enregistres (sur foi du cookie seul)
define('_PANIER_ENREGISTRES_TTL',7*24*3600); // duree des paniers pour les visiteurs authentifies (sur foi de son authent)
if (!isset($GLOBALS['spip_pipeline']['panier_actualise_contenu']))
	$GLOBALS['spip_pipeline']['panier_actualise_contenu'] = '';

function panier_setcookie($nom,$valeur){
	static $expire = NULL;
	if ($expire==NULL) $expire = time()+_PANIER_EPHEMERE_TTL;
	include_spip('inc/cookie');
	spip_setcookie($nom,$_COOKIE[$nom] = $valeur, $expire);
}
function panier_delcookie($nom){
	include_spip('inc/cookie');
	spip_setcookie($nom,"", 0);
	unset($_COOKIE[$nom]);
}

function panier_calcule_cle($id_panier,$id_auteur){
	// soit le panier est encore anonyme sinon l'auteur doit correspondre
	// on n'accepte pas le cas auteur deconnecte, panier identifie
	$res = spip_query("SELECT id_panier,id_auteur,date_panier FROM spip_paniers WHERE id_panier="._q($id_panier)." AND (id_auteur=0 OR id_auteur="._q($id_auteur).")");
	if (!$row = spip_fetch_array($res)) return false;
	return md5(implode(';',array_values($row)));
}

function panier_mise_a_jour_cle($id_panier,$id_auteur){
	$cle = panier_calcule_cle($id_panier,$id_auteur);
	panier_setcookie('id_panier',$id_panier);
	panier_setcookie('id_panier_key',$cle);
}

function panier_id_panier_encours() {
	$id_panier = 0;
	$id_auteur = isset($GLOBALS['auteur_session']['id_auteur'])?$GLOBALS['auteur_session']['id_auteur']:0;
	// on prend en priorite le panier existant en memoire
	// l'id et sa cle de verif doivent etre present dans les cookies et coherents sinon on oublie le panier
	if (isset($_COOKIE['id_panier']) && isset($_COOKIE['id_panier_key']) && isset($_COOKIE['panier'])){
		$id_panier = $_COOKIE['id_panier'];
		$key = $_COOKIE['id_panier_key'];
		if (!($cle = panier_calcule_cle($id_panier,$id_auteur))
		  && $key !== $cle) {
		 	$id_panier = 0;
		 	panier_delcookie('id_panier');
		 	panier_delcookie('id_panier_key');
		 	panier_delcookie('panier');
		 	spip_log('panier errone, id_panier_key invalide');
		}
	}
	if (!$id_panier && $id_auteur){
		// regarder si pas deja un panier memorise et pas trop vieux
		$res = spip_query('SELECT id_panier,id_auteur,cookie_panier,date_panier FROM spip_paniers WHERE id_auteur='._q($id_auteur).' ORDER BY date_panier DESC LIMIT 0,1');
		if (($row = spip_fetch_array($res)) && (time()<strtotime($row['date_panier'])+_PANIER_ENREGISTRES_TTL)) {
			$id_panier = $row['id_panier'];
			panier_setcookie('panier',$row['cookie_panier']);
			panier_mise_a_jour_cle($id_panier,$id_auteur);
		}
	}
	if (!$id_panier && isset($_COOKIE['panier']) && strlen($_COOKIE['panier'])) {
		// il y a un panier en memoire qu'on ne retrouve pas, il faut le creer !
		include_spip('base/abstract_sql');
		$id_panier = spip_abstract_insert('spip_paniers',"(id_auteur,cookie_panier,maj)","("._q($id_auteur).",'',NOW())");
		spip_log("creation panier:$id_panier",'panier');
		$_COOKIE['id_panier_key'] = $_COOKIE['id_panier'] = 0; // forcer la remise a jour de la cle
	}
	#spip_log("id_encours:$id_panier/id_auteur:$id_auteur"/*.var_export($_COOKIE,true)*/,'panier');
	return $id_panier;
}

function panier_calcul_total($items){
	$net = 0;
	$gross = 0;
	$reduc = 1.0;
	foreach($items as $k=>$item){
		if (preg_match(',[-][0-9.]+[%],',trim($item[3]))) {
			$reduc = $reduc * (1.0+floatval($item[3])/100.0);
		}
		else {
			$net += $item[2];
			$gross += $item[3];
		}
	}
	$net = max($net,0) * $reduc;
	$gross = max($gross,0) * $reduc;
	return array($net,$gross);
}

function panier_explique_cookie($panier){
	// fonction analogue a la fonction js de panier.js.html qui decompose la chaine en table
	$items = explode('!',$panier);
	foreach($items as $k=>$item){
		if (strlen(trim($item))) {
			$items[$k] = array_map('urldecode',explode('|',$item));
			/* id, quantite, net, gross, category */
			$items[$k]['id'] = $items[$k][0];
			$items[$k]['quantity'] = $items[$k][1];
			$items[$k]['net_price'] = $items[$k][2];
			$items[$k]['gross_price'] = $items[$k][3];
			$items[$k]['category'] = $items[$k][4];
		}
		else unset($items[$k]);
	}
	return $items;
}
function panier_make_cookie($items){
	// fonction analogue a la fonction js de panier.js.html qui recompose la table en chaine
	// mais qui tri le panier dans l'ordre : articles, reduc fixes, reduc %
	$produits = array();
	$reduc_fixes = array();
	$reduc_pourcent = array();
	foreach($items as $k=>$item){
		$sitem = str_replace('%25','%',implode('|',array_map('urlencode',array($item[0],$item[1],$item[2],$item[3],$item[4]))));
		if (floatval($item[3])>0)
			$produits[] = $sitem;
		elseif(substr(trim($item[3]),-1)=='%')
			$reduc_pourcent[] = $sitem;
		else
			$reduc_fixes[] = $sitem;
	}
	$cookie = implode('!',array_merge($produits,$reduc_fixes,$reduc_pourcent));
	return $cookie;
}

function panier_update_from_cookies($id_panier) {
	$id_auteur = isset($GLOBALS['auteur_session']['id_auteur'])?$GLOBALS['auteur_session']['id_auteur']:0;
	$panier = isset($_COOKIE['panier'])?$_COOKIE['panier']:'';
	$res = spip_query("SELECT * FROM spip_paniers WHERE id_panier="._q($id_panier));
	$mise_a_jour_cle = (!$_COOKIE['id_panier_key'] || ($_COOKIE['id_panier']!=$id_panier));
	if($row = spip_fetch_array($res)) {
		if (($id_auteur!==$row['id_auteur'])
		  OR ($panier!==$row['cookie_panier'])
		  OR _request('var_panier')
		  OR _request('var_promo')) {
				$items = panier_explique_cookie($panier);
				$items = pipeline('panier_actualise_contenu',array('args'=>array(),'data'=>$items));
				$panier = panier_make_cookie($items);
				panier_setcookie('panier',$panier);
				spip_log("maj panier en base:$id_panier",'panier');
	  		spip_query("UPDATE spip_paniers SET "
		  	  . (($id_auteur && ($row['id_auteur']==0)) ? "id_auteur="._q($id_auteur).", " :"") // on ne peut mettre a jour l'id_auteur d'un panier qu'a la premiere connexion (securite) !
		  	  . "cookie_panier="._q($panier).", "
		  	  . "date_panier=NOW()"
		  	  . " WHERE id_panier="._q($id_panier));
				spip_query("DELETE FROM spip_forms_donnees_paniers WHERE id_panier="._q($id_panier));
				$rang = 0;
				foreach($items as $item) {
					if (intval($item[1]))
						spip_query("INSERT INTO spip_forms_donnees_paniers (id_panier,id_donnee,quantite,rang) VALUES ("._q($id_panier).","._q($item[0]).","._q($item[1]).","._q($rang++).")");
				}
				$mise_a_jour_cle = true;
		}
		if ($mise_a_jour_cle)
			panier_mise_a_jour_cle($id_panier,$id_auteur);
	}
}

if (_DIR_RESTREINT) {
	if ($id_panier = panier_id_panier_encours()) {
		panier_update_from_cookies($id_panier);
		$GLOBALS['auteur_session']['id_panier']=$id_panier; // mettre l'id_panier dans la session !
	}
	#spip_log("panier:$id_panier:".$GLOBALS['auteur_session']['id_auteur'].":".var_export($_COOKIE,true),'panier');
}
?>
