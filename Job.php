<?php
namespace FreePBX\modules\Restart;

use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputInterface;
use \FreePBX\Job\TaskInterface;
use \FreePBX;
use \Exception;

class Job implements TaskInterface {
    public static function run(InputInterface $input, OutputInterface $output) {
        $output->writeln("");
        if ($input->getOption("force")) {
            $id = $input->getOption("run");
            $job = FreePBX::Job();
            foreach ($job->getAll() as $row) {
                if ($row["id"] === $id) {
                    $jobname = $row["jobname"];
                    break;
                }
            }
            $output->writeln(sprintf(
                _("Cannot force this job. Try \"fwconsole phonerestart --jobname %s\" instead."),
                $jobname
            ));
            return false;
        }
        $output->writeln(_("Starting phone restarts..."));
        \FreePBX::Restart()->runJobs($output);
        $output->writeln(_("Finished"));
        return true;
    }
}
