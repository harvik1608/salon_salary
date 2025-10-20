<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<style>
	.table th {
		font-size: 10px !important;
		font-weight: bold !important;
		text-transform: none !important;
		color: #000 !important;
	}
	.table td {
		font-size: 10px !important;
	}
	td small {
		font-size: 11px !important;
	}
	thead tr th {
		text-align: center !important;
	}
</style>
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="card">
			<h5 class="card-header"><b><?php echo $salon['name']; ?></b></h5>
			<div class="container">
				<form method="post" action="<?php echo base_url('export-salon-report/'.$salon['id']); ?>">
					<div class="row mb-5">						
						<div class="col-lg-3">
							<label class="form-label" for="basic-default-fullname">Month & Year</label>
						    <input type="month" class="form-control" id="datetime" name="datetime" value="<?php echo date('Y-m'); ?>" />
						</div>
						<!--<div class="col-lg-4">-->
						<!--	<div class="mb-4">-->
						<!--		<label class="form-label" for="basic-default-fullname">From Date</label>-->
						<!--		<input type="date" class="form-control" id="from_date" name="fdate" />-->
						<!--	</div>-->
						<!--</div>-->
						<!--<div class="col-lg-4">-->
						<!--	<div class="mb-4">-->
						<!--		<label class="form-label" for="basic-default-fullname">To Date</label>-->
						<!--		<input type="date" class="form-control" id="to_date" name="tdate" />-->
						<!--	</div>-->
						<!--</div>-->
					</div>
					<div class="row">
						<div class="col-lg-3">
							<button type="button" class="btn btn-primary btn-sm" id="filterBtn">Filter</button>
							<a class="btn btn-danger btn-sm text-white" id="clearBtn">Clear</a>
							<button type="submit" class="btn btn-success btn-sm">Export</button>
						</div>
					</div>
				</form><br>
				<div id="summary-view">
				</div>
				<br>
			</div>
		</div>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script type="text/javascript">
	var page_title = "Salons";
	$(document).ready(function() {
        load_data();
        $(document).on("click","#filterBtn",function(){
			load_data();
		});
		$(document).on("click","#clearBtn",function(){
			$("select").val("");
			$("input").val("");
			load_data();
		});
		$(document).on("click",".show_more_tbl_1",function(){
		    if($.trim($(this).text()) == "Show More") {
		        $(this).text("Show Less");
		        $("#tbl_1 tbody").show(1000);
		    } else {
		        $(this).text("Show More");
		        $("#tbl_1 tbody").hide(1000);
		    }
		});
    });
    function load_data()
    {
    	$.ajax({
    		url: "<?php echo base_url('summary-view'); ?>",
    		type: "GET",
    		data: {
    			salon_id: "<?php echo $salon_id; ?>",
    			month_year: $("#datetime").val(),
    			from_date: $("#from_date").val(),
    			to_date: $("#to_date").val()
    		},
    		beforeSend: function () {
		        // $("#page-loader").show();
		    },
		    success: function (response) {
		        $("#summary-view").html(response);
		    },
		    error: function () {
		        alert("An error occurred.");
		    },
		    complete: function () {
		    	// $("#page-loader").css("display","none !important;");
		        // $("#page-loader").hide();
		    }
    	});
    }
    function remove_atten(id)
    {
    	if(confirm("Are you sure?")) {
    		$.ajax({
	    		url: "<?php echo base_url('remove-staff-attendance'); ?>",
	    		type: "GET",
	    		data: {
	    			id: id
	    		},
	    		success:function(response){
	    			load_data();
	    		}
	    	});
    	}
    }
    function update_atten(id)
    {
    	var stime = $.trim($("#checkin-"+id).find("td:eq(2)").text());
    	$("#checkin-"+id).find("td:eq(2)").html('<input type="text" value="'+stime+'" onblur="cal_working_hours('+id+')" />');
    	var etime = $.trim($("#checkin-"+id).find("td:eq(3)").text());
    	$("#checkin-"+id).find("td:eq(3)").html('<input type="text" value="'+etime+'" onblur="cal_working_hours('+id+')" />');
    	var break_in_minute = $.trim($("#checkin-"+id).find("td:eq(4)").text());
    	if(break_in_minute == "-") {
    		break_in_minute = "";
    	}
    	$("#checkin-"+id).find("td:eq(4)").html('<input type="text" value="'+break_in_minute+'" onblur="cal_working_hours('+id+')" />');
    	var tip = $.trim($("#checkin-"+id).find("td:eq(6) span").text());
    	$("#checkin-"+id).find("td:eq(6)").html('<input type="text" value="'+tip+'" />');
    	$("#checkin-"+id).find("td:eq(0) small:eq(0)").html('<a href="javascript:save_atten('+id+');"><i class="icon-base bx bx-save icon-sm"></i></a>');
    	$("#checkin-"+id).find("td:eq(7) b").hide();
    	$("#checkin-"+id).find("td:eq(7) select").show();
    }
    function save_atten(id)
    {
    	var stime = $.trim($("#checkin-"+id).find("td:eq(2) input").val());
    	var etime = $.trim($("#checkin-"+id).find("td:eq(3) input").val());
    	var btime = $.trim($("#checkin-"+id).find("td:eq(4) input").val());
    	var tip = $.trim($("#checkin-"+id).find("td:eq(6) input").val());
    	var salon_id = $.trim($("#checkin-"+id).find("td:eq(7) select option:selected").val());
    	$.ajax({
    		url: "<?php echo base_url('save-staff-attendance'); ?>",
    		type: "GET",
    		data: {
    			id: id,
    			in_time: stime,
    			out_time: etime,
    			break: btime,
    			hours_diff: $.trim($("#checkin-"+id).find("td:eq(5) span").text()),
    			tip: tip,
    			salon_id: salon_id
    		},
    		success:function(response){
    			load_data();
    		}
    	});
    }
    function cal_working_hours(id)
    {
    	var in_time = $.trim($("#checkin-"+id).find("td:eq(2) input").val());
		var out_time = $.trim($("#checkin-"+id).find("td:eq(3) input").val());
		var break_time = $.trim($("#checkin-"+id).find("td:eq(4) input").val());

		// Parse in_time and out_time into hours and minutes
		var in_time_parts = in_time.split(':');
		var out_time_parts = out_time.split(':');

		var in_hours = parseInt(in_time_parts[0], 10);
		var in_minutes = parseInt(in_time_parts[1], 10);
		var out_hours = parseInt(out_time_parts[0], 10);
		var out_minutes = parseInt(out_time_parts[1], 10);

		// Calculate total work time in minutes
		var total_minutes = (out_hours * 60 + out_minutes) - (in_hours * 60 + in_minutes);

		// Initialize break time in minutes
		var break_minutes = break_time && !isNaN(break_time) ? parseInt(break_time, 10) : 0;

		// Subtract break time from total work time
		var working_minutes = total_minutes - break_minutes;
		console.log(working_minutes);
		// Convert working time to hours and fractional minutes
		var working_hours = Math.floor(working_minutes / 60); // Full hours
		var working_fractional = (working_minutes % 60) / 60; // Remaining minutes as fraction
		// console.log(working_fractional);
		// Final working time in decimal hours
		var working_time_in_hours = working_hours + working_fractional;

		// Display the result in the input field
		if(!isNaN(working_time_in_hours)) {
			$("#checkin-"+id).find("td:eq(5) span").text(working_time_in_hours.toFixed(2));
		} else {
			$("#checkin-"+id).find("td:eq(5) span").text("0.00");
		}
    }
    function copy_content(id)
    {
    	html2canvas($("#staff-checkin-"+id)[0]).then(canvas => {
            let imgData = canvas.toDataURL("image/png");

            // Create a download link
            let link = document.createElement("a");
            link.href = imgData;
            link.download = "captured_image.png";
            link.click();
        });
        show_toast("Report downloaded");
    }
    function show_full_table(staff_id)
    {
    	if($("#staff_more_less_view_"+staff_id).text() == "Show More") {
    		$("#staff-checkin-"+staff_id+" tbody").show();
    		$("#staff_more_less_view_"+staff_id).html("<small><b>Show Less</b></small>");
    	} else {
    		$("#staff_more_less_view_"+staff_id).html("<small><b>Show More</b></small>");
    		$("#staff-checkin-"+staff_id+" tbody").hide();
    	}
    }
    function copy_summary_content()
    {
    	html2canvas($("#staff_summary_tbl")[0]).then(canvas => {
            let imgData = canvas.toDataURL("image/png");

            // Create a download link
            let link = document.createElement("a");
            link.href = imgData;
            link.download = "captured_image.png";
            link.click();
        });
        show_toast("Report downloaded");
    }
</script>
<?= $this->endSection(); ?>