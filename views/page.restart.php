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
																	<button type="button" class="btn" id="selectall"><?= htmlspecialchars(_("SELECT ALL")) ?></button>
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
																<input type="time" class="form-control scheduler" name="schedtime" id="schedtime" value="00:00" disabled="disabled"/>
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
									<!--Schedule Month-->
									<div class="element-container">
										<div class="row">
											<div class="col-md-12">
												<div class="row">
													<div class="form-group">
														<div class="col-md-3">
															<label class="control-label" for="schedmonth"><?= htmlspecialchars(_("Reboot Month")) ?></label>
															<i class="fa fa-question-circle fpbx-help-icon" data-for="schedmonth"></i>
														</div>
														<div class="col-md-9">
															<select class="form-control scheduler" name="schedmonth" id="schedmonth" disabled="disabled">
																<option selected="selected">*</option>
																<option value="1"><?= htmlspecialchars(_("January")) ?></option>
																<option value="2"><?= htmlspecialchars(_("February")) ?></option>
																<option value="3"><?= htmlspecialchars(_("March")) ?></option>
																<option value="4"><?= htmlspecialchars(_("April")) ?></option>
																<option value="5"><?= htmlspecialchars(_("May")) ?></option>
																<option value="6"><?= htmlspecialchars(_("June")) ?></option>
																<option value="7"><?= htmlspecialchars(_("July")) ?></option>
																<option value="8"><?= htmlspecialchars(_("August")) ?></option>
																<option value="9"><?= htmlspecialchars(_("September")) ?></option>
																<option value="10"><?= htmlspecialchars(_("October")) ?></option>
																<option value="11"><?= htmlspecialchars(_("November")) ?></option>
																<option value="12"><?= htmlspecialchars(_("December")) ?></option>
															</select>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<span id="schedmonth-help" class="help-block fpbx-help-block">
													<?= htmlspecialchars(_("Select the month you wish the device(s) to reboot. If set to *, the schedule will ignore the month. For recurring reboots, this means the phone will reboot every month on the specified day at the specified time.")) ?>
												</span>
											</div>
										</div>
									</div>
									<!--END Schedule Month-->
									<!--Schedule Day-->
									<div class="element-container">
										<div class="row">
											<div class="col-md-12">
												<div class="row">
													<div class="form-group">
														<div class="col-md-3">
															<label class="control-label" for="schedday"><?= htmlspecialchars(_("Reboot Day")) ?></label>
															<i class="fa fa-question-circle fpbx-help-icon" data-for="schedday"></i>
														</div>
														<div class="col-md-9">
															<select class="form-control scheduler" name="schedday" id="schedday" disabled="disabled">
																<option selected="selected">*</option>
																<option>1</option>
																<option>2</option>
																<option>3</option>
																<option>4</option>
																<option>5</option>
																<option>6</option>
																<option>7</option>
																<option>8</option>
																<option>9</option>
																<option>10</option>
																<option>11</option>
																<option>12</option>
																<option>13</option>
																<option>14</option>
																<option>15</option>
																<option>16</option>
																<option>17</option>
																<option>18</option>
																<option>19</option>
																<option>20</option>
																<option>21</option>
																<option>22</option>
																<option>23</option>
																<option>24</option>
																<option>25</option>
																<option>26</option>
																<option>27</option>
																<option>28</option>
																<option>29</option>
																<option>30</option>
																<option>31</option>
															</select>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<span id="schedday-help" class="help-block fpbx-help-block">
													<?= htmlspecialchars(_("Select the day you wish the device(s) to reboot. If set to *, the schedule will ignore the day. For recurring reboots, this means the phone will reboot every day at the specified time.")) ?>
												</span>
											</div>
										</div>
									</div>
									<!--END Schedule Day-->
									<!--Schedule Recurring-->
									<div class="element-container">
										<div class="row">
											<div class="col-md-12">
												<div class="row">
													<div class="form-group">
														<div class="col-md-3">
															<label class="control-label" for="schedrecurring"><?= htmlspecialchars(_("Recurring Reboot?")) ?></label>
															<i class="fa fa-question-circle fpbx-help-icon" data-for="schedrecurring"></i>
														</div>
														<div class="col-md-9">
															<input type="checkbox" class="form-control scheduler" name="schedrecurring" id="schedrecurring"/>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<span id="schedrecurring-help" class="help-block fpbx-help-block">
													<?= htmlspecialchars(_("Check this box to make the restart occur repeatedly at the specified time. By using * for day and/or month, you can make the phone reboot daily, monthly, or yearly.")) ?>
												</span>
											</div>
										</div>
									</div>
									<!--END Schedule Recurring-->
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
