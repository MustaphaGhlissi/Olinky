<?php

namespace Protector\AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * GeneratedLink
 *
 * @ORM\Table(name="generated_link")
 * @ORM\Entity(repositoryClass="Protector\AppBundle\Repository\GeneratedLinkRepository")
 */
class GeneratedLink
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var array
     *
     * @ORM\Column(name="links", type="array")
     * @Assert\NotBlank(message="Veuillez renseigner ce champ")
     */
    private $links;


    /**
     * @var string
     *
     * @ORM\Column(name="token", unique=true, type="string", length=255)
     */
    private $token;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set links
     *
     * @param array $links
     *
     * @return GeneratedLink
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * Get links
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return GeneratedLink
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

}
