<?php

namespace Pumukit\SchemaBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pumukit\SchemaBundle\Document\EmbeddedBroadcast
 *
 * @MongoDB\EmbeddedDocument
 */
class EmbeddedBroadcast
{
    const TYPE_PUBLIC = 'public';
    const TYPE_PASSWORD = 'password';
    const TYPE_LOGIN = 'login';
    const TYPE_GROUPS = 'groups';

    const NAME_PUBLIC = 'Public';
    const NAME_PASSWORD = 'Password protected';
    const NAME_LOGIN = 'Only logged in Users';
    const NAME_GROUPS = 'Only Users in Groups';

    /**
     * @var int $id
     *
     * @MongoDB\Id
     */
    private $id;

    /**
     * @var string $name
     *
     * @MongoDB\String
     */
    private $name = self::NAME_PUBLIC;

    /**
     * @var string $type
     *
     * @MongoDB\String
     */
    private $type = self::TYPE_PUBLIC;

    /**
     * @var string $password
     *
     * @MongoDB\String
     */
    private $password;

    /**
     * @var ArrayCollection $groups
     *
     * @MongoDB\ReferenceMany(targetDocument="Group", simple=true, sort={"key":1})
     */
    private $groups;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Contains group
     *
     * @param Group $group
     *
     * @return boolean
     */
    public function containsGroup(Group $group)
    {
        return $this->groups->contains($group);
    }

    /**
     * Add group
     *
     * @param Group $group
     */
    public function addGroup(Group $group)
    {
        return $this->groups->add($group);
    }

    /**
     * Remove group
     *
     * @param Group $group
     */
    public function removeGroup(Group $group)
    {
        $this->removeGroupById($group->getId());
    }

    /**
     * Remove group by id
     *
     * @param string $groupId
     */
    public function removeGroupById($groupId)
    {
        $this->groups = $this->groups->filter(function ($group) use ($groupId) {
                return $group->getId() !== $groupId;
            });
    }

    /**
     * Get groups
     *
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * to String
     */
    public function __toString()
    {
        $groups = $this->getGroups();
        $groupsDescription = '';
        if (($this->getType() === self::TYPE_GROUPS) && ($groups)) {
            $groupsDescription = ': ';
            foreach ($groups as $group) {
                $groupsDescription .= $group->getName();
                if ($group != $groups->last()) {
                    $groupsDescription .= ', ';
                }
            }
        }

        return $this->getName() . $groupsDescription;
    }

    /**
     * @Assert\True(message = "Password required if not public")
     */
    public function isPasswordValid()
    {
        return ((self::TYPE_PUBLIC == $this->getType()) || 
                ((self::TYPE_PASSWORD == $this->getType()) && ("" != $this->getPassword())));
    }
}