<?php
namespace App\Service;

use App\Entity\Chapter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class ComicsService
{
    private $chapterDirectory;
    private $entityManager;

    public function __construct(string $chapterDirectory, EntityManagerInterface $entityManager)
    {
        $this->chapterDirectory = $chapterDirectory;
        $this->entityManager = $entityManager;
    }

    public function purge(Chapter $chapter)
    {
        if (!$chapter->getIsDeleted()) {
            throw new ComicsServiceException('Cannot purge a chapter that is not deleted!');
        }
        $this->entityManager->remove($chapter);
        $this->entityManager->flush();
        $filesystem = new Filesystem();
        $filesystem->remove($this->getChapterFolderAbsolutePath($chapter->getFolder()));
    }

    public function getChapterFolderAbsolutePath(string $folder): string
    {
        return $this->chapterDirectory . '/' . $folder;
    }
}
