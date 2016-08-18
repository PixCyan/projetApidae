<?php

namespace ApidaeBundle\Repository;

/**
 * LabelQualiteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LabelQualiteRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * Nombre de labels étant affiliées à la liste d'objet Apidae donnée pour une liste de labels donnée.
     * @param $idsObjets
     * @param $idsLabels
     * @return array
     */
    public  function getCountServicesByIdsObjets($idsObjets, $idsLabels) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('l')
            ->from('ApidaeBundle:LabelQualite', 'l')
            ->innerJoin('l.objetsApidae', 'o')
            ->where('l.labId IN (?1)')
            ->andWhere('o.idObj IN (?2)')
            ->setParameters(array(1 => $idsLabels, 2 => $idsObjets));
        $query = $qb->getQuery()->getResult();
        return count($query);
    }
}
