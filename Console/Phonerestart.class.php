<?php
namespace FreePBX\Console\Command;

use FreePBX;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use FreePBX\modules\Restart as RestartModule;

class Phonerestart extends Command
{
    private $jobname;
    private $extensions;

    private $verbosity = OutputInterface::VERBOSITY_NORMAL;
    private $stderr;

    private $input;
    private $output;

    protected function configure()
    {
        $this->setName("phonerestart");
        $this->setAliases(["pr"]);
        $this->setDescription(_("Restart phones"));
        $this->setDefinition(array(
            new InputOption(
                "extension", "e",
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                _("The extension to reboot (may be specified more than once)")
            ),
            new InputOption(
                "jobname", "j",
                InputOption::VALUE_REQUIRED,
                _("The name of a preset cron job to run")
            ),
            new InputOption(
                "quiet", "q",
                InputOption::VALUE_NONE,
                _("Supress error output.")
            ),
        ));
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->verbosity = $output->getVerbosity();
        $this->stderr = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $jobname = $input->getOption("jobname");
        $extensions = $input->getOption("extension");

        if ($jobname) {
            if (!preg_match("/scheduled_reboot_[0-9]{4}/", $jobname)) {
                $this->showHelp("Invalid job name specified");
            }
            $this->jobname = $jobname;
        }

        if ($extensions) {
            $this->extensions = $extensions;
        }

        if (empty($this->jobname) && empty($this->extensions)) {
            $this->showHelp();
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // need to make sure the module class is available
        $restarter = FreePBX::create()->Restart;
        if ($this->jobname) {
            if ($this->extensions) {
                $this->stderrOutput(_("Job name specified, ignoring extensions."));
            }
            $restarter->runJobs($output, $this->jobname);
        } else {
            foreach ($this->extensions as $ext) {
                if (RestartModule::restartDevice($ext)) {
                    $output->writeln(sprintf(_("Restart request sent for %s"), $ext));
                }
            }
        }
    }

    private function stderrOutput($msg, $debug = false)
    {
        if ($this->verbosity === OutputInterface::VERBOSITY_QUIET) {
            return;
        }
        $verbosity = $debug ? OutputInterface::VERBOSITY_VERY_VERBOSE : OutputInterface::VERBOSITY_NORMAL;
        $this->stderr->writeln(trim($msg), $verbosity);
    }

    private function showHelp($msg = "")
    {
        $this->stderrOutput($msg);
        $help = new HelpCommand();
        $help->setCommand($this);
        $help->run($this->input, $this->output);
        exit(2);
    }
}