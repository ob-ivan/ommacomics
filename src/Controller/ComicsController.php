<?php
namespace App\Controller;

use App\Entity\Chapter;
use App\Form\ChapterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
            $archiveName = $folderName . '.' . $file->guessExtension();
            $file->move(
                $this->getParameter('chapter_directory'),
                $archiveName
            );
            $chapter->setFolder($folderName);
            $entityManager->persist($chapter);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('view_chapter'));
        }

        return $this->render('comics/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
