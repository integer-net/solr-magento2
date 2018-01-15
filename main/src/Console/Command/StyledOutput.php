<?php

namespace IntegerNet\Solr\Console\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Output adapter for Symfony Console < 2.7 (Magento 2.1) compatibility
 *
 * @package IntegerNet\Solr\Console\Command
 */
class StyledOutput
{
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * StyledOutput constructor.
     * @param OutputInterface $output
     * @param SymfonyStyle $symfonyStyle
     */
    public function __construct(OutputInterface $output, SymfonyStyle $symfonyStyle = null)
    {
        $this->output = $output;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function title($message)
    {
        if ($this->symfonyStyle) {
            $this->symfonyStyle->title($message);
        } else {
            $this->output->writeln($message);
            $this->output->writeln(str_repeat('=', mb_strlen($message)));
        }
    }

    public function success($message)
    {
        if ($this->symfonyStyle) {
            $this->symfonyStyle->success($message);
        } else {
            $this->output->writeln("<info>$message</info>");
        }
    }

    public function note($message)
    {
        if ($this->symfonyStyle) {
            $this->symfonyStyle->note($message);
        } else {
            $this->output->writeln("<comment>$message</comment>");
        }
    }

    public function error($message)
    {
        if ($this->symfonyStyle) {
            $this->symfonyStyle->error($message);
        } else {
            $this->output->writeln("<error>$message</error>");
        }
    }
}