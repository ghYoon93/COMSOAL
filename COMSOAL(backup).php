<?
	header("Content-Type: text/html; charset=UTF-8");

	if(empty($_POST['cycleTime']))
	{
		$workTime = $_POST['workTime'];
		$production = $_POST['production'];
		$cycleTime = $workTime / $production;  //생산 주기
	}
	else
	{
		$cycleTime = $_POST['cycleTime'];
	}

	$numWork = 0;

	while(true) //요소작업 목록 생성
	{
		if(is_null($_POST['elementWork'.$numWork])) break;
		$inputValue[$numWork][0] = $_POST['elementWork'.$numWork];
		$numWork++;
	}

	for($i=0; $i<$numWork; $i++) //초기 작업 요소들의 소요 시간
	{
		$inputValue[$i][1] = $_POST['elementWorkTime'.$i];

		$predecessorWork = explode(",", $_POST['precedenceWork'.$i]);
		
			for($j = 0; $j < count($predecessorWork); $j++)
			{
				array_push($inputValue[$i],$predecessorWork[$j]);
			}
	}

	$stationIdle = $cycleTime;
	$workStation = array(array());
	$workStationTime = array();
	$numWorkStation = 0;
	$maxLineEfficiency = 0;
	for($i = 0; $i < $numWork; $i++)
	{
		$standardTime += $inputValue[$i][1];
	}
	$minWorkStation = ceil($standardTime/$cycleTime);

	function AssignWorkStation($inputValue, $cycleTime, $stationIdle, $workStation,$workStationTime, $numWorkStation)
	{
		$numWork = count($inputValue);
		

		if(empty($inputValue))
		{	

			$actualCycleTime = $workStationTime[0];
			for($i =0; $i < $numWorkStation+1; $i++)
			{
				if($actualCycleTime < $workStationTime[$i]) 
				{
					$actualCycleTime = $workStationTime[$i];
				}	
			}
			$lineEfficiency = array(( array_sum($workStationTime)/ ($actualCycleTime * ($numWorkStation+1)) ) * 100,$workStation,($numWorkStation+1),$actualCycleTime);	
			return $lineEfficiency;
		}
		else
		{	
			
			unset($noPredecessorWork);
			$noPredecessorWork = array();
			
			for ($i=0; $i < $numWork; $i++) 
			{ 
				if(empty($inputValue[$i][2]))
				{
					array_push($noPredecessorWork,array($inputValue[$i][0],$inputValue[$i][1]));

				}
			}
			$numNoPredecessorWork = count($noPredecessorWork);     //Create list B

			unset($lessThanIdle);
			$lessThanIdle = array();
			while(empty($lessThanIdle))
			{
				for($i = 0; $i < $numNoPredecessorWork; $i++)
				{
				
					if($noPredecessorWork[$i][1] <= $stationIdle)
					{
						array_push($lessThanIdle,array($noPredecessorWork[$i][0],$noPredecessorWork[$i][1]));
					}
				}
				if(empty($lessThanIdle))

				{
					$numWorkStation++;
					$workStation[$numWorkStation] = array();
					$stationIdle = $cycleTime;
				}
			}
			$selectWork =$lessThanIdle[mt_rand(0,count($lessThanIdle)-1)][0];   //Create list C
			
			for($i = 0; $i < $numWork; $i++)
			{
				for($j = 2; $j <= count($inputValue[$i]); $j++)
				{
					if($inputValue[$i][$j] == $selectWork)
					{
						array_splice($inputValue[$i],$j,1);
						break;
					}

				}			
			}           //Update

			for($i = 0; $i < $numWork; $i++)
			{
				if($inputValue[$i][0] == $selectWork)
					{
						array_push($workStation[$numWorkStation], $selectWork);
						$workStationTime[$numWorkStation] += $inputValue[$i][1];
						$stationIdle -= $inputValue[$i][1];
						array_splice($inputValue,$i,1);
						break;
					}
			}

			return AssignWorkStation($inputValue, $cycleTime, $stationIdle, $workStation,$workStationTime, $numWorkStation);
		}	
	}

	$maxWorkStation = 0;

	for($i = 0; $i < 25000; $i++)
	{
		$newLineEfficiency = AssignWorkStation($inputValue, $cycleTime, $stationIdle, $workStation,$workStationTime, $numWorkStation);
		$numberOfWorkStation = $newLineEfficiency[2];
		${"countWorkStation".$numberOfWorkStation} +=1;
		if($maxWorkStation < $numberOfWorkStation)
		{
			$maxWorkStation = $numberOfWorkStation;
		}
		if($maxLineEfficiency[0] < $newLineEfficiency[0]) 
		{
			unset($maxLineEfficiency);
			$maxLineEfficiency = $newLineEfficiency;
			if($maxLineEfficiency[2] <= $minWorkStation)
			{
				$cycleTime = $newLineEfficiency[3];
				$stationIdle = $cycleTime;
			}	
		}
	}

	for($i = 0; $i < count($maxLineEfficiency[1]); $i++)
	{
		echo "작업장".($i+1).": ";
		for($j = 0; $j < count($maxLineEfficiency[1][$i]); $j++)
		{
			echo $maxLineEfficiency[1][$i][$j]." ";
		}
		echo "<br>";
	}
	echo round($maxLineEfficiency[0],2);
	echo "<br>";

	for($i = 0; $i < $maxWorkStation+1; $i++)
	{
		if(!empty(${"countWorkStation".$i}))
		{
			echo $i."개의 작업장: ".${"countWorkStation".$i}."회";
			echo "<br>";
		}
	}
	//echo $maxLineEfficiency[3];
	//echo "<br>";
	//echo $maxLineEfficiency[2];
	//echo "<br>";
	//echo $minWorkStation;


?>