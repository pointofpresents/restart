<?php
use FreePBX\modules\Restart;

if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

$restartlist = isset($_REQUEST['restartlist'])?$_REQUEST['restartlist']:[];

if (isset($_POST["action"]) && $_POST["action"] === "restart") {
    $restarted = false;
    foreach($restartlist as $device)  {
        Restart::restartDevice($device);
        $restarted = true;
    }
}

if(isset($restarted))  {
    if($restarted){
        $txtinfo = sprintf(
            '<div class="well well-info">%s</div>',
            htmlspecialchars(_("Restart requests sent!"))
        );
    } else {
        $txtinfo = sprintf(
            '<div class="well well-warning">%s</div>',
            htmlspecialchars(_("Warning: The restart mechanism behavior is vendor specific.  Some vendors only restart the phone if there is a change to the phone configuration or if an updated firmware is available via tftp/ftp/http"))
        );
    }
} else {
    $txtinfo = sprintf(
        '<div class="well well-info">%s</div>',
        htmlspecialchars(_("Currently, only Aastra, Snom, Polycom, Grandstream and Cisco devices are supported."))
    );
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
						<form class="fpbx-submit" action="?display=restart&amp;action=restart" method="post">
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
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$("#selectall").on("click", function() {
		$("#xtnlist option").attr("selected", true);
	});
</script>
