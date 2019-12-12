<?php use \FreePBX\modules\Restart; ?>
<div class="container-fluid">
	<h1><?= htmlspecialchars(_('Restart Phones')) ?></h1>
	<?= $txtinfo ?>
	<div class = "display full-border">
		<div class="row">
			<div class="col-sm-12">
				<div class="fpbx-container">
					<div class="display no-border">
						<form class="fpbx-submit" action="?display=restart" method="post">

							<div class="nav-container">
								<div class="scroller scroller-left"></div>
								<div class="scroller scroller-right"></div>
								<div class="wrapper">
									<ul class="nav nav-tabs list" role="tablist">
										<li role="presentation" data-name="restartform" class="active">
											<a href="#restartform" aria-controls="restartform" role="tab" data-toggle="tab">
												<?= htmlspecialchars(_("Reboot Devices")) ?>
											</a>
										</li>
										<li role="presentation" data-name="restartlist">
											<a href="#restartlist" aria-controls="restartlist" role="tab" data-toggle="tab">
												<?= htmlspecialchars(_("Pending Reboots")) ?>
											</a>
										</li>
									</ul>
								</div>
							</div>

							<div class="tab-content display">
								<div role="tabpanel" id="restartform" class="tab-pane active">
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
																<select class="form-control" id="xtnlist" multiple="multiple" name="restartlist[]" size="8">
<?php foreach ($device_list as $device): ?>
																	<option value="<?= htmlspecialchars($device["id"]) ?>">
																		<?= htmlspecialchars("$device[id] - $device[description] - $device[ua] Device") ?>
																	</option>
<?php endforeach ?>
																</select>
																<span class="input-group-addon">
																	<button class="btn" id="selectall"><?= htmlspecialchars(_("SELECT ALL")) ?></button>
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
									<!--Schedule Enable-->
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
									<!--END Schedule Enable-->
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
								</div>
								<div role="tabpanel" id="restartlist" class="tab-pane">
									<table id="pending_restart_grid"
										data-url="ajax.php?module=restart&amp;command=listJobs"
										data-cache="false"
										data-cookie="true"
										data-cookie-id-table="pending_restart_grid"
										data-maintain-selected="true"
										data-show-columns="true"
										data-show-toggle="true"
										data-toggle="table"
										data-pagination="true"
										data-search="true"
										class="table table-striped"
									>
										<thead>
											<tr>
												<th data-sortable="true" data-field="jobname">
													<?= htmlspecialchars(_("Job Name")) ?>
												</th>
												<th data-sortable="true" data-field="time">
													<?= htmlspecialchars(_("Time")) ?>
												</th>
												<th data-sortable="true" data-field="devices">
													<?= htmlspecialchars(_("Devices")) ?>
												</th>
												<th data-formatter="Restart.actionLinkFormatter">
													<?= htmlspecialchars(_("Actions")) ?>
												</th>
											</tr>
										</thead>
									</table>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
