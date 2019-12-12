<?php
use FreePBX\modules\Restart;

if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

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
        FreePBX::Restart()->scheduleRestart($restartlist, $schedtime);
        $txtinfo = sprintf(
            '<div class="well well-info">%s</div>',
            htmlspecialchars(_("Restart requests scheduled!"))
        );
    }
}

$device_list = FreePBX::Core()->getAllDevicesByType();

?>
<div class="container-fluid">
	<h1><?= htmlspecialchars(_('Restart Phones')) ?></h1>
	<?= $txtinfo ?>
	<div class = "display full-border">
		<div class="row">
			<div class="col-sm-12">
				<div class="fpbx-container">
					<div class="display full-border">
						<form class="fpbx-submit" action="?display=restart" method="post">
							<!--Device List-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="xtnlist"><?= htmlspecialchars(_("Device List")) ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="xtnlist"></i>
												</div>
												<div class="col-md-9">
													<div class="input-group">
														<select class="form-control" id="xtnlist" multiple="multiple" name="restartlist[]">
<?php foreach ($device_list as $device): ?>
    <?php if ($ua = ucfirst(Restart::getUserAgent($device["id"]))): ?>
															<option value="<?= htmlspecialchars($device["id"]) ?>">
																<?= htmlspecialchars("$device[id] - $device[description] - $ua Device") ?>
															</option>
	<?php endif ?>
<?php endforeach ?>
														</select>
														<span class="input-group-addon">
															<button class="btn" id="selectall"><?= htmlspecialchars(_('SELECT ALL')) ?></button>
														</span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="xtnlist-help" class="help-block fpbx-help-block">
											<?= htmlspecialchars(_("Select Device(s) to restart.  Currently, only Aastra, Snom, Polycom, Grandstream and Cisco devices are supported.  All other devices will not show up in this list.  Click the \"Select All\" button to restart all supported devices.")) ?>
										</span>
									</div>
								</div>
							</div>
							<!--END Device List-->
							<!--Schedule Time-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="enable_schedule_n"><?= htmlspecialchars(_("Scheduled Reboot")) ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="schedtime"></i>
												</div>
												<div class="col-md-9 radioset">
													<input type="radio" id="enable_schedule_n" name="enable_schedule" value="0" checked="checked"/>
													<label for="enable_schedule_n"><?= htmlspecialchars(_("Now")) ?></label>
													<input type="radio" id="enable_schedule_y" name="enable_schedule" value="1"/>
													<label for="enable_schedule_y"><?= htmlspecialchars(_("Later")) ?></label>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="enable_schedule-help" class="help-block fpbx-help-block">
											<?= htmlspecialchars(_("You can reboot the devices now, or at a scheduled time in the next 24 hours.")) ?>
										</span>
									</div>
								</div>
							</div>
							<!--END Schedule Time-->
							<!--Schedule Time-->
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="schedtime"><?= htmlspecialchars(_("Reboot Time")) ?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="schedtime"></i>
												</div>
												<div class="col-md-9">
													<div class="input-group">
														<input type="time" class="form-control" name="schedtime" id="schedtime" value="00:00" disabled="disabled"/>
														<div class="input-group-addon">
															<?= htmlspecialchars(_("Server time:")) ?>
															<span id="idTime" data-time="<?= time() ?>" data-zone="<?= htmlspecialchars(date_default_timezone_get()) ?>">
																<?= htmlspecialchars((new \DateTime)->format("H:i:s T")) ?>
															</span>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="schedtime-help" class="help-block fpbx-help-block">
											<?= htmlspecialchars(_("Select the time you wish the device(s) to reboot.")) ?>
										</span>
									</div>
								</div>
							</div>
							<!--END Schedule Time-->
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
