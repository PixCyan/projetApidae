<?php

namespace ApidaeBundle\Repository;

use ApidaeBundle\Entity\ObjetApidae;
use ApidaeBundle\Entity\Categorie;
use Doctrine\ORM\EntityRepository;
use DoctrineExtensions\Query\Mysql\Regexp;

/**
 * ObjetApidaeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ObjetApidaeRepository extends EntityRepository {
    // Example - $qb->innerJoin('u.Group', 'g', Expr\Join::WITH, $qb->expr()->eq('u.status_id', '?1'))
    // Example - $qb->innerJoin('u.Group', 'g', 'WITH', 'u.status = ?1')
    // Example - $qb->innerJoin('u.Group', 'g', 'WITH', 'u.status = ?1', 'g.id')
    //public function innerJoin($join, $alias, $conditionType = null, $condition = null, $indexBy = null);

    /**
     * Rnvoie tous les objets dont la regex donné correpond au nom
     * @param $regex
     * @return array
     */
    public function getObjetByNom($regex) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->where('REGEXP(o.nom, :regexp) = true')
        ->setParameter('regexp', $regex);
        $query = $qb->getQuery()->getResult();
        return $query;

    }

    /**
     * Renvoie les ids de tous les objets
     * @return array
     */
    public function getAllIds() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('o.id', 'o.idObj')
            ->from('ApidaeBundle:ObjetApidae', 'o');
        $query = $qb->getQuery()->getArrayResult();
        return $query;
    }

    /**
     * Renvoie tous les objets correspondant à la liste de categorie donnée
     * @param $categories
     * @return array
     */
    public function getObjetsByCategories($categories) {
        /*return $this->createQueryBuilder('o')
            ->where('o.categories IN (:categories)')
            ->setParameter('categories', $categories)
            ->getQuery()
            ->getResult();*/
            $em = $this->getEntityManager();
            $qb = $em->createQueryBuilder();
            $qb->select('o')
                ->from('ApidaeBundle:ObjetApidae', 'o')
                ->innerJoin('o.categories', 'c', 'WITH', 'c.catId = ?1')
                ->setParameters(array(1 => $categories));
            $query = $qb->getQuery()->getArrayResult();
            return $query;
    }


    /**
     * Renvoie tous les objets correspondant à la liste d'ids
     * @param int[] $ids
     * @return ObjetApidae[]
     */
    public function getObjetsByids($ids) {
        return $this->createQueryBuilder('o')
            ->where('o.idObj IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }


    /**
     * Renvoie une liste d'objet apidae ayant la categorie donnée et la selection donnée
     * @param $idsCategories
     * @param $idSelection
     * @return \Doctrine\ORM\Query
     * @internal param $idCategorie
     */
    public function getObjetsCategorieSelection($idsCategories, $idSelection) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->innerJoin('o.categories', 'c')
            ->innerJoin('o.selectionsApidae', 'sel')
            ->where('c.catId IN (?1)')
            ->andWhere('sel.idSelectionApidae = ?2')
            ->setParameters(array(1 => $idsCategories, 2 => $idSelection));
        $query = $qb->getQuery()->getResult();
        return $query;

        /*
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->innerJoin('o.categories', 'c', 'WITH', 'c.catId = ?1')
            ->innerJoin('o.selectionsApidae', 'sel', 'WITH', 'sel.idSelectionApidae = ?2')
            ->setParameters(array(1 => $idCategorie, 2 => $idSelection));
        $query = $qb->getQuery()->getResult();
        return $query;

        Requete SQL
        select * from objet_apidae as o
            inner join service as s
            inner join objetHasServices as k
            on s.id = k.service_id
            and o.id = k.objet_apidae_id
            and s.serId = 1177
            inner join selection_apidae as sel
            inner join selection_apidae_objet_apidae as selObj
            on sel.id = selObj.selection_apidae_id
            and o.id = selObj.objet_apidae_id
            and sel.idSelectionApidae = 40518*



        select idSelectionApidae, id_obj, obj_Nom, serId, labId from objet_apidae as o
        */
    }

    /**
     * Renvoie une liste d'objet apidae ayant un service donné et la selection donnée
     * @param $idsServices
     * @param $idSelection
     * @return \Doctrine\ORM\Query
     * @internal param $idService
     */
    public function getObjetsServiceSelection($idsServices, $idSelection) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->innerJoin('o.services', 's')
            ->innerJoin('o.selectionsApidae', 'sel')
            ->where('s.serId IN (?1)')
            ->andWhere('sel.idSelectionApidae = ?2')
            ->setParameters(array(1 => $idsServices, 2 => $idSelection));
        $query = $qb->getQuery()->getResult();
        return $query;

        /*
        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->innerJoin('o.services', 's', 'WITH', 's.serId = ?1')
            ->innerJoin('o.selectionsApidae', 'sel', 'WITH', 'sel.idSelectionApidae = ?2')
            ->setParameters(array(1 => $idService, 2 => $idSelection));
        $query = $qb->getQuery()->getResult();
        return $query;
        //return $query->getArrayResult(); */
    }

    /**
     * Renvoie une liste d'objet apidae ayant le label donné et la selection donnée
     * @param $idsLabels
     * @param $idSelection
     * @return \Doctrine\ORM\Query
     * @internal param $idLabel
     */
    public function getObjetsLabelsSelection($idsLabels, $idSelection) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->innerJoin('o.labelsQualite', 'l')
            ->innerJoin('o.selectionsApidae', 'sel')
            ->where('l.labId IN (?1)')
            ->andWhere('sel.idSelectionApidae = ?2')
            ->setParameters(array(1 => $idsLabels, 2 => $idSelection));
        $query = $qb->getQuery()->getResult();
        return $query;


        /*
        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->innerJoin('o.labelsQualite', 'l', 'WITH', 'l.labId= ?1')
            ->innerJoin('o.selectionsApidae', 'sel', 'WITH', 'sel.idSelectionApidae = ?2')
            ->setParameters(array(1 => $idLabel, 2 => $idSelection));
        $query = $qb->getQuery()->getResult();
        return $query;*/
    }

    /**
     * Renvoie les objets liés à la catégorie donnée
     * @param $idCategorie
     * @return \Doctrine\ORM\Query
     */
    public function getObjetsCategorie($idCategorie) {
        //requete testée et validée
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->innerJoin('o.categories', 'c', 'WITH', 'c.catId = ?1')
            ->setParameters(array(1 => $idCategorie));
        $query = $qb->getQuery()->getResult();
        return $query;
        //return $query->getArrayResult();

        /* Requete SQL :
           select * from objet_apidae as o
            inner join categorie as c
            inner join objetHascategories as k
            on c.id = k.categorie_id
            and o.id = k.objet_apidae_id
            and c.catId = 1616
         */
    }

    /**
     * Renvoie le nombre d'objets liés aux services données et à la sélection donnée
     * @param $idsServices
     * @param $idSelection
     * @param $idsObjets
     * @return mixed
     */
    public function getCountObjetHasServices($idsServices, $idSelection, $idsObjets) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->innerJoin('o.selectionsApidae', 'sel')
            ->innerJoin('o.services', 's')
            ->where('s.serId IN (?1)')
            ->andWhere('o.idObj IN (?2)')
            ->andWhere('sel.idSelectionApidae = ?3')
            ->groupBy('o.idObj')
            ->setParameters(array(1 => $idsServices, 2 => $idsObjets, 3 => $idSelection));
        $query = $qb->getQuery()->getResult();
        return count($query);
    }

    /**
     * Renvoie le nombre d'objets liés aux labels donnés et à la sélection donnée
     * @param $idsLabels
     * @param $idSelection
     * @param $idsObjets
     * @return int
     */
    public function getCountObjetHasLabels($idsLabels, $idSelection, $idsObjets) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->innerJoin('o.selectionsApidae', 'sel')
            ->innerJoin('o.labelsQualite', 'l')
            ->where('l.labId IN (?1)')
            ->andWhere('o.idObj IN (?2)')
            ->andWhere('sel.idSelectionApidae = ?3')
            ->groupBy('o.idObj')
            ->setParameters(array(1 => $idsLabels, 2 => $idsObjets, 3 => $idSelection));
        $query = $qb->getQuery()->getResult();
        return count($query);
    }

    /**
     * Renvoie le nombre d'objets liés aux categories donnés et à la sélection donnée
     * @param $idsCategories
     * @param $idSelection
     * @param $idsObjets
     * @return int
     */
    public function getCountObjetHasCategories($idsCategories, $idSelection, $idsObjets) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->innerJoin('o.selectionsApidae', 'sel')
            ->innerJoin('o.categories', 'c')
            ->where('c.catId IN (?1)')
            ->andWhere('o.idObj IN (?2)')
            ->andWhere('sel.idSelectionApidae = ?3')
            ->groupBy('o.idObj')
            ->setParameters(array(1 => $idsCategories, 2 => $idsObjets, 3 => $idSelection));
        $query = $qb->getQuery()->getResult();
        return count($query);
    }


    //-------------- Fonction de test
    public function getTest($services, $paiements, $tourismes, $categories, $classements,  $idSelection)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->innerJoin('o.selectionsApidae', 'sel')
            ->innerJoin('o.services', 's')
            ->innerJoin('o.categories', 'c')
            ->innerJoin('o.labelsQualite', 'l')
            ->where('s.serId IN (?1)')
            ->andWhere('s.serId IN  (?2)')
            ->andWhere('s.serId IN  (?3)')
            ->andWhere('c.catId IN  (?4)')
            ->andWhere('l.labId IN  (?5)')
            ->andWhere('sel.idSelectionApidae = ?6')
            ->setParameters(array(1 => $services, 2 => $paiements, 3 => $tourismes, 4 => $categories, 5 => $classements, 6 => $idSelection));
        $query = $qb->getQuery()->getResult();
        return $query;
    }

}
