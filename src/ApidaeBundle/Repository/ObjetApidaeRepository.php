<?php

namespace ApidaeBundle\Repository;

use ApidaeBundle\Entity\ObjetApidae;
use ApidaeBundle\Entity\Categorie;
use Doctrine\ORM\EntityRepository;

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
     * @param $idCategorie
     * @param $idSelection
     * @return \Doctrine\ORM\Query
     */
    public function getObjetsCategorieSelection($idCategorie, $idSelection) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->innerJoin('o.categories', 'c', 'WITH', 'c.catId = ?1')
            ->innerJoin('o.selectionsApidae', 'sel', 'WITH', 'sel.idSelectionApidae = ?2')
            ->setParameters(array(1 => $idCategorie, 2 => $idSelection));
        $query = $qb->getQuery()->getResult();
        return $query;

        /* Requete SQL
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
            and sel.idSelectionApidae = 40518
        */
    }

    /**
     * Renvoie une liste d'objet apidae ayant un service donné et la selection donnée
     * @param $idService
     * @param $idSelection
     * @return \Doctrine\ORM\Query
     */
    public function getObjetsServiceSelection($idService, $idSelection) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->innerJoin('o.services', 's', 'WITH', 's.serId = ?1')
            ->innerJoin('o.selectionsApidae', 'sel', 'WITH', 'sel.idSelectionApidae = ?2')
            ->setParameters(array(1 => $idService, 2 => $idSelection));
        $query = $qb->getQuery()->getResult();
        return $query;
        //return $query->getArrayResult();
    }

    /**
     * Renvoie une liste d'objet apidae ayant le label donné et la selection donnée
     * @param $idLabel
     * @param $idSelection
     * @return \Doctrine\ORM\Query
     */
    public function getObjetsLabelsSelection($idLabel, $idSelection) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('o')
            ->from('ApidaeBundle:ObjetApidae', 'o')
            ->innerJoin('o.labelsQualite', 'l', 'WITH', 'l.labId= ?1')
            ->innerJoin('o.selectionsApidae', 'sel', 'WITH', 'sel.idSelectionApidae = ?2')
            ->setParameters(array(1 => $idLabel, 2 => $idSelection));
        $query = $qb->getQuery()->getResult();
        return $query;
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

}
