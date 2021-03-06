<?php
namespace App\Controller;

use App\Entity\Chapter;
use App\Form\EditType;
use App\Form\UploadType;
use App\Repository\ChapterRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use ZipArchive;

class ComicsController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function main(EntityManagerInterface $entityManager)
    {
        /** @var ChapterRepository $chapterRepository */
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
            'Uploading a chapter is only available for authors'
        );
        $chapter = new Chapter();
        $form = $this->createForm(UploadType::class, $chapter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $chapter->getFolder();
            $folderName = $this->generateUniqueFileName();
            $this->unzip($file, $this->getParameter('chapter_directory'), $folderName);
            $chapter->setFolder($folderName);
            $chapter->setCreateDate(new DateTime());

            // @todo: This should be a default value somewhere in Chapter or UploadType!
            $chapter->setIsDeleted(false);

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
        if (!$chapter || $chapter->getIsDeleted()) {
            return $this->renderUnknownChapterError($folder);
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

    /**
     * @Route("/edit/{folder}", name="edit")
     */
    public function edit($folder, EntityManagerInterface $entityManager, Request $request)
    {
        $this->denyAccessUnlessGranted(
            'ROLE_AUTHOR',
            null,
            'Editing a chapter is only available for authors'
        );
        $chapter = $entityManager->getRepository(Chapter::class)
            ->findOneByFolder($folder);
        if (!$chapter) {
            return $this->renderUnknownChapterError($folder);
        }
        $form = $this->createForm(EditType::class, $chapter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $action = $form->getClickedButton()->getName();
            switch ($action) {
                case 'delete':  $chapter->setIsDeleted(true);  break;
                case 'restore': $chapter->setIsDeleted(false); break;
            }
            $entityManager->persist($chapter);
            $entityManager->flush();
            $message = '';
            switch ($action) {
                case 'save':    $message = 'Your changes were saved.'; break;
                case 'delete':  $message = 'The chapter has been deleted.'; break;
                case 'restore': $message = 'You have restored the chapter.'; break;
            }
            if ($message) {
                $this->addFlash('info', $message);
            }
            return $this->redirect($this->generateUrl('edit', [
                'folder' => $folder,
            ]));
        }

        return $this->render('comics/edit.html.twig', [
            'form' => $form->createView(),
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

    private function renderUnknownChapterError($folder)
    {
        return $this->render('comics/error.html.twig', [
            'message' => 'Unknown chapter ' . $folder,
        ]);
    }
}
