<?php
namespace App\Controller;

use App\Entity\Chapter;
use App\Form\ChapterType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
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
            // TODO: Unzip
            $chapter->setFolder($folderName);
            $chapter->setCreateDate(new DateTime());
            $entityManager->persist($chapter);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('chapter_view'));
        }

        return $this->render('comics/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function generateUniqueFileName()
    {
        return date('Ymd-His-') . base64_encode(random_bytes(2));
    }
}
