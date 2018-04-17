<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
//use Doctrine\Common\Annotations\AnnotationReader;
//use Doctrine\Common\Annotations\AnnotationRegistry;

use App\Entity\Lecture;

// Trying to use right association to link Courses and Lessons together
// https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/association-mapping.html
// NB! To have real flexibility we'll start without any hard mapping between them!

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CourseRepository")
 * @ORM\Table(name="courses", options={"charset"="utf8mb4", "collate"="utf8mb4_unicode_ci"})
 * @Vich\Uploadable
 */
class Course
{

    public function __construct() {
        $this->date = new \DateTime();
        $this->lectures = new ArrayCollection();
    }

    /**
     * @ORM\OneToMany(targetEntity="Lecture", mappedBy="course")
     */
     private $lectures;
     public function getLectures()
     {
         return $this->lectures;
     }
     public function setLectures($lectures)
     {
         $this->lectures = $lectures;
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

    // Source URL
    // We could get Name from domain and link short description to it

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

/*
    / **
     * @ORM\Column(type="string", length=255, nullable=true)
     * /
    private $image;
    public function getImage()
    {
        return $this->image;
    }
    public function setImage($image)
    {
        $this->image = $image;
    }
*/

    //

    // https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/usage.md

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     * Mappings are defined at : app/config/config.yml
     * @Vich\UploadableField(mapping="courses", fileNameProperty="image")
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


    // Date of Creation or Update ?

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

    // Tags as plain text separated with commas

    /**
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

    // There are common Categories between News, Analytics section and Courses?

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     */
    private $category;
    public function getCategory()
    {
        return $this->category;
    }
    public function setCategory($category)
    {
        $this->category = $category;
    }


    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default": false})
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

    // Complexity Level : 0 = NOT_DEFINED | 1 = LOW | 2 = MEDIUM | 3 = HIGH
    // NB! Level — сделать выбор по списку Easy / Moderate / Advanced / Expert

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default": 0})
     */
    private $level;
    public function getLevel()
    {
        return $this->level;
    }
    public function setLevel($level)
    {
        $this->level = $level;
    }

/* NB! Do not need this. Use Easy Admin [ type_options: {choices: ] instead

    private $levelName;
    public function getLevelName()
    {
        switch($this->level) {
            case 2:
                return 'Moderate';
            case 3:
                return 'Advanced';
            case 4:
                return 'Expert';
        }

        return "Easy";
    }
    public function setLevelName($level)
    {
        switch($level) {
            case 'Moderate':
                $this->level = 2; break;
            case 'Advanced':
                $this->level = 3; break;
            case 'Expert':
                $this->level = 4; break;
            default:
                $this->level = 1; ;
        }
    }
*/

    // Price in base currency (USD?) like $99.95

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2, options={"default": 0.0})
     */
    private $price;
    public function getPrice()
    {
        return $this->price;
    }
    public function setPrice($price)
    {
        $this->price = $price;
    }

    // Virtual Property to aggregate Rating from external Stars table

    private $stars;
    public function getStars()
    {
        return $this->stars;
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


    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->getTitle();
    }

}
