<?php
namespace FreePBX\modules;

use BMO;
use DateTime;
use Exception;
use FreePBX;
use FreePBX\FreePBX_Helpers as Helper;
use FreePBX\modules\Restart\Job;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Output\OutputInterface;

class Restart extends Helper implements BMO
{
    const MODULE_NAME = "restart";

    public function __construct($freepbx = null)
    {
        if ($freepbx === null) {
            throw new Exception("Not given a FreePBX Object");
        }
        $this->FreePBX = $freepbx;
    }

    public function install() {}

    public function uninstall() {}

    public function backup() {}

    public function restore($backup) {}

    public function doConfigPageInit($page) {}

    public function getActionBar($request)
    {
        $buttons = array(
            'submit' => array(
                'name' => 'submit',
                'id' => 'submit',
                'value' => _('Restart Phones')
            )
        );
        return $buttons;
    }

    /**
     * This function is run when configuration is generated
     *
     * "When the 'reload' button is clicked, genConfig will be called, the output will
     * be given to any modules that requested it, and what they return will then be
     * given to writeConfig."
     *
     * @see \FreePBX\FileHooks::processNewHooks()
     * @return associative array with filename=>contents
     */
    public function genConfig()
    {
        $conf = array(
            "sip_notify_additional.conf" => array(
                "aastra-check-cfg"       => array("Event" => "check-sync"),
                "aastra-xml"             => array("Event" => "aastra-xml"),
                "algo-check-cfg"         => array("Event" => "check-sync"),
                "audiocodes-check-cfg"   => array("Event" => "check-sync"),
                "cisco-check-cfg"        => array("Event" => "check-sync"),
                "cyberdata-check-cfg"    => array("Event" => "check-sync"),
                "grandstream-check-cfg"  => array("Event" => "check-sync"),
                "linksys-cold-restart"   => array("Event" => "reboot_now"),
                "linksys-warm-restart"   => array("Event" => "restart_now"),
                "panasonic-check-cfg"    => array("Event" => "check-sync"),
                "polycom-check-cfg"      => array("Event" => "check-sync"),
                "reboot-snom"            => array("Event" => "reboot"),
                "reboot-yealink"         => array("Event" => "check-sync\\;reboot=false"),
                "sipura-check-cfg"       => array("Event" => "resync"),
                "spa-reboot"             => array("Event" => "reboot"),
            )
        );
        return $conf;
    }

    /**
     * This function is run when configuration is applied
     *
     * @see \FreePBX\FileHooks::processNewHooks()
     * @param array $config The configuration object (returned from genConfig)
     * @return void
     */
    public function writeConfig($config)
    {
        $this->FreePBX->WriteConfig($config);
    }

    /**
     * Ajax request check; confirm command is okay and optionally pass some settings
     *
     * @see \FreePBX\Ajax::doRequest()
     * @param string $command The command name
     * @param string $setting Settings to return back
     * @return boolean
     */
    public function ajaxRequest($command, &$setting)
    {
        return in_array($command, array("listJobs", "deleteJob"));
    }

    /**
     * Handle the ajax request, passed in $_REQUEST["command"]
     *
     * @see \FreePBX\Ajax::doRequest()
     * @return mixed The result of the command
     */
    public function ajaxHandler()
    {
        $request = $_REQUEST;
        $command = isset($request["command"]) ? $request["command"] : "";
        if ($command === "listJobs") {
            $return = [];
            try {
                $job = FreePBX::Job();
                $jobs = array_filter(
                    $job->getAll(),
                    function($v) { return $v["modulename"] === self::MODULE_NAME; }
                );
                foreach ($jobs as $job) {
                    $sched = explode(" ", $job["schedule"]);
                    $time = "$sched[1]:$sched[0]";
                    $day = $sched[2];
                    $month = $sched[3];
                    $jobname = $job["jobname"];
                    $recurring = (strpos($jobname, "recurring") === 0);
                    if ($devices = $this->getConfig($jobname)) {
                        $devices = implode(", ", $devices);
                    } else {
                        $devices = _("None (invalid entry)");
                    }
                    $return[] = array(
                        "jobname" => $jobname,
                        "time" => $time,
                        "day" => $day,
                        "month" => $month,
                        "devices" => $devices,
                        "recurring" => $recurring,
                    );
                }
            } catch (Exception $e) {
                // assume exception means no Job class
                $conf = FreePBX::Config();
                $user = $conf->get("AMPASTERISKWEBUSER");
                $cron = FreePBX::Cron($user);
                $jobs = array_filter(
                    $cron->getAll(),
                    function($v) { return preg_match("/(?:scheduled|recurring)_reboot_", $v); }
                );
                foreach ($jobs as $job) {
                    $cron = explode(" ", $job);
                    $time = "$cron[1]:$cron[0]";
                    $day = $cron[2];
                    $month = $cron[3];
                    $jobname = str_replace("--jobname=", "", $cron[7]);
                    $recurring = (strpos($jobname, "recurring") === 0);
                    $devices = _("None (invalid entry)");
                    if ($devices = $this->getConfig($jobname)) {
                        $devices = implode(", ", $devices);
                    }
                    $return[] = array(
                        "jobname" => $jobname,
                        "time" => $time,
                        "day" => $day,
                        "month" => $month,
                        "devices" => $devices,
                        "recurring" => $recurring,
                    );
                }
            }
            return $return;
        } elseif ($command === "deleteJob") {
            $jobname = $_GET["itemid"];
            return$this->deleteJob($jobname);
        }
        return ["status"=>false, "message"=>_("Unknown command")];
    }

