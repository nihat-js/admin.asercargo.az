@extends('backend.app')
@section('title')
	Collector
@endsection
@section('actions')

@endsection
@section('content')
<div class="container" style="margin-top: 12rem;">
	<form class="form-inline" data-bind="submit: handleScanCode">
		<h5>Barcode</h5>
		<div class="form-group">
			<input class="form-control" type="text" data-bind="textInput: scanCode, hasFocus: focusScanCode" />
		</div>
		<button type="submit" class="btn btn-primary">Scan</button>
		<span class="btn btn-primary"
					      id="save-btn" onclick="save_collector();">Save</span>
	</form>

	<h5>Track Numbers</h5>
	<ul class="list-group" data-bind="foreach: contents">
		<li class="list-group-item" data-bind="text: id, css: { 'list-group-item-warning': uncommitted }"></li>
	</ul>

	<div data-bind="visible: unclaim().length > 0">
		<h5>To Unclaim</h5>
		<ul class="list-group" data-bind="foreach: unclaim">
			<li class="list-group-item list-group-item-warning" data-bind="text: $data"></li>
		</ul>
	</div>
	
</div>
@endsection

@section('css')
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.2/toastr.min.css">
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.4.0/knockout-min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.6.1/lodash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.12.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.2/toastr.min.js"></script>
	<script>
		const monster = "";
		const cards = "761568295269";
		const nuts = "029000073135";
		const meds = "312546001879";

		const initialState = {
			contents: [monster],
			racks: [meds],
			carts: [nuts]
		};

		const States = {
			None: 0,
			Claim: 1,
			Unclaim: 2
		}

		const ScanType = {
			Cart: 1,
			Rack: 2,
			UnclaimedLot: 3,
			ClaimedLot: 4,
			PendingClaimLot: 5,
			PendingUnclaimLot: 6
		}

		class CartItemViewModel {
			constructor(id, uncommitted = false) {
				this.id = id;
				this.uncommitted = ko.observable(uncommitted);
			}
		}

		class ScanViewModel {
			scanCode = ko.observable("");
			focusScanCode = ko.observable(false);

			state = States.None;

			contents = ko.observableArray(_.map(initialState.contents, (item) => new CartItemViewModel(item)));
			unclaim = ko.observableArray();
			
			targetRack = ko.observable(null);

			handleScanCode() {
				var newScanCode = this.scanCode();
				var scanType = this.getScanType(newScanCode);
				switch(this.state) {
					case States.None: {
						switch(scanType) {
							case ScanType.UnclaimedLot: {
								this.state = States.Claim;
								this.contents.push(new CartItemViewModel(newScanCode, true));
								this.setCommitTimeout();
							} break;
							case ScanType.ClaimedLot: {
								this.state = States.Unclaim;
								var claimedLot = _.find(this.contents(), item => item.id == newScanCode);
								this.contents.remove(claimedLot);
								this.unclaim.push(claimedLot.id);
								this.setCommitTimeout();
							} break;
							case ScanType.Rack: {
								toastr.warning("You must scan an unclaimed carrier first!");
							} break;
							case ScanType.Cart: {
								toastr.info(`Switching to cart ${newScanCode}`);
							} break;
						}
					} break;
					case States.Claim: {
						switch(scanType) {
							case ScanType.UnclaimedLot: {
								this.contents.push(new CartItemViewModel(newScanCode, true));
								this.resetCommitTimeout();
							} break;
							case ScanType.PendingClaimLot: {
								var claimedLot = _.find(this.contents(), item => item.id == newScanCode);
								this.contents.remove(claimedLot);
								if(_.every(this.contents(), item => !item.uncommitted())) {
									this.reset();
								}
							} break;
							default: {
								toastr.error("Tried to perform an invalid scan while in Claim state.");
							} break;
						}
					} break;
					case States.Unclaim: {
						switch(scanType) {
							case ScanType.ClaimedLot: {
								var claimedLot = _.find(this.contents(), item => item.id == newScanCode);
								this.contents.remove(claimedLot);
								this.unclaim.push(claimedLot.id);
								this.resetCommitTimeout();
							}break;
							case ScanType.PendingUnclaimLot: {
								var unclaimLot = _.find(this.unclaim(), item => item == newScanCode);
								this.unclaim.remove(unclaimLot);
								this.contents.push(new CartItemViewModel(unclaimLot));
								if(this.unclaim().length == 0) {
									this.reset();
								}
							} break;
							case ScanType.Rack: {
								this.targetRack(newScanCode);
								this.commit();
							} break;
							default: {
								toastr.error("Tried to perform an invalid scan while in Unclaim state.");
							} break;
						}
					} break;
					// Insert this should never happen here.
					default: {
						toastr.error("Wat????");
					} break;
				}
				
				this.scanCode("");
				this.focusScanCode(true);
			}

			timeoutId = null;

			setCommitTimeout() {
				this.timeoutId = setTimeout(() => {
					this.commit();
				}, 5000);
			}

			resetCommitTimeout() {
				clearTimeout(this.timeoutId);
				this.setCommitTimeout();
			}

			reset() {
				this.state = States.None;
				if(this.timeoutId != null) {
					clearTimeout(this.timeoutId);
					this.timeoutId = null;
				}
			}

			commit() {
				this.timeoutId = null;
				switch(this.state) {
					case States.Claim: {
						for(var lot of this.contents()) {
							if(lot.uncommitted()){
								lot.uncommitted(false);
							}
						}
					} break;
					case States.Unclaim: {
						if(this.targetRack() == null) {
							toastr.error("You must scan a rack to unclaim lots/carriers!");
							var toUnclaim = _.map(this.unclaim(), item => new CartItemViewModel(item));
							this.contents(_.concat(this.contents(), toUnclaim));
							this.unclaim([]);
							return;
						}
						
						var lotsToUnclaim = this.unclaim();
						this.targetRack(null);
						this.unclaim([]);
						for(var lot of lotsToUnclaim) {
							// Do unclaim logic
						}
					} break;
				}
				this.state = States.None;
			}

			getScanType(scanCode) {
				var lot = _.find(this.contents(), item => item.id == scanCode);
				if(lot != null) {
					if(lot.uncommitted()) {
						return ScanType.PendingClaimLot;
					}
					return ScanType.ClaimedLot;
				}
				
				var lot = _.find(this.unclaim(), item => item == scanCode);
				if(lot != null) {
					return ScanType.PendingUnclaimLot;
				}
				
				if(_.some(initialState.racks, item => item == scanCode)) {
					return ScanType.Rack;
				}
				
				if(_.some(initialState.carts, item => item == scanCode)) {
					return ScanType.Cart;
				}
				
				return ScanType.UnclaimedLot;
			}
		}

		var vm = new ScanViewModel();
		ko.applyBindings(vm);
		setTimeout(() => {
			vm.focusScanCode(true);
		}, 100);

		function save_collector() {
			var tracking_number = $("#tracking_number").val();

			swal({
				title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
				text: 'Loading, please wait...',
				showConfirmButton: false
			});
			let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

			let formData = new FormData();
			formData.append('tracking_number', tracking_number);
		
			let settings = {headers: {'content-type': 'multipart/form-data',processData: false}};
			$.ajax({
				type: "post",
				url: '',
				headers: {
					'X-CSRF-TOKEN': CSRF_TOKEN
				},
				processData: false,
				contentType: false,
				data: formData,
				success: function (response) {
					swal.close();
					if (response.case === 'success') {
						let flight_details = response.flight_details;
						if (flight_details !== false) {
							flight_departure = flight_details['departure'];
							flight_destination = flight_details['destination'];
							flight_date = flight_details['plan_take_off'];
						}
						let amount = response['amount_response']['amount'] + ' ' + response['amount_response']['currency'];
						package_amount = amount;
						let internal_id = response['internal_id'];
						package_internal_id = internal_id;
						item_add_to_table(amount, internal_id);
						check_package_collector(check_package_url, tracking_number, true);
						generate_waybill_content_for_print();
						clear_values();
						show_alert_message(response.case, response.title, response.content);
						$("#waybill_doc").removeClass("btn-danger").addClass("btn-success");
						waybill_print_access = true;

						let has_container_details = response.has_container_details;
						let container_details = response.container_details;
						if (has_container_details) {
							let container_name = container_details['container'];
							let container_package_count = container_details['count'];
							let container_total_weight = container_details['weight'];

							$("#container_details_name").html(container_name);
							$("#container_details_count").html(container_package_count);
							$("#container_details_weight").html(container_total_weight);
							$("#container_details_area").css('display', 'block');
						}
					} else {
						let message_type = 'warning';
						if (response.case === 'error') {
							message_type = 'danger';
						}
						if (response.type === 'validation') {
							let content = response.content;
							let validation_message = '';
							$.each(content, function (index, value) {
								if (value.length !== 0) {
									for (let i = 0; i < value.length; i++) {
										validation_message += value[i] + '\n';
									}
								}
							});
							show_alert_message(message_type, response.title, validation_message);
						} else {
							show_alert_message(message_type, response.title, response.content);
						}
						$("#waybill_doc").removeClass("btn-success").addClass("btn-danger");
						waybill_print_access = false;
					}
				}
			});
		}
	</script>
@endsection
