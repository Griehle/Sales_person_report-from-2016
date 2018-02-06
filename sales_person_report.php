<?php
/**
 * Created by Gary Riehle.
 * User: owner
 * Date: 2/6/2016
 * Time: 2:05 PM
 */

<?php
//include 'include/session.php';
require("../finance/opentrack/ArkonaInterop.php");

      $con = mysqli_connect("localhost","user","password","database");
      if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

      $setDate = 0;

      if($_GET['search']==1)
      {
        $fdom = date('Y-m-d', strtotime($_POST['sDate']));
        $ldom = date('Y-m-d', strtotime($_POST['eDate']));
      }
      else
      {
        $fdom = date('Y-m-01', strtotime(date('Y-m-d')));
        $ldom = date('Y-m-t', strtotime(date('Y-m-d')));
      }







?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Sales Person Report</title>
<link rel="stylesheet" type="text/css" href="../script/datepickr.css" />
<!--<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">-->
<link rel="stylesheet" href="css/index.css">
</head>

  <form action="?search=1" method="post" id="finForm">
	    <table cellspacing="0" cellpadding="5" border="0" align="center">
             <tr>
                    <td colspan="2">
                        <b>Sales Report Search</b>
                    </td>
            </tr>
	        <tr>
	          <td colspan="2">
                  Start Date: <input type="text" name="sDate" id="datepick1" size="6" value="<?php if($_GET['search']){echo $_POST['sDate'];}else{echo date("m/1/Y");}?>">

                  End Date: <input type="text" name="eDate" id="datepick2" size="6" value="<?php if($_GET['search']){echo $_POST['eDate'];}else{echo date("m/t/Y");}?>">
	          </td>
	        </tr>
            <td align="right">
                  <input type="submit">
	      </td>
	  </table>
  </form>

    <div class = 'totals'>
    	<h3>Sales Totals</h3>
         <table>

         	<tr>

         <?php
                // This should be the stuff to display the images with names and total sales numbers

	/*$sSql = "Select SalesAgent, count(*) as nSales from testdriveform where SalesAgent not like '%Test%' and Date between '" . date("Y-m-1") ."' and '" . date("Y-m-t") . "' group by SalesAgent order by nSales desc";
	$sResult = mysqli_query($con, $sSql);
	$sRows = mysqli_num_rows($sResult);
	$nTotals = 0;

	          while($sRow = mysqli_fetch_array($sResult)) {
	          if(strlen($sRow['SalesAgent']) > 1)
	          {

                  echo '<td nowrap align="center">';

                  echo '<img src="../img/' . str_replace(" ", "_", explode(",", $sRow['SalesAgent'])) . '.jpg" width="100" onclick="salesMan(\'' .  explode(",", $sRow['SalesAgent']) . '\')" style="cursor:pointer" alt="' . explode(",", $sRow['SalesAgent']) . '" onerror="src=\'../img/default.jpg\'"><br><b>';


                  echo $sRow['nSales'];
                  $nTotals += $sRow['nSales'];
                  echo '</b></td>';
                  }
                }

				    */


	   $sql = "SELECT * FROM `Deals` WHERE `Merged` > '" . $fdom . "' and `Merged` < '" . $ldom . "' and DealNumber != 3258 Group By DealNumber ORDER BY Merged";

      $pResult = mysqli_query($con, $sql);
	  $mTotals = mysqli_num_rows ($pResult);

	   if(mysqli_num_rows($pResult) > 0)
      {
		  while($pRow = mysqli_fetch_array($pResult)) {

			  $rezults[$pRow['DealNumber']] = array (
				  'Deal' => new SimpleXMLElement($pRow['Deal']),
				  'Vehicle' => new SimpleXMLElement($pRow['Vehicle']),
				  'Customer' => new SimpleXMLElement($pRow['Customer']),
			   );

		  }
		 // var_dump($rezults);


?>
      </tr>
      </table>
    </div>
    <div class = "totals">
    	<p>Totals : <?php echo $mTotals ?></p>
    </div>
		<table>

    <?php

            $i = 1;
			$html = '';
			$salesCount = array();
			$html .= '<table>';
            foreach($rezults as $key => $pRow)
            {
                $sDeal = $pRow['Deal'];
                $vResult = $pRow['Vehicle'];
                $custResult = $pRow['Customer'];
				$companyNumber = $sDeal -> CompanyNumber;
          		$sPerson = $sDeal -> SalesPersons -> SalesPerson -> SalesPersonName;
				$sPersonId = $sDeal -> SalesPersons -> SalesPerson -> SalesPersonID;
				$sPersonType =  $sDeal -> SalesPersons -> SalesPerson -> SalesPersonType;
				$split = $sDeal -> SalesPersons -> SalesPerson -> SplitGross;
				$tradeResult = new Vehicle();
       		    $tradeResult = $tradeResult -> VehicleLookup(array("StockNumber"=> $result -> TradeIns -> TradeIn -> StockNumber));
				$trade = $sDeal -> TradeIns -> TradeIn -> StockNumber;
				$tradeAllowance = $sDeal->TradeAllowance;
				$date1 = new DateTime(date('Y-m-d', strtotime($vResult->DateInInventory)));
                $date2 = new DateTime(date('Y-m-d', strtotime($pRow['Merged'])));

				$sKey = $sPerson->__toString();
					//var_dump(array_key_exists($sKey, $salesCount));
				if (array_key_exists($sKey, $salesCount))
                   $salesCount[$sKey] = $salesCount[$sKey]+1;
                else
                  $salesCount[$sKey] = 1;
				 //var_dump($salesCount);

                if($companyNumber == "CJ1"){
                    $location = "SLC";
                }
                else{
                    $location = "Lindon";
                }

                $status = "";
                if($_POST['status'])
                {
                    $deal = new Deal();
                    $resulti = $deal -> DealLookup($sDeal->DealNumber);

                    $result = $resulti -> content -> DealLookupResponse -> Deal;
					// $salesRep = $resulti -> content -> DealLookupResponse -> Deal -> SalesPerson;


                    switch($result->RecordStatus){
                        case "A":
                            $status = "A";
                            break;
                        case "U":
                            $status = "C";
                            break;
                        default:
                            $status = "P";
                    }
                }

                $html .=  '<tr ';
                 if(fmod($i, 2) == 0)
                        $html .=   'bgcolor="#F7F7F7"';
                $html .=  '><td>';
                $html .=  $i;
                $i++;
                $html .=  '</td>';
				$html .=  '<td>'.$key.'</td>';
				$html .=  '<td>';
				$html .=  $sPerson;
				$html .=  '</td>';
                $html .=  '<td>';
                $html .=  $location;
                $html .=  '</td><td>';
                $html .=  $vResult->StockNumber;
                $html .=  '</td><td>';
                $html .=  $vResult->ModelYear;
                $html .=  '</td><td>';
                $html .=  $vResult->Make;
                $html .=  '</td><td>';
                $html .=  $vResult->Model;
                $html .=  '</td><td align="right">';
                $html .=  $vResult->ListPrice;
                $html .=  '</td><td align="right">';
                $html .=  $pRow['Discount'];

                $html .=  '</td><td align="center">';
                if($pRow['Merged'])
                    $html .=  date("m/d/Y", strtotime($pRow['Merged']));
                $html .=  '</td><td align="center">';
                $html .=  $status;
                $html .=  '</td><td align="right">';
				if ($trade != ""){
				$html .=  $trade. '-trade   ';
				$html .=  ' Trade allowance'.$tradeAllowance.'   ';
				}

                $interval = $date2->diff($date1);

                $html .=  $interval->days.' Interval days';
                $html .=  '</td></tr>';
            }
			$html .= '</table>';
			//var_dump($salesCount);
			$htmli = '<table><tr>';
			foreach($salesCount as $key => $count){

			   $nName = str_replace(' ', '_', $key);
			   $htmli .= "<td>";
			   $htmli .= '<img src ="../img'. $nName .'.jpg" width="100" onclick="salesMan("'. $nName .'") style="cursor:pointer" alt="' . $nName . '" onerror="src=\'../img/default.jpg\'"><br><b>';
			   $htmli .= $key . " " .$count;
			   $htmli .= "</td>";
				}
			$htmli .= "</tr></table>";
			echo $htmli;
			echo $html;
    }



?>

   </table>

<script type="text/javascript" src="../script/datepickr.min.js"></script>

<script type="text/javascript">

	new datepickr('datepick1', {
				'dateFormat': 'm/d/y'
			});
        new datepickr('datepick2', {
				'dateFormat': 'm/d/y'
			});

</script>
<script src="script/app.js"></script>