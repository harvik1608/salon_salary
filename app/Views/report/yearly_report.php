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
			<h5 class="card-header"><b>Yearly Reports</b></h5>
			<div class="container">
				<form method="post" action="">
					<div class="row">	
					    <div class="col-lg-3">
							<div class="mb-4">
								<label class="form-label" for="basic-default-fullname">Staff</label>
								<select class="form-control select2" id="staff" name="staff">
									<option value="all">All</option>
									<?php
										foreach ($staffs as $key => $val) {
										    echo '<option value="'.$val['id'].'">'.strtoupper(strtolower($val['fname'].' '.$val['lname'])).'</option>'; 
										} 
									?>
								</select>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="mb-4">
								<label class="form-label" for="basic-default-fullname">Year</label>
								<select class="form-control select2" id="year" name="year">
									<?php
										for($i = date("Y"); $i <= 2050; $i ++) {
											if($i == date("Y")) {
												echo '<option value="'.$i.'" selected>'.$i.'</option>';
											} else {
												echo '<option value="'.$i.'">'.$i.'</option>';
											}
										} 
									?>
								</select>
							</div>
						</div>
						
						<div class="col-lg-3">
							<div class="mb-4">
								<label class="form-label" for="basic-default-fullname">From Month</label>
								<select class="form-control select2" id="from_month">
								    <option value="">Choose Month</option>
								    <?php
								        for($i = 1; $i <= 12; $i ++) {
								            echo '<option value="'.$i.'">'.date('F', mktime(0, 0, 0, $i, 1)).'</option>';
								        }
								    ?>
								</select>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="mb-4">
								<label class="form-label" for="basic-default-fullname">To Month</label>
								<select class="form-control select2" id="to_month">
								    <option value="">Choose Month</option>
								    <?php
								        for($i = 1; $i <= 12; $i ++) {
								            echo '<option value="'.$i.'">'.date('F', mktime(0, 0, 0, $i, 1)).'</option>';
								        }
								    ?>
								</select>
							</div>
						</div>
					</div>
					<!-- <div class="row">
						<div class="col-lg-3">
							<button type="submit" class="btn btn-success btn-sm">Export</button>
						</div>
					</div> -->
				</form><br>
				<div id="summary-view">
				</div>
				<br>
			</div>
		</div>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script type="text/javascript">
	var page_title = "Yearly Reports";
	$(document).ready(function() {
        load_data();
        $("#staff,#year,#from_month,#to_month").change(function(){
        	load_data();
        });
    });
    function load_data()
    {
    	$.ajax({
    		url: "<?php echo base_url('load-yearly-report'); ?>",
    		type: "GET",
    		data: {
    			year: $("#year").val(),
    			staff: $("#staff").val(),
    			from_month: $("#from_month").val(),
    			to_month: $("#to_month").val(),
    		},
    		beforeSend: function () {
		        // $("#page-loader").show();
		    },
		    success: function (response) {
		        $("#summary-view").html(response);
		        calc_total();
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
    function save_staff_yearly_note(staff_id)
    {
        if($("#staff_note_"+staff_id).val() != "") {
            $.ajax({
        		url: "<?php echo base_url('save-staff-yearly-note'); ?>",
        		type: "GET",
        		data: {
        			year: $("#year").val(),
        			note: $("#staff_note_"+staff_id).val(),
        			staff_id: staff_id,
        		},
        		beforeSend: function () {
    		        $("#save-btn-"+staff_id).html('<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>').attr("disabled",true);
    		    },
    		    success: function (response) {
    		       show_toast(response.message);
    		       $("#save-btn-"+staff_id).html('Save').attr("disabled",false);
    		    },
    		    error: function () {
    		        $("#save-btn-"+staff_id).html('Save').attr("disabled",false);
    		        show_toast("An error occurred.");
    		    },
    		    complete: function () {
    		        $("#save-btn-"+staff_id).html('Save').attr("disabled",false);
    		    	// $("#page-loader").css("display","none !important;");
    		        // $("#page-loader").hide();
    		    }
        	});   
        } else {
            show_toast("Note is required");
            $("#staff_note_"+staff_id).focus();
        }
    }
    function calc_total()
    {
        var all_salons = $("#all_salons").val().split(",");
        if($("#summary-view table").length > 0) {
            for(var i = 0; i < all_salons.length; i ++) {
                $("#summary-view table").each(function(){
                    var total_days = total_hours = total_salary = grand_total_days = grand_total_hours = grand_total_salary = 0;
                    let tblId = $(this).attr("id");
                    $("#"+tblId+" tbody tr").each(function(){
                        if($(this).find("span[class^=day-"+(all_salons[i])+"]").length > 0) {
                            total_days = total_days + parseFloat($(this).find("span[class^=day-"+(all_salons[i])+"]").attr("data-day"));
                            total_hours = total_hours + parseFloat($(this).find("span[class^=hour-"+(all_salons[i])+"]").attr("data-hour"));
                            total_salary = total_salary + parseFloat($(this).find("span[class^=salary-"+(all_salons[i])+"]").attr("data-salary"));
                        }
                        if($(this).find("span[class^=total-day-"+(all_salons[i])+"]").length > 0) {
                            grand_total_days = grand_total_days + parseFloat($(this).find("span[class^=total-day-"+(all_salons[i])+"]").attr("data-day"));
                            grand_total_hours = grand_total_hours + parseFloat($(this).find("span[class^=total-hour-"+(all_salons[i])+"]").attr("data-hour"));
                            grand_total_salary = grand_total_salary + parseFloat($(this).find("span[class^=total-salary-"+(all_salons[i])+"]").attr("data-salary"));
                        }
                    });
                    let formatted = total_hours.toFixed(2);
                    let formatted_salary = total_salary.toFixed(2);
                    $("#"+tblId+" td[class=total-"+all_salons[i]+"]").html("<b>DAYS : "+total_days+"<br>------------------------------------<br>HOURS : "+formatted+"<br>------------------------------------<br>SALARY : "+formatted_salary+"</b>");
                    
                    let formatted_grand = grand_total_hours.toFixed(2);
                    let formatted_grand_salary = grand_total_salary.toFixed(2);
                    $("#"+tblId+" td[class=grand-total]").html("<b>DAYS : "+grand_total_days+"<br>------------------------------------<br>HOURS : "+formatted_grand+"<br>------------------------------------<br>SALARY : "+formatted_grand_salary+"</b>");
                });
            }
        }
    }
    function download_report(id)
    {
    	html2canvas($("#table-"+id)[0]).then(canvas => {
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