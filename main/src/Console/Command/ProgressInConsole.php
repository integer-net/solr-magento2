<?php

namespace IntegerNet\Solr\Console\Command;

use IntegerNet\Solr\Indexer\Progress\EventFinish;
use IntegerNet\Solr\Indexer\Progress\EventInfo;
use IntegerNet\Solr\Indexer\Progress\EventProgress;
use IntegerNet\Solr\Indexer\Progress\EventStart;
use IntegerNet\Solr\Indexer\Progress\ProgressHandler;
use IntegerNet\Solr\Indexer\Progress\ProgressUpdate;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressInConsole implements ProgressHandler
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProgressBar
     */
    private $progressBar;
    /**
     * @var bool
     */
    private $useProgressBar;

    const USE_PROGRESS_BAR = true;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output, $useProgressBar = false)
    {
        $this->output = $output;
        $this->useProgressBar = $useProgressBar;
    }

    public function progress(ProgressUpdate $update)
    {
        if ($update instanceof EventStart) {
            $this->output->writeln('<info>' . $update->getDescription() . '...</info>');
            if ($update->getExpectedSteps() && $this->useProgressBar) {
                $this->progressBar = $this->createProgressBarForEvent($update);
                $this->progressBar->start();
            }
        } elseif ($update instanceof EventProgress && $this->progressBar) {
            $this->progressBar->advance($update->getSteps());
        } elseif ($update instanceof EventFinish) {
            if ($this->progressBar) {
                $this->progressBar->finish();
                $this->progressBar->clear();
            }
            $this->output->writeln(
                '<info>' . $update->getDescription() . ' finished in ' . $update->getElapsedTimeMs() . 'ms</info>'
            );
        } elseif ($update instanceof EventInfo) {
            $this->output->writeln(
                '<comment>' . $update->getDescription() . '</comment>'
            );
        }
    }

    /**
     * @param EventStart $event
     * @return ProgressBar
     */
    private function createProgressBarForEvent(EventStart $event)
    {
        $progressBar = new ProgressBar($this->output, $event->getExpectedSteps());
        $progressBar->setBarCharacter('<fg=blue>█</>');
        $progressBar->setEmptyBarCharacter(' ');
        $progressBar->setProgressCharacter('');
        $progressBar->setBarWidth(40);
        return $progressBar;
    }

}