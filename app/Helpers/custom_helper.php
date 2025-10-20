<?php 
	use App\Models\User;
	use App\Models\Attendance;

	function preview($data)
	{
		echo "<pre>";
		print_r ($data);
		exit;
	}

	function default_where()
	{
		return "is_active = 1 AND deleted_at IS NULL";
	}

	function check_permission($module)
	{
		$session = session();
		$userdata = $session->get('userdata');
		if($userdata['role'] == 1) {
			return true;
		} else {
			$model = new User;
			$udata = $model->select("permissions")->where('id',$userdata["id"])->first();
			if($udata) {
				if($udata["permissions"] != "") {
					$permissions = explode(",", $udata["permissions"]);
					if(in_array($module,$permissions)) {
						return true;
					} else {
						return false;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}

	function hours_diff($in_time,$out_time)
	{
		$checkInTime = $in_time;
        $checkOutTime = $out_time;

        // Create DateTime objects for check-in and check-out times
        $checkIn = new \DateTime($checkInTime);
        $checkOut = new \DateTime($checkOutTime);

        // Calculate the difference between the two times
        $interval = $checkIn->diff($checkOut);
        return $interval->h;
	}

	function calculateWorkingHours($start, $end, $breakMinutes)
	{
	    $startTime = new DateTime($start);
	    $endTime = new DateTime($end);

	    // Calculate the difference between start and end time
	    $interval = $startTime->diff($endTime);
	    
	    // Convert the total time difference to seconds
	    $totalSeconds = ($interval->h * 3600) + ($interval->i * 60) + $interval->s;

	    // Subtract the break time (convert minutes to seconds)
	    $totalSeconds -= ($breakMinutes * 60);

	    // Convert back to HH:MM:SS format
	    $hours = floor($totalSeconds / 3600);
	    $minutes = floor(($totalSeconds % 3600) / 60);
	    $seconds = $totalSeconds % 60;

	    return sprintf("%02d:%02d", $hours, $minutes);
	}

	function attendanceReport($salon_id,$staff_id,$year,$month)
	{
		$sdate = date('Y-m-01', strtotime("$year-$month-01")); // First day of current month
		$edate = date('Y-m-t', strtotime("$year-$month-01"));

		$model = new Attendance;
		if($salon_id != 0) {
		    $row = $model->select("COUNT(*) AS total_days, SUM(hours_diff) AS total_month_hours,rate")->where('salon_id',$salon_id)->where("staff_id",$staff_id)->where("date >=",$sdate)->where("date <=",$edate)->where("in_time !=","00:00:00")->where("deleted_at IS NULL")->get()->getRowArray();   
		} else {
		    $row = $model->select("COUNT(*) AS total_days, SUM(hours_diff) AS total_month_hours,rate")->where("staff_id",$staff_id)->where("date >=",$sdate)->where("date <=",$edate)->where("in_time !=","00:00:00")->where("deleted_at IS NULL")->get()->getRowArray();
		}
		return $row;
	}
	
	function format_date($date)
	{
	    return date("d M, Y",strtotime($date));
	}
	
	function format_time($time)
	{
	    return date("h:i A",strtotime($time));
	}