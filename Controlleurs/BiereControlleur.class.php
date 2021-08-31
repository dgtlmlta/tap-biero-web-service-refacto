<?php

namespace TodoWS\Controlleurs;

use TodoWS\lib\Requete;
use TodoWS\modeles\Biere;
use TodoWS\modeles\Commentaire;
use TodoWS\modeles\Usager;

/**
 * Class BiereControleur
 * Controleur de la ressource Biere
 * 
 * @author Jonathan Martel
 * @version 1.1
 * @update 2019-11-11
 * @license MIT
 */

  
class BiereControlleur 
{
    private $retour = array('data'=>array());

    /**
     * Méthode qui gère les action en GET
     * @param Requete $oReq
     * @return Mixed Données retournées
     */
    public function getAction(Requete $requete)
    {
        if(!isset($requete->url_elements[0]) || !is_numeric($requete->url_elements[0]))	// Normalement l'id de la biere 
        {
            $this->retour["data"] = $this->getListeBiere();
            return $this->retour;
        }
        
        $id_biere = (int)$requete->url_elements[0];

        if(isset($requete->url_elements[1])) 
        {
            switch($requete->url_elements[1]) 
            {
                case 'commentaire':
                    $this->retour["data"] = $this->getCommentaire($id_biere);
                    break;
                case 'note':
                    $this->retour["data"] = $this->getNote($id_biere);
                    break;
                default:
                    unset($this->retour['data']);	
                    $this->retour['erreur'] = $this->erreur(400);					
                    break;
            }

            return $this->retour;
        }
        
        
        $this->retour["data"] = $this->getBiere($id_biere);
        
        return $this->retour;		
        
    }
    
    /**
     * Méthode qui gère les action en POST
     * @param Requete $oReq
     * @return Mixed Données retournées
     */
    public function postAction(Requete $oReq)	// Modification
    {
        if(!$this->valideAuthentification())
        {
            $this->retour['erreur'] = $this->erreur(401);
        }
        
        return $this->retour;
    }
    
    /**
     * Méthode qui gère les action en PUT
     * @param Requete $oReq
     * @return Mixed Données retournées
     */
    public function putAction(Requete $oReq)		//ajout ou modification
    {
        // var_dump($oReq);
        if(!$this->valideAuthentification())
        {
            $this->retour['erreur'] = $this->erreur(401);
            return $this->retour;
        }

        if(!isset($oReq->url_elements[0]) || !is_numeric($oReq->url_elements[0]))	// Normalement l'id de la biere 
        {
            $this->ajouterBiere($oReq->parametres);
            return $this->retour;
        }
        
        $id_biere = (int)$oReq->url_elements[0];
        
        if(isset($oReq->url_elements[1])) 
        {
            switch($oReq->url_elements[1]) 
            {
                case 'commentaire':                    
                    $modeleUsager = new Usager();
                    $id_usager = $modeleUsager->ajouterUsager($oReq->parametres["courriel"]);

                    $this->ajouterCommentaire($id_usager, $id_biere, $oReq->parametres["commentaire"]);
                    break;
                case 'note':
                    $this->retour["data"] = $this->getNote($id_biere);
                    break;
                default:
                    unset($this->retour['data']);	
                    $this->retour['erreur'] = $this->erreur(400);					
                    break;
            }

            return $this->retour;
        }
        
        
        $this->retour["data"] = $this->getBiere($id_biere);
        
        return $this->retour;
    }
    
    /**
     * Méthode qui gère les action en DELETE
     * @param Requete $oReq
     * @return Mixed Données retournées
     */
    public function deleteAction(Requete $oReq)
    {
        if(!$this->valideAuthentification())
        {
            $this->retour['erreur'] = $this->erreur(401);
            return $this->retour;
        }

        if(!isset($oReq->url_elements[0]) || !is_numeric($oReq->url_elements[0]))	// Normalement l'id de la biere 
        {
            $this->retour['erreur'] = $this->erreur(401);
            return $this->retour;
        }

        $modelBiere = new Biere();		
        $idBiere = (int)$oReq->url_elements[0];

        if(!$resultat = $modelBiere->effacerBiere($idBiere)) {
            $this->retour['erreur'] = $this->erreur(401, "Erreur de requête à la base de données");
            return $this->retour;
        };
        
        $this->retour["data"] = [
            "message" => $resultat
        ];

        return $this->retour;
    }


     /**
     * Méthode qui ajoute une bière à la base de données
     * 
     * @param Array $data Tableau des données nécessaires à l'ajout
     * @return Mixed Données retournées
     */
    private function ajouterBiere(Array $data)
    {
        $modelBiere = new Biere();

        if(!$resultatId = $modelBiere->ajouterBiere($data)) {
            $this->retour['erreur'] = $this->erreur(401, "Erreur de requête à la base de données");
            return $this->retour;
        };

        $this->retour["data"] = [
            "message" => "Insertion réussie",
            "biereId" => $resultatId
        ];
                
        return $this->retour;
    }

    /**
     * Méthode qui ajoute un commentaire à la base de données
     * 
     * @param int $id_usager Identifiant de l'usager
     * @param int $id_biere Identifiant de la bière
     * @param String $commentaire Le commentaire
     * @return Mixed Données retournées
     */
    private function ajouterCommentaire($id_usager, $id_biere, $commentaire)
    {
        $modelCommentaire = new Commentaire();

        if(!$resultatId = $modelCommentaire->ajouterCommentaire($id_usager, $id_biere, $commentaire)) {
            $this->retour['erreur'] = $this->erreur(401, "Erreur de requête à la base de données");
            return $this->retour;
        };

        $this->retour["data"] = [
            "message" => "Insertion réussie",
            "biereId" => $resultatId
        ];
                
        return $this->retour;
    }
        
    
    /**
     * Retourne les informations de la bière $id_biere
     * @param int $id_biere Identifiant de la bière
     * @return Array Les informations de la bière
     * @access private
     */	
    private function getBiere($id_biere)
    {
        $res = Array();
        $oBiere = new Biere();
        $res = $oBiere->getBiere($id_biere);
        return $res; 
    }
    
    /**
     * Retourne les informations des bières de la db	 
     * @return Array Les informations sur toutes les bières
     * @access private
     */	
    private function getListeBiere()
    {
        $res = Array();
        $oBiere = new Biere();
        $res = $oBiere->getListe();
        
        return $res; 
    }
    
    /**
     * Retourne les commentaires de la bière $id_biere
     * @param int $id_biere Identifiant de la bière
     * @return Array Les commentaires de la bière
     * @access private
     */	
    private function getCommentaire($id_biere)
    {
        $modelCommentaire = new Commentaire();
        
        return $modelCommentaire->getListe($id_biere);
    }

    /**
     * Retourne la note moyenne et le nombre de note de la bière $id_biere
     * @param int $id_biere Identifiant de la bière
     * @return Array La note de la bière
     * @access private
     */	
    private function getNote($id_biere)
    {
        
        $res = Array();
        return $res; 
    }
    
    /**
     * Valide les données d'authentification du service web
     * @return Boolean Si l'authentification est valide ou non
     * @access private
     */	
    private function valideAuthentification()
    {
          $access = false;
        $headers = apache_request_headers();
        
        if(isset($headers['Authorization']))
        {
            if(isset($_SERVER['PHP_AUTH_PW']) && isset($_SERVER['PHP_AUTH_USER']))
            {
                if($_SERVER['PHP_AUTH_PW'] == 'biero' && $_SERVER['PHP_AUTH_USER'] == 'biero')
                {
                    $access = true;
                }
            }
        }
          return $access;
    }

    
    private function erreur($code, $messageErreur = "Erreur de requete", $data="")
    {
        //header('HTTP/1.1 400 Bad Request');
        http_response_code($code);

        return array(
            "message"	=> $messageErreur,
            "code"		=> $code
        );
        
    }

}
