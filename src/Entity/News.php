<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

use App\Entity\Likes;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NewsRepository")
 * @ORM\Table(name="news")
 * @Vich\Uploadable
 */
class News
{

    public function __construct() {
        $this->date = new \DateTime();
        $this->tags = new ArrayCollection();
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    public function getId() {
        return $this->id;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;
    public function getTitle()
    {
        return $this->title;
    }
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @ORM\Column(type="text")
     */
    private $lead;
    public function getLead()
    {
        return $this->lead;
    }
    public function setLead($lead)
    {
        $this->lead = $lead;
    }

    /**
     * @ORM\Column(type="text")
     */
    private $text;
    public function getText()
    {
        return $this->text;
    }
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $source;
    public function getSource()
    {
        return $this->source;
    }
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Provider")
     * @ORM\JoinColumn(name="provider_id", referencedColumnName="id", nullable=true)     
     */
    private $provider;
    public function getProvider()
    {
        return $this->provider;
    }
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }


    // https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/usage.md

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     * Mappings are defined at : app/config/config.yml
     * @Vich\UploadableField(mapping="news", fileNameProperty="image")
     * @var File $imageFile
     */

    private $imageFile;

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     */
    public function setImageFile(File $imageFile = null)
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->date = new \DateTime();
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    // Image Name for storing in DB (nor the File nor an Entity)

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */

    private $image;
/*
    public function getImage()
    {
        return $this->image;
    }
    public function setImage($image)
    {
        $this->image = $image;
    }
*/

    public function setImage(?string $image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }
/*
    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }
*/


    /**
     * @ORM\Column(type="datetime")
     */
    private $date;
    public function getDate()
    {
        return $this->date;
    }
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */

    private $tags;
    public function getTags()
    {
        return $this->tags;
    }
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

/*
    / * *
     * @ORM\ManyToMany(targetEntity="Tags")
     * /
    private $tags;
    public function getTags()
    {
        return $this->tags;
    }
    public function setTags($tags)
    {
        $this->tags = $tags;
    }
*/

//    / * *
//     * @ORM\OneToMany(targetEntity="Likes", mappedBy="content_id")
//     * /
//    private $likes;
//    public function getLikes()
//    {
//        return $this->likes;
//    }
//    public function setLikes($likes)
//    {
//        $this->likes = $likes;
//    }

/*
    / **
     * @ORM\ManyToOne(targetEntity="Category")
     * /
    private $category;
    public function getCategory()
    {
        return $this->category;
    }
    public function setCategory($category)
    {
        $this->category = $category;
    }
*/

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $active;
    public function getActive()
    {
        return $this->active;
    }
    public function setActive($flag)
    {
        $this->active = $flag;
    }

/*
    private $virt;
    public function getVirt()
    {
        return "[VIRT]";
    }

    private $Likes;
    public function getLikes()
    {
        $conn = $this->get('doctrine.dbal.connection_factory');
        var_dump($conn);

        $likes = $conn->fetchColumn(
            'SELECT SUM(count) FROM likes WHERE content_type = "news" AND content_id = ? ',
            [ $this->getId() ],
            0
        );

        return intval($likes);

        return "[LIKES]";
    }
*/

/*
    private $likes;
    public function getLikes()
    {
        //$conn = $this->getEntityManager()->getConnection();
        //$conn = $this->em->getConnection();
        //$conn = $this->getContainer()->getDoctrine()->getManager()->getConnection();

        $conn = $this->get('doctrine.dbal.connection_factory');
        var_dump($conn);

        $likes = $conn->fetchColumn(
            'SELECT SUM(count) FROM likes WHERE content_type = "news" AND content_id = ? ',
            [ $this->getId() ],
            0
        );

        return intval($likes);

//        $conn = $this->getEntityManager()->getConnection();
//        $likes = $conn->fetchColumn(
//            'SELECT SUM(count) FROM likes WHERE content_type = "news" AND content_id = ? ',
//            [ $this->getId() ],
//            0
//        );

//        return intval($likes);


//        $likesRepo = $this->getDoctrine()->getRepository(Likes::class);
//        $likes = $likesRepo->getLikes("news", $this-getId());
//        return $likes;
*/

//    }


    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->getTitle();
    }

}
