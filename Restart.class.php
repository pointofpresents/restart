<?php
namespace FreePBX\modules;

class Restart implements \BMO
{

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
        $buttons = array();
        switch($request['display']) {
            case 'restart':
                $buttons = array(
                    'submit' => array(
                        'name' => 'submit',
                        'id' => 'submit',
                        'value' => _('Restart Phones')
                    )
                );
            break;
        }
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
                "aastra-check-cfg" => array("Event" => "check-sync"),
                "aastra-xml" => array("Event" => "aastra-xml"),
                "algo-check-cfg" => array("Event" => "check-sync"),
                "audiocodes-check-cfg" => array("Event" => "check-sync"),
                "cisco-check-cfg" => array("Event" => "check-sync"),
                "cyberdata-check-cfg" => array("Event" => "check-sync"),
                "grandstream-check-cfg" => array("Event" => "check-sync"),
                "linksys-cold-restart" => array("Event" => "reboot_now"),
                "linksys-warm-restart" => array("Event" => "restart_now"),
                "panasonic-check-cfg" => array("Event" => "check-sync"),
                "polycom-check-cfg" => array("Event" => "check-sync"),
                "reboot-snom" => array("Event" => "reboot"),
                "reboot-yealink" => array("Event" => "check-sync\;reboot=false"),
                "sipura-check-cfg" => array("Event" => "resync"),
                "spa-reboot" => array("Event" => "reboot"),
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

    public static function restartDevice($device)  {
        $messages = array(
            "aastra" => "aastra-check-cfg",
            "cisco" => "cisco-check-cfg",
            "grandstream" => "grandstream-check-cfg",
            "polycom" => "polycom-check-cfg",
            "snom" => "reboot-snom",
            "yealink" => "reboot-yealink",
        );
        $ua = self::getUserAgent($device);
        if ($ua) {
            self::sipNotify($messages[$ua], $device);
        }
    }

    public static function getUserAgent($device)
    {
        $astman = \FreePBX::astman();
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
        $astman = \FreePBX::astman();
        $command = sprintf("sip notify %s %s", $event, $device);
        $res = $astman->Command($command);
    }


}
