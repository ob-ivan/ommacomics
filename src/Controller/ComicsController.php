<?php
namespace App\Controller;

use App\Entity\Chapter;
use App\Form\ChapterType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use ZipArchive;

class ComicsController extends Controller
{
    /**
     * @Route("/", name="main")
     */
    public function main(EntityManagerInterface $entityManager)
    {
        $chapterRepository = $entityManager->getRepository(Chapter::class);
        $publicChapters = $chapterRepository->findByIsPublic(true);
        $privateChapters = [];
        if ($this->isGranted('ROLE_AUTHOR')) {
            $privateChapters = $chapterRepository->findByIsPublic(false);
        }
        return $this->render(
            'comics/main.html.twig',
            [
                'publicChapters' => $publicChapters,
                'privateChapters' => $privateChapters,
            ]
        );
    }

    /**
     * @Route("/upload", name="upload")
     */
    public function upload(EntityManagerInterface $entityManager, Request $request)
    {
        $this->denyAccessUnlessGranted(
            'ROLE_AUTHOR',
            null,
            'Uploading chapter is only available for authors'
        );
        $chapter = new Chapter();
        $form = $this->createForm(ChapterType::class, $chapter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $chapter->getFolder();
            $folderName = $this->generateUniqueFileName();
            $this->unzip($file, $this->getParameter('chapter_directory'), $folderName);
            $chapter->setFolder($folderName);
            $chapter->setCreateDate(new DateTime());
            $entityManager->persist($chapter);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('read', [
                'folder' => $folderName,
            ]));
        }

        return $this->render('comics/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/read/{folder}", name="read")
     */
    public function read($folder, EntityManagerInterface $entityManager)
    {
        $chapter = $entityManager->getRepository(Chapter::class)
            ->findOneByFolder($folder);
        if (!$chapter) {
            return $this->render('comics/error.html.twig', [
                'message' => 'Unknown chapter ' . $folder,
            ]);
        }
        if (!$chapter->getIsPublic()) {
            $this->denyAccessUnlessGranted(
                'ROLE_AUTHOR',
                null,
                'You are not allowed to read this chapter'
            );
        }
        $fullFolderPath = "{$this->getParameter('chapter_directory')}/{$folder}";
        if (!is_dir($fullFolderPath)) {
            return $this->render('comics/error.html.twig', [
                'message' => 'Folder not found for chapter ' . $folder,
            ]);
        }
        return $this->render('comics/read.html.twig', [
            'folder' => $folder,
            'files' => array_filter(
                scandir($fullFolderPath),
                function ($fileName) use ($fullFolderPath) {
                    return is_file("$fullFolderPath/$fileName");
                }
            ),
        ]);
    }

    private function generateUniqueFileName()
    {
        return date('Ymd-His-') . preg_replace('/\W/', '', base64_encode(random_bytes(6)));
    }

    private function unzip(
        SplFileInfo $file,
        string $chapterDirectory,
        string $folderName
    ) {
        $zip = new ZipArchive();
        $zip->open($file->getRealPath());
        $destination = "$chapterDirectory/$folderName";
        $zip->extractTo($destination);
        $zip->close();
        // Move files from subfolders to the top.
        $subfiles = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $destination,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_PATHNAME
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($subfiles as $absPath) {
            if (is_dir($absPath)) {
                // Should be safe, as directories are walked last.
                rmdir($absPath);
                continue;
            }
            $subfile = new File($absPath);
            $subfile->move($destination);
        }
    }
}