    public function showPage()
    {
        $txtinfo = sprintf(
            '<div class="well well-info">%s</div>',
            htmlspecialchars(_("Currently, only Aastra, Snom, Polycom, Grandstream and Cisco devices are supported."))
        );

        if (isset($_POST["restartlist"]) && is_array($_POST["restartlist"])) {
            // when would this be displayed, and why???
            $txtinfo = sprintf(
                '<div class="well well-warning">%s</div>',
                htmlspecialchars(_("Warning: The restart mechanism behavior is vendor specific.  Some vendors only restart the phone if there is a change to the phone configuration or if an updated firmware is available via tftp/ftp/http"))
            );
            $restartlist = $_POST['restartlist'];
            if (empty($_POST["schedtime"])) {
                foreach($restartlist as $device) {
                    Restart::restartDevice($device);
                    $txtinfo = sprintf(
                        '<div class="well well-info">%s</div>',
                        htmlspecialchars(_("Restart requests sent!"))
                    );
                }
            } else {
                $schedtime = $_POST["schedtime"];
                $schedmonth = $_POST["schedmonth"];
                $schedday = $_POST["schedday"];
                $recurring = isset($_POST["schedrecurring"]);
                if ($schedmonth === "*") {
                    $format = ($schedday === "*" ? "*-* H:i" : "*-d H:i");
                } elseif ($schedday === "*") {
                    $format = "m-* H:i";
                } else {
                    $format = "m-d H:i";
                }
                $date = \Datetime::createFromFormat($format, "$schedmonth-$schedday $schedtime");
                if ($date) {
                    FreePBX::Restart()->scheduleRestart($restartlist, $schedtime, $schedmonth, $schedday, $recurring);
                    $txtinfo = sprintf(
                        '<div class="well well-info">%s</div>',
                        htmlspecialchars(_("Restart requests scheduled!"))
                    );
                } else {
                    $txtinfo = sprintf(
                        '<div class="well well-error">%s</div>',
                        htmlspecialchars(_("An invalid schedule was provided."))
                    );
                }
            }
        }

        $device_list = [];
        foreach (FreePBX::Core()->getAllDevicesByType() as $device) {
            $ua = ucfirst(self::getUserAgent($device["id"]));
            if ($ua) {
                $device["ua"] = $ua;
                $device_list[] = $device;
            }
        }
        return load_view(__DIR__ . "/views/page.restart.php", compact("txtinfo", "device_list"));
    }

