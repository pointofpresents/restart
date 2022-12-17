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
            $jobname = "";
            $id = $input->getOption("run");
            $job = FreePBX::Job();
            foreach ($job->getAllEnabled() as $row) {
                if ($row["id"] === $id) {
                    $jobname = $row["jobname"];
                    break;
                }
            }
            if (empty($jobname)) {
                $output->writeln(_("Cannot find this job ID. Use \"fwconsole job --list\" to get the job ID (not name)."));
                return false;
            }
            try {
                FreePBX::Restart()->runJob($output, $jobname, strpos($jobname, "recurring") === false);
            } catch (Exception $e) {
                $output->writeln(sprintf(
                    _("Cannot force this job. Try \"fwconsole phonerestart --jobname %s\" instead."),
                    $jobname
                ));
                return false;
            }
        }
        $output->writeln(_("Starting phone restarts..."));
        FreePBX::Restart()->runJobs($output);
        $output->writeln(_("Finished"));
        return true;
    }
}
