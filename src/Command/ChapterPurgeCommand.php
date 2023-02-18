<?php
namespace App\Command;

use App\Repository\ChapterRepository;
use App\Service\ComicsService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChapterPurgeCommand extends Command
{
    const MAXIMUM_AGE_IN_SECONDS = 180 * 86400;

    protected static $defaultName = 'chapter:purge';

    private $chapterRepository;
    private $comicsService;

    public function __construct(ChapterRepository $chapterRepository, ComicsService $comicsService)
    {
        $this->chapterRepository = $chapterRepository;
        $this->comicsService = $comicsService;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $maximumTimestamp = time() - self::MAXIMUM_AGE_IN_SECONDS;
        $purgedCount = 0;

        foreach ($this->chapterRepository->findByDeleteTimestamp($maximumTimestamp) as $chapter) {
            $output->writeln('Chapter info:');
            $output->writeln('    id              = ' . $chapter->getId());
            $output->writeln('    folder          = ' . $chapter->getFolder());
            $output->writeln('    createDate      = ' . $chapter->getCreateDate()->format(DATE_ATOM));
            $output->writeln('    isPublic        = ' . $chapter->getIsPublic() ? 'true' : 'false');
            $output->writeln('    deleteTimestamp = ' . $chapter->getDeleteTimestamp());
            $output->writeln('    displayName     = ' . $chapter->getDisplayName());
            $output->writeln('    isHorizontal    = ' . $chapter->getIsHorizontal());
            $output->write('Purging... ');
            $this->comicsService->purge($chapter);
            $output->writeln('Done!');
            ++$purgedCount;
        }

        $output->writeln('Purged ' . $purgedCount . ' chapter(s).');
        return Command::SUCCESS;
    }

}