<?php
/*
* This file is part of the scheduler-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\SchedulerBundle\Command;

use Abc\Bundle\SchedulerBundle\Iterator\IteratorRegistryInterface;
use Abc\Bundle\SchedulerBundle\Schedule\Exception\SchedulerException;
use Abc\Bundle\SchedulerBundle\Schedule\SchedulerInterface;
use Abc\ProcessControl\ControllerInterface;
use Abc\ProcessControl\NullController;
use Abc\Bundle\SchedulerBundle\Iterator\IteratorRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class SchedulerProcessCommand extends Command
{
    protected static $defaultName = 'abc:scheduler:process';
    protected static $defaultDescription = 'Process schedules';

    /** @var SchedulerInterface */
    private $scheduler;
    /** @var IteratorRegistry */
    private $iteratorRegistry;

    public function __construct(
        SchedulerInterface $scheduler,
        IteratorRegistry $iteratorRegistry
    )
    {
        $this->scheduler = $scheduler;
        $this->iteratorRegistry = $iteratorRegistry;

        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->addOption('iteration', 'i', InputOption::VALUE_OPTIONAL, 'Run n iterations before exiting', false);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $i                = 0;
        $startMemoryUsage = memory_get_usage(true);

        try {
            do {
                if ($i > 0) {
                    usleep(5000000);
                }

                $i++;
                $this->iterate($input, $output, $startMemoryUsage);
            } while (!$this->getProcessController()->doStop() && (!$input->getOption('iteration') || $i < (int)$input->getOption('iteration')));

            $output->writeln('End of iteration cycle');
        } catch (\Exception $e) {
            $output->writeln(sprintf("<error>KO - %s</error>", $e->getMessage()));
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param                 $startMemoryUsage
     */
    protected function iterate(InputInterface $input, OutputInterface $output, $startMemoryUsage)
    {
        foreach ($this->iteratorRegistry->all() as $name => $iterator) {
            try {
                $numOfProcessed = $this->scheduler->process($iterator);

                if ($numOfProcessed > 0) {
                    $output->writeln(
                        sprintf(
                            "<comment>processed %s schedules of iterator %s</comment>",
                            $numOfProcessed,
                            $name
                        )
                    );
                }
            } catch (SchedulerException $e) {
                $output->writeln(sprintf("<error>Failed to process %s out of %s schedules</error>", count($e->getScheduleExceptions()), $e->getNumOfProcessed()));
            }
        }
    }

    /**
     * @return ControllerInterface
     */
    public function getProcessController()
    {
        return new NullController();
    }
}
