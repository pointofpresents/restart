<?php
namespace FreePBX\modules\Restart;

use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputInterface;
use \FreePBX\Job\TaskInterface;

class Job implements TaskInterface {
    public static function run(InputInterface $input, OutputInterface $output) {
        $output->writeln("");
        $output->writeln(_("Starting phone restarts..."));
        \FreePBX::Restart()->runJobs($output);
        $output->writeln(_("Finished"));
        return true;
    }
}