    public function runJobs(OutputInterface $output, $jobname = "")
    {
        if (strpos($jobname, "scheduled_reboot_") === 0) {
            $this->runJob($output, $jobname, true);
        } elseif (strpos($jobname, "recurring_reboot_") === 0) {
            $this->runJob($output, $jobname);
        } elseif ($jobname === "") {
            $d = new Datetime();
            $time = $d->format("n_j_Hi");
            // run one-time job based on date/time
            $jobname = "scheduled_reboot_$time";
            $this->runJob($output, $jobname, true);

            $time = $d->format("*_*_Hi");
            // run one-time job based on just time
            $jobname = "scheduled_reboot_$time";
            $this->runJob($output, $jobname, true);

            $jobs = $this->getAll();
            foreach ($jobs as $name => $job) {
                if (strpos($name, "recurring_reboot_") !== 0) {
                    continue;
                }
                // check for daily jobs
                $time = $d->format("*_*_Hi");
                if (strpos($name, "recurring_reboot_$time_") === 0) {
                    $this->runJob($output, $name);
                    continue;
                }
                // check for monthly jobs
                $time = $d->format("*_j_Hi");
                if (strpos($name, "recurring_reboot_$time_") === 0) {
                    $this->runJob($output, $name);
                    continue;
                }
                // check for annual jobs
                $time = $d->format("n_j_Hi");
                if (strpos($name, "recurring_reboot_$time_") === 0) {
                    $this->runJob($output, $name);
                    continue;
                }
                // check for fucked up jobs (every day in a month???)
                $time = $d->format("n_*_Hi");
                if (strpos($name, "recurring_reboot_$time_") === 0) {
                    $this->runJob($output, $name);
                    continue;
                }
            }
        }
    }

    private function runJob(OutputInterface $output, $jobname, $delete = false)
    {
        if ($devicelist = $this->getConfig($jobname)) {
            foreach ($devicelist as $device) {
                $output->writeln(sprintf(_("Restart request sent for %s"), $device));
                self::restartDevice($device);
            }
        }
        if ($delete !== true) {
            // keep recurring jobs
            return;
        }
        $this->deleteJob($jobname);
    }

    private function deleteJob($jobname)
    {
        $this->delConfig($jobname);
        try {
            $job = FreePBX::Job();
            $result = $job->remove(self::MODULE_NAME, $jobname);
        } catch (Exception $e) {
            // assume exception means no Job class
            $conf = FreePBX::Config();
            $user = $conf->get("AMPASTERISKWEBUSER");
            $cron = FreePBX::Cron($user);
            $result = $cron->removeAll("--jobname=$jobname");
        }

        return $result;
    }

    public static function restartDevice($device)
    {
        $messages = array(
            "aastra"      => "aastra-check-cfg",
            "cisco"       => "cisco-check-cfg",
            "grandstream" => "grandstream-check-cfg",
            "polycom"     => "polycom-check-cfg",
            "snom"        => "reboot-snom",
            "yealink"     => "reboot-yealink",
        );
        $ua = self::getUserAgent($device);
        if ($ua) {
            self::sipNotify($messages[$ua], $device);
            return true;
        }
        return false;
    }

    public function scheduleRestart($device, $schedtime, $schedmonth, $schedday, $recurring = false)
    {
        list($hour, $min) = explode(":", $schedtime);
        $jobname = "scheduled_reboot_${schedmonth}_${schedday}_$hour$min";
        if ($recurring) {
            $jobname = "recurring_reboot_${schedmonth}_${schedday}_$hour$min_" . Uuid::uuid4();
        }
        $schedule = "$min $hour $schedday $schedmonth *";
        try {
            $job = FreePBX::Job();
            $job->remove(self::MODULE_NAME, $jobname);
            $job->addClass(
                self::MODULE_NAME,
                $jobname,
                Job::class,
                $schedule
            );
        } catch (Exception $e) {
            // assume exception means no Job class
            $conf = FreePBX::Config();
            $user = $conf->get("AMPASTERISKWEBUSER");
            $bindir = $conf->get("AMPSBIN");
            $cron = FreePBX::Cron($user);
            $cron->removeAll($jobname);
            $cron->add(array(
                "command" => "$bindir/fwconsole phonerestart --jobname=$jobname",
                "minute" => $min,
                "hour" => $hour,
                "day" => $schedday,
                "month" => $schedmonth,
            ));
        }
        $this->setConfig($jobname, $device);
    }

    public static function getUserAgent($device)
    {
        $astman = FreePBX::astman();
        $command = sprintf("sip show peer %s", $device);
        $response = $astman->Command($command);
        $response = implode("\n", $response);
        if (preg_match("/useragent *: *(.*?)\n/i", $response, $matches) && count($matches) > 1) {
            $ua = $matches[1];
            $agents = array("aastra", "cisco", "grandstream", "polycom", "snom", "yealink");
            $result = array_filter($agents, function($v) use($ua){ return stristr($ua, $v); });
            return array_pop($result);
        }
        return null;
    }

    private static function sipNotify($event, $device)
    {
        $astman = FreePBX::astman();
        $command = sprintf("sip notify %s %s", $event, $device);
        $res = $astman->Command($command);
    }


}
