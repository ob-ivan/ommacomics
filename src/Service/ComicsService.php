<?php
namespace App\Service;

class ComicsService
{
    private $chapterDirectory;

    public function __construct(string $chapterDirectory)
    {
        $this->chapterDirectory = $chapterDirectory;
    }

    public function getChapterFolderAbsolutePath(string $folder): string
    {
        return $this->chapterDirectory . '/' . $folder;
    }
}
