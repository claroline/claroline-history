<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Doctrine\ORM\EntityRepository;

class WorkspaceTagRepository extends EntityRepository
{
    public function findNonEmptyTagsByUser(User $user)
    {
        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            INNER JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t WITH t = rwt.tag
            WHERE t.user = :user
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter("user", $user);

        return $query->getResult();
    }

    public function findNonEmptyAdminTags()
    {
        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            INNER JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t WITH t = rwt.tag
            WHERE t.user IS NULL
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }


    public function findNonEmptyAdminTagsByWorspaces(array $workspaces)
    {
        if (count($workspaces) === 0) {
            throw new \InvalidArgumentException("Array argument cannot be empty");
        }

        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            INNER JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t WITH t = rwt.tag
            INNER JOIN Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace w WITH w = rwt.workspace
            WHERE t.user IS NULL
            AND (
        ";

        $index = 0;
        $eol = PHP_EOL;

        foreach ($workspaces as $workspace) {
            $dql .= $index > 0 ? '    OR ' : '    ';
            $dql .= "w.id = {$workspace->getId()}{$eol}";
            $index++;
        }
        $dql .= ")";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findPossibleAdminChildren(WorkspaceTag $tag)
    {
        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user IS NULL
            AND NOT EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user IS NULL
                AND (
                    (h.tag = :tag AND h.parent = t)
                    OR (h.tag = t AND h.parent = :tag AND h.level = 1)
                )
            )
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter("tag", $tag);

        return $query->getResult();
    }

    public function findAdminChildren(WorkspaceTag $tag)
    {
        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user IS NULL
            AND EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user IS NULL
                AND h.tag = t
                AND h.parent = :tag
                AND h.level = 1
            )
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter("tag", $tag);

        return $query->getResult();
    }

    /**
     * Find all admin tags that don't have any parents
     */
    public function findAdminRootTags()
    {
        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user IS NULL
            AND NOT EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user IS NULL
                AND h.tag = t
                AND h.level > 0
            )
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Find all admin tags that are children of given tags id
     * Given admin tags are included
     */
    public function findAdminChildrenFromTags(array $tags)
    {
        if (count($tags) === 0) {
            throw new \InvalidArgumentException("Array argument cannot be empty");
        }

        $index = 0;
        $eol = PHP_EOL;
        $tagsTest = "(";

        foreach ($tags as $tag) {
            $tagsTest .= $index > 0 ? "    OR " : "    ";
            $tagsTest .= "p.id = {$tag}{$eol}";
            $index++;
        }
        $tagsTest .= "){$eol}";

        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user IS NULL
            AND EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                JOIN h.parent p
                WHERE h.user IS NULL
                AND h.tag = t
                AND {$tagsTest}
            )
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Find all admin tags that is parent of the given tag
     */
    public function findAdminParentsFromTag(WorkspaceTag $tag)
    {
        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t
            WHERE t.user IS NULL
            AND EXISTS (
                SELECT h
                FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy h
                WHERE h.user IS NULL
                AND h.tag = :tag
                AND h.parent = t
            )
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter("tag", $tag);

        return $query->getResult();
    }
}