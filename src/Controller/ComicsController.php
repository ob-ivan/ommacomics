<?php
namespace App\Controller;

use App\Entity\Chapter;
use App\Form\ChapterType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use ZipArchive;

class ComicsController extends Controller
{
    /**
     * @Route("/")
     */
    public function main()
    {
        return $this->render('comics/main.html.twig');
    }

    /**
     * @Route("/upload", name="upload")
     */
    public function upload(EntityManagerInterface $entityManager, Request $request)
    {
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
    public function read()
    {
        return $this->render('comics/read.html.twig', [
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
        $zip->extractTo("$chapterDirectory/$folderName");
        $zip->close();
        // TODO: Traverse subfolders.
    }
}
