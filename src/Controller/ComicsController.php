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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Markup;
use ZipArchive;

class ComicsController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function main(ChapterRepository $chapterRepository)
    {
        $publicChapters = $chapterRepository->findByIsPublic(true);
        $privateChapters = [];
        $recycleBinCount = null;
        if ($this->isGranted('ROLE_AUTHOR')) {
            $privateChapters = $chapterRepository->findByIsPublic(false);
            $recycleBinCount = $chapterRepository->getCountIsDeleted();
        }
        return $this->render(
            'comics/main.html.twig',
            [
                'publicChapters'  => $publicChapters,
                'privateChapters' => $privateChapters,
                'recycleBinCount' => $recycleBinCount,
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
            $this->unzip($file, $this->getChapterFolderAbsolutePath($folderName));
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
    public function read($folder, ChapterRepository $chapterRepository)
    {
        $chapter = $chapterRepository->findOneByFolder($folder);
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
    public function edit($folder, EntityManagerInterface $entityManager, Request $request, SessionInterface $session, ChapterRepository $chapterRepository)
    {
        $this->denyAccessUnlessGranted(
            'ROLE_AUTHOR',
            null,
            'Editing a chapter is only available for authors'
        );
        $chapter = $chapterRepository->findOneByFolder($folder);
        if (!$chapter) {
            return $this->renderUnknownChapterError($folder);
        }
        $form = $this->createForm(EditType::class, $chapter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $action = $form->getClickedButton()->getName();

            switch ($action) {
                case 'save':
                    $entityManager->persist($chapter);
                    $entityManager->flush();
                    $this->addFlash('info', 'Your changes were saved.');
                    break;
                case 'delete':
                    $this->performDeleteAction($session, $entityManager, $chapter);
                    break;
                case 'restore':
                    $this->performRestoreAction($entityManager, $chapter);
                    break;
                default:
                    break;
            }

            return $this->redirect($this->generateUrl('edit', [
                'folder' => $folder,
            ]));
        }

        return $this->render('comics/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/recycle-bin", name="recycle-bin")
     * @param ChapterRepository $chapterRepository
     * @return Response
     */
    public function recycleBin(ChapterRepository $chapterRepository)
    {
        $this->denyAccessUnlessGranted(
            'ROLE_AUTHOR',
            null,
            'The recycle bin is only available for authors'
        );
        $deletedChapters = $chapterRepository->findByIsDeleted();
        return $this->render(
            'comics/recycleBin.html.twig',
            [
                'deletedChapters' => $deletedChapters,
            ]
        );
    }

    /**
     * @Route("/restore/{folder}", name="restore")
     */
    public function restore($folder, EntityManagerInterface $entityManager, ChapterRepository $chapterRepository)
    {
        $this->denyAccessUnlessGranted(
            'ROLE_AUTHOR',
            null,
            'Restoring a chapter is only available for authors'
        );
        $chapter = $chapterRepository->findOneByFolder($folder);
        if (!$chapter) {
            return $this->renderUnknownChapterError($folder);
        }
        $this->performRestoreAction($entityManager, $chapter);
        return $this->redirect($this->generateUrl('edit', [
            'folder' => $folder,
        ]));
    }

    /**
     * @Route("/purge/{folder}", name="purge")
     */
    public function purge($folder, EntityManagerInterface $entityManager, ChapterRepository $chapterRepository)
    {
        $this->denyAccessUnlessGranted(
            'ROLE_AUTHOR',
            null,
            'Purging a chapter is only available for authors'
        );
        $chapter = $chapterRepository->findOneByFolder($folder);
        if (!$chapter) {
            return $this->renderUnknownChapterError($folder);
        }
        if (!$chapter->getIsDeleted()) {
            return $this->render('comics/error.html.twig', [
                'message' => 'Cannot purge a chapter that is not deleted!',
            ]);
        }
        $chapterName = $chapter->getDisplayName();
        $entityManager->remove($chapter);
        $entityManager->flush();
        $filesystem = new Filesystem();
        $filesystem->remove($this->getChapterFolderAbsolutePath($folder));
        $this->addFlash('info', 'You have purged the chapter "' . $chapterName . '".');
        return $this->redirect($this->generateUrl('main'));
    }

    private function generateUniqueFileName()
    {
        return date('Ymd-His-') . preg_replace('/\W/', '', base64_encode(random_bytes(6)));
    }

    private function unzip(SplFileInfo $file, string $destination)
    {
        $zip = new ZipArchive();
        $zip->open($file->getRealPath());
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

    private function getChapterFolderAbsolutePath(string $folderName): string
    {
        return $this->getParameter('chapter_directory') . '/' . $folderName;
    }

    /**
     * @param SessionInterface $session
     * @param EntityManagerInterface $entityManager
     * @param Chapter $chapter
     */
    private function performDeleteAction(SessionInterface $session, EntityManagerInterface $entityManager, Chapter $chapter): void
    {
        $chapter->setIsDeleted(true);
        $entityManager->persist($chapter);
        $entityManager->flush();

        $message = new Markup(
            $this->renderView('comics/flash/delete.html.twig', ['chapter' => $chapter]),
            'UTF-8'
        );

        $session->getFlashBag()->add('info', $message);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Chapter $chapter
     */
    private function performRestoreAction(EntityManagerInterface $entityManager, Chapter $chapter): void
    {
        $chapter->setIsDeleted(false);
        $entityManager->persist($chapter);
        $entityManager->flush();
        $this->addFlash('info', 'You have restored the chapter "' . $chapter->getDisplayName() . '".');
    }
}
