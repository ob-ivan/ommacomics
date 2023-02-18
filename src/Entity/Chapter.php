<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Chapter
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\All({
     *     @Assert\NotBlank(message="Select ZIP or JPEG file(s)", groups={ "upload" }),
     *     @Assert\File(mimeTypes={ "application/zip", "image/jpeg" }, groups={ "upload" })
     * })
     */
    private $folder;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $createDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublic;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $deleteTimestamp;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $displayName;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isHorizontal;

    public function getId()
    {
        return $this->id;
    }

    public function getFolder()
    {
        return $this->folder;
    }

    public function setFolder($folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    public function getCreateDate(): ?\DateTimeInterface
    {
        return $this->createDate;
    }

    public function setCreateDate(\DateTimeInterface $createDate): self
    {
        $this->createDate = $createDate;

        return $this;
    }

    public function getIsPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    public function getDeleteTimestamp(): ?int
    {
        return $this->deleteTimestamp;
    }

    public function setDeleteTimestamp(?int $deleteTimestamp): self
    {
        $this->deleteTimestamp = $deleteTimestamp;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->deleteTimestamp !== null;
    }

    /**
     * @deprecated Use setDeleteTimestamp() instead.
     * @param bool $isDeleted
     * @return $this
     */
    public function setIsDeleted(bool $isDeleted): self
    {
        $this->deleteTimestamp = $isDeleted ? time() : null;

        return $this;
    }

    public function getDisplayName(): string
    {
        return $this->displayName ?: $this->folder;
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getIsHorizontal(): ?bool
    {
        return $this->isHorizontal;
    }

    public function setIsHorizontal(bool $isHorizontal): self
    {
        $this->isHorizontal = $isHorizontal;

        return $this;
    }
}
