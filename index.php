<?php
/**
 * Summary.
 * Script to calculate Egypt groups in wc18
 *
 * @author Amr Aly <amrkhalid@outlook.com>
 */

if (!isset($_GET['req'])) {
    echo <<<HTML
<html>
<head>
<style>
body  {
    background-image: url("https://upload.wikimedia.org/wikipedia/en/6/67/2018_FIFA_World_Cup.svg");
    background-repeat: no-repeat;

}
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/css/bootstrap-select.min.css">
<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/js/bootstrap-select.min.js"></script>

  <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-38783602-2"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-38783602-2');
</script>
</head>
<body>
<div style="text-align:center">
<h1>Possible Group Combos</h1></div>
HTML;
    echo '<div style="width: 800px; margin: auto;">';
    $pot1 = array("russia", "germany", "brazil", "portugal", "argentina", "belgium",
        "poland", "france");
    $pot2 = array("spain", "peru", "switzerland", "england", "colombia", "mexico",
        "uruguay", "croatia");
    $pot3 = array("denmark", "iceland", "costa rica", "sweden", "tunisia", "egypt",
        "senegal", "iran");
    $pot4 = array("serbia", "nigeria", "australia", "japan", "morocco", "panama",
        "south korea", "saudi arabia");
    $list = array_merge($pot1, $pot2, $pot3, $pot4);
    echo '<div style="margin-bottom: 10">';
    echo '<select name="teamselect" title="Choose a country"  id="teamselect" style="" class="selectpicker" onchange="if (this.selectedIndex) updateTable();" data-style="btn-default" >';
    foreach ($list as $team) {
        echo ' <option value="' . $team . '">' . ucwords($team) . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo <<<HTML


<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%" cellspacing="0" width="100%">
<thead>
    <tr>
        <th>Team 1</th>
        <th>Team 2</th>
        <th>Team 3</th>
        <th>Team 4</th>
        <th>Difficulity Score</th>
    </tr>
</thead>
<tfoot>
    <tr>
    <th>Team 1</th>
    <th>Team 2</th>
    <th>Team 3</th>
    <th>Team 4</th>
    <th>Difficulity Score</th>
    </tr>
</tfoot>
</table>
</div>
</body>
<footer id="myFooter">
        <div class="container">
            <div class="row">
                <div class="col-sm-3 myCols">
Amr K. Aly

            </div>
        </div>
        <div class="social-networks">
            <a href="https://twitter.com/amr0khalid" class="twitter"><i class="fa fa-twitter"></i></a>
            <a href="https://fb.com/afroty" class="facebook"><i class="fa fa-facebook-official"></i></a>
        </div>
        <div class="footer-copyright">
            <p>© 2017 Copyright Text </p>
        </div>
    </footer>
<script type="text/javascript">
var link = "index.php/?req=data&country="+location.hash.slice(1);
$(document).ready(function() {
    $('#example').DataTable( {
        "processing": true,
        "serverSide": false,
        "ajax": link
    } );
} );
function updateTable(){
var sel = document.getElementById('teamselect');
var opt = sel.options[sel.selectedIndex];
window.location ="#"+opt.value;
window.location.reload();

}
</script>
</html>
HTML;
    return;
}
$ch = curl_init();

// set url
curl_setopt($ch, CURLOPT_URL, "http://www.fifa.com/common/fifa-world-ranking/_ranking_matchpoints_totals.js");

//return the transfer as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
$fifaRatings = json_decode($output, true);
curl_close($ch);

//LIST OF TEAMS BY Confederation

$teampot4 = $_GET['country'];
$counter = 0;
$pots = whichPots($teampot4);
$possibleGroupsArr = array();
foreach ($pots[0] as $teampot1) {
    foreach ($pots[1] as $teampot2) {
        foreach ($pots[2] as $teampot3) {
            if (confederationErrorCheck(array($teampot1, $teampot2, $teampot3, $teampot4)) == false) {
                continue;
            }
            array_push($possibleGroupsArr, array(ucwords($teampot4), ucwords($teampot2), ucwords($teampot3), ucwords($teampot1), 0));
            $counter++;
        }
    }

}
calculateScore($possibleGroupsArr);
appendflag($possibleGroupsArr);
//echo '<pre>';
//print_r($possibleGroupsArr);
//echo "<br>Number of different groups: " . $counter;
if (isset($_GET['req']) && $_GET['req'] == 'data') {

    echo json_encode(array(
        "draw" => isset($request['draw']) ?
        intval($request['draw']) :
        1,
        "recordsTotal" => 316,
        "recordsFiltered" => 10,
        "data" => $possibleGroupsArr,
    )
    );
    return;
}
/**
 * To check the confederation redundancy
 *
 * @param mixed[] $teamArray gets array of 4 teams
 *
 * @return bool
 */
function confederationErrorCheck(array $teamArray)
{
    $confarr = array('caf' => 0, 'afc' => 0, 'concaf' => 0, 'conmebol' => 0);
    $uefaCounter = 0;
    foreach ($teamArray as $team) {
        if (teamConfChek($team) == "uefa") {
            $uefaCounter++;
            continue;
        }
        //   echo $team . '<br>';
        $confarr[teamConfChek($team)]++;

    }
    //print_r($confarr);
    //echo $uefaCounter . '<br>';
    if ($uefaCounter > 2) {
        //echo "False: ";
        return false;
    }

    if (max($confarr) > 1) {
        //echo "False: ";

        return false;
    }
    return true;
}

/**
 * To check which conf team belongs to
 *
 * @param string $team teamname
 *
 * @return string
 */
function teamConfChek($team)
{
    $caf = array("Algeria", "Angola", "Benin", "Botswana", "Burkina", "Faso", "Burundi",
        "Cameroon", "Cape", "Verde", "Central", "African", "Republic", "Chad",
        "Comoros", "Congo", "Democratic", "Republic", "of", "the", "Congo", "Côte",
        "d'Ivoire", "Djibouti", "egypt", "Equatorial", "Guinea", "", "Región", "Continental"
        , "Región", "Insular", "Eritrea", "Ethiopia", "Gabon", "Gambia", "Ghana", "Guinea",
        "Guinea-Bissau", "Kenya", "Lesotho", "Liberia", "Libya", "Madagascar", "Malawi", "Mali",
        "Mauritania", "Mauritius", "morocco", "Mozambique", "Namibia", "Niger", "nigeria", "Réunion",
        "Rwanda", "São", "Tomé", "and", "Príncipe", "senegal", "Seychelles", "Sierra", "Leone", "Somalia",
        "South", "Africa", "South", "South Sudan", "Sudan", "Swaziland", "Tanzania", "Togo", "tunisia",
        "Uganda", "Zambia", "Zanzibar", "Zimbabwe");

    $concaf = array("mexico", "costa rica", "panama");

    $afc = array("iran", "japan", "south korea", "saudi arabia", "australia");

    $conmebol = array("brazil", "uruguay", "argentina", "colombia", "peru");

    $uefa = array("russia", "france", "portugal", "germany", "serbia", "poland",
        "england", "spain", "belgium", "iceland", "switzerland", "croatia", "denmark",
        "sweden");
    return in_array($team, $caf) == 1 ? "caf" : (in_array($team, $concaf) == true ?
        "concaf" : (in_array($team, $conmebol) == true ? "conmebol" :
            (in_array($team, $uefa) == true ? "uefa" : (in_array($team, $afc) == true ? "afc" : "ERROR"))));

}
/**
 * To check which conf team belongs to
 *
 * @param string $team teamname
 *
 * @return array array of pots
 */
function whichPots($team)
{
    $pot1 = array("russia", "germany", "brazil", "portugal", "argentina", "belgium",
        "poland", "france");
    $pot2 = array("spain", "peru", "switzerland", "england", "colombia", "mexico",
        "uruguay", "croatia");
    $pot3 = array("denmark", "iceland", "costa rica", "sweden", "tunisia", "egypt",
        "senegal", "iran");
    $pot4 = array("serbia", "nigeria", "australia", "japan", "morocco", "panama",
        "south korea", "saudi arabia");

    return in_array($team, $pot1) == true ? array($pot2, $pot3, $pot4) :
    (in_array($team, $pot2) == true ? array($pot1, $pot3, $pot4) :
        (in_array($team, $pot3) == true ? array($pot2, $pot1, $pot4) :
            (in_array($team, $pot4) == true ? array($pot2, $pot3, $pot1) : (
                false
            ))));
}
/**
 * Computes difficulity and sorts groups based on that
 *
 * @param mixed[] $possibleGroupsArr array of possible groups
 *
 * @return void
 */
function calculateScore(array &$possibleGroupsArr)
{
    foreach ($possibleGroupsArr as &$group) {
        $group[4] = getTeamScore($group[1]) + getTeamScore($group[2]) + getTeamScore($group[3]);
    }
    usort($possibleGroupsArr, 'compareOrder');

}
/**
 * Sorting Rule
 *
 * @param mixed[] $a array a
 * @param mixed[] $b array b
 *
 * @return void
 */
function compareOrder($a, $b)
{
    return $b[4] - $a[4];
}
/**
 * Gets team score
 *
 * @param string $team team name
 *
 * @return float
 */
function getTeamScore($team)
{
    $abbrvToFullname = array('ALB' => 'ALBANIA',
        'DZA' => 'ALGERIA',
        'ASM' => 'AMERICAN SAMOA',
        'AND' => 'ANDORRA',
        'AGO' => 'ANGOLA',
        'AIA' => 'ANGUILLA',
        'ATA' => 'ANTARCTICA',
        'ATG' => 'ANTIGUA AND BARBUDA',
        'ARG' => 'ARGENTINA',
        'ARM' => 'ARMENIA',
        'ABW' => 'ARUBA',
        'AUS' => 'AUSTRALIA',
        'AUT' => 'AUSTRIA',
        'AZE' => 'AZERBAIJAN',
        'BHS' => 'BAHAMAS',
        'BHR' => 'BAHRAIN',
        'BGD' => 'BANGLADESH',
        'BRB' => 'BARBADOS',
        'BLR' => 'BELARUS',
        'BEL' => 'BELGIUM',
        'BLZ' => 'BELIZE',
        'BEN' => 'BENIN',
        'BMU' => 'BERMUDA',
        'BTN' => 'BHUTAN',
        'BOL' => 'BOLIVIA',
        'BIH' => 'BOSNIA AND HERZEGOWINA',
        'BWA' => 'BOTSWANA',
        'BVT' => 'BOUVET ISLAND',
        'BRA' => 'BRAZIL',
        'IOT' => 'BRITISH INDIAN OCEAN TERRITORY',
        'BRN' => 'BRUNEI DARUSSALAM',
        'BGR' => 'BULGARIA',
        'BFA' => 'BURKINA FASO',
        'BDI' => 'BURUNDI',
        'KHM' => 'CAMBODIA',
        'CMR' => 'CAMEROON',
        'CAN' => 'CANADA',
        'CPV' => 'CAPE VERDE',
        'CYM' => 'CAYMAN ISLANDS',
        'CAF' => 'CENTRAL AFRICAN REPUBLIC',
        'TCD' => 'CHAD',
        'CHL' => 'CHILE',
        'CHN' => 'CHINA',
        'CXR' => 'CHRISTMAS ISLAND',
        'CCK' => 'COCOS ISLANDS',
        'COL' => 'COLOMBIA',
        'COM' => 'COMOROS',
        'COG' => 'CONGO',
        'COD' => 'CONGO, THE DRC',
        'COK' => 'COOK ISLANDS',
        'CRC' => 'COSTA RICA',
        'CIV' => 'COTE D IVOIRE',
        'CRO' => 'CROATIA',
        'CUB' => 'CUBA',
        'CYP' => 'CYPRUS',
        'CZE' => 'CZECH REPUBLIC',
        'DEN' => 'DENMARK',
        'DJI' => 'DJIBOUTI',
        'DMA' => 'DOMINICA',
        'DOM' => 'DOMINICAN REPUBLIC',
        'TMP' => 'EAST TIMOR',
        'ECU' => 'ECUADOR',
        'EGY' => 'EGYPT',
        'SLV' => 'EL SALVADOR',
        'GNQ' => 'EQUATORIAL GUINEA',
        'ERI' => 'ERITREA',
        'EST' => 'ESTONIA',
        'ETH' => 'ETHIOPIA',
        'FLK' => 'FALKLAND ISLANDS',
        'FRO' => 'FAROE ISLANDS',
        'FJI' => 'FIJI',
        'FIN' => 'FINLAND',
        'FRA' => 'FRANCE',
        'FXX' => 'FRANCE, METROPOLITAN',
        'GUF' => 'FRENCH GUIANA',
        'PYF' => 'FRENCH POLYNESIA',
        'ATF' => 'FRENCH SOUTHERN TERRITORIES',
        'GAB' => 'GABON',
        'GMB' => 'GAMBIA',
        'GEO' => 'GEORGIA',
        'GER' => 'GERMANY',
        'GHA' => 'GHANA',
        'GIB' => 'GIBRALTAR',
        'GRC' => 'GREECE',
        'GRL' => 'GREENLAND',
        'GRD' => 'GRENADA',
        'GLP' => 'GUADELOUPE',
        'GUM' => 'GUAM',
        'GTM' => 'GUATEMALA',
        'GIN' => 'GUINEA',
        'GNB' => 'GUINEA-BISSAU',
        'GUY' => 'GUYANA',
        'HTI' => 'HAITI',
        'HMD' => 'HEARD AND MC DONALD ISLANDS',
        'VAT' => 'HOLY SEE (VATICAN CITY STATE)',
        'HND' => 'HONDURAS',
        'HKG' => 'HONG KONG',
        'HUN' => 'HUNGARY',
        'ISL' => 'ICELAND',
        'IND' => 'INDIA',
        'IDN' => 'INDONESIA',
        'IRN' => 'IRAN',
        'IRQ' => 'IRAQ',
        'IRL' => 'IRELAND',
        'ISR' => 'ISRAEL',
        'ITA' => 'ITALY',
        'JAM' => 'JAMAICA',
        'JPN' => 'JAPAN',
        'JOR' => 'JORDAN',
        'KAZ' => 'KAZAKHSTAN',
        'KEN' => 'KENYA',
        'KIR' => 'KIRIBATI',
        'PRK' => 'D.P.R.O. KOREA',
        'KOR' => 'SOUTH KOREA',
        'KWT' => 'KUWAIT',
        'KGZ' => 'KYRGYZSTAN',
        'LAO' => 'LAOS',
        'LVA' => 'LATVIA',
        'LBN' => 'LEBANON',
        'LSO' => 'LESOTHO',
        'LBR' => 'LIBERIA',
        'LBY' => 'LIBYAN ARAB JAMAHIRIYA',
        'LIE' => 'LIECHTENSTEIN',
        'LTU' => 'LITHUANIA',
        'LUX' => 'LUXEMBOURG',
        'MAC' => 'MACAU',
        'MKD' => 'MACEDONIA',
        'MDG' => 'MADAGASCAR',
        'MWI' => 'MALAWI',
        'MYS' => 'MALAYSIA',
        'MDV' => 'MALDIVES',
        'MLI' => 'MALI',
        'MLT' => 'MALTA',
        'MHL' => 'MARSHALL ISLANDS',
        'MTQ' => 'MARTINIQUE',
        'MRT' => 'MAURITANIA',
        'MUS' => 'MAURITIUS',
        'MYT' => 'MAYOTTE',
        'MEX' => 'MEXICO',
        'FSM' => 'FEDERATED STATES OF MICRONESIA',
        'MDA' => 'REPUBLIC OF MOLDOVA',
        'MCO' => 'MONACO',
        'MNG' => 'MONGOLIA',
        'MSR' => 'MONTSERRAT',
        'SRB' => 'SERBIA',
        'MAR' => 'MOROCCO',
        'MOZ' => 'MOZAMBIQUE',
        'MMR' => 'MYANMAR',
        'NAM' => 'NAMIBIA',
        'NRU' => 'NAURU',
        'NPL' => 'NEPAL',
        'NLD' => 'NETHERLANDS',
        'ANT' => 'NETHERLANDS ANTILLES',
        'NCL' => 'NEW CALEDONIA',
        'NZL' => 'NEW ZEALAND',
        'NIC' => 'NICARAGUA',
        'NER' => 'NIGER',
        'NGA' => 'NIGERIA',
        'NIU' => 'NIUE',
        'NFK' => 'NORFOLK ISLAND',
        'MNP' => 'NORTHERN MARIANA ISLANDS',
        'NOR' => 'NORWAY',
        'OMN' => 'OMAN',
        'PAK' => 'PAKISTAN',
        'PLW' => 'PALAU',
        'PAN' => 'PANAMA',
        'PNG' => 'PAPUA NEW GUINEA',
        'PRY' => 'PARAGUAY',
        'PER' => 'PERU',
        'PHL' => 'PHILIPPINES',
        'PCN' => 'PITCAIRN',
        'POL' => 'POLAND',
        'POR' => 'PORTUGAL',
        'PRI' => 'PUERTO RICO',
        'QAT' => 'QATAR',
        'REU' => 'REUNION',
        'ROM' => 'ROMANIA',
        'RUS' => 'RUSSIA',
        'RWA' => 'RWANDA',
        'KNA' => 'SAINT KITTS AND NEVIS',
        'LCA' => 'SAINT LUCIA',
        'VCT' => 'SAINT VINCENT AND THE GRENADINES',
        'WSM' => 'SAMOA',
        'SMR' => 'SAN MARINO',
        'STP' => 'SAO TOME AND PRINCIPE',
        'KSA' => 'SAUDI ARABIA',
        'SEN' => 'SENEGAL',
        'SYC' => 'SEYCHELLES',
        'SLE' => 'SIERRA LEONE',
        'SGP' => 'SINGAPORE',
        'SVK' => 'SLOVAKIA',
        'SVN' => 'SLOVENIA',
        'SLB' => 'SOLOMON ISLANDS',
        'SOM' => 'SOMALIA',
        'ZAF' => 'SOUTH AFRICA',
        'SGS' => 'SOUTH GEORGIA AND SOUTH S.S.',
        'ESP' => 'SPAIN',
        'LKA' => 'SRI LANKA',
        'SHN' => 'ST. HELENA',
        'SPM' => 'ST. PIERRE AND MIQUELON',
        'SDN' => 'SUDAN',
        'SUR' => 'SURINAME',
        'SJM' => 'SVALBARD AND JAN MAYEN ISLANDS',
        'SWZ' => 'SWAZILAND',
        'SWE' => 'SWEDEN',
        'SUI' => 'SWITZERLAND',
        'SYR' => 'SYRIAN ARAB REPUBLIC',
        'TWN' => 'TAIWAN, PROVINCE OF CHINA',
        'TJK' => 'TAJIKISTAN',
        'TZA' => 'UNITED REPUBLIC OF TANZANIA',
        'THA' => 'THAILAND',
        'TGO' => 'TOGO',
        'TKL' => 'TOKELAU',
        'TON' => 'TONGA',
        'TTO' => 'TRINIDAD AND TOBAGO',
        'TUN' => 'TUNISIA',
        'TUR' => 'TURKEY',
        'TKM' => 'TURKMENISTAN',
        'TCA' => 'TURKS AND CAICOS ISLANDS',
        'TUV' => 'TUVALU',
        'UGA' => 'UGANDA',
        'UKR' => 'UKRAINE',
        'ARE' => 'UNITED ARAB EMIRATES',
        'ENG' => 'ENGLAND',
        'USA' => 'UNITED STATES',
        'UMI' => 'U.S. MINOR ISLANDS',
        'URU' => 'URUGUAY',
        'UZB' => 'UZBEKISTAN',
        'VUT' => 'VANUATU',
        'VEN' => 'VENEZUELA',
        'VNM' => 'VIET NAM',
        'VGB' => 'VIRGIN ISLANDS (BRITISH)',
        'VIR' => 'VIRGIN ISLANDS (U.S.)',
        'WLF' => 'WALLIS AND FUTUNA ISLANDS',
        'ESH' => 'WESTERN SAHARA',
        'YEM' => 'YEMEN',
        'YUG' => 'Yugoslavia',
        'ZMB' => 'ZAMBIA',
        'ZWE' => 'ZIMBABWE');

    // create curl resource

    // $output contains the output string
    foreach ($GLOBALS['fifaRatings'] as $teamInfo) {
        $score = array_search(array_search(strtoupper($team), $abbrvToFullname), $teamInfo, true) == true ? $teamInfo['points'] : null;
        if ($score) {
            return $score;
        }

    }
    // close curl resource to free up system resources
}
function appendflag(array &$arr)
{
    $countryToFlagAbbv = array("russia" => "RU", "germany" => "DE", "brazil" => "BR", "portugal" => "PT", "argentina" => "AR", "belgium" => "BE",
        "poland" => "PL", "france" => "FR", "spain" => "ES", "peru" => "PE", "switzerland" => "CH", "england" => "GB", "colombia" => "CO", "mexico" => "MX",
        "uruguay" => "UY", "croatia" => "HR", "denmark" => "DK", "iceland" => "IS", "costa rica" => "CR", "sweden" => "SE", "tunisia" => "TN", "egypt" => "EG",
        "senegal" => "SN", "iran" => "IR", "serbia" => "RS", "nigeria" => "NG", "australia" => "AU", "japan" => "JP", "morocco" => "MA", "panama" => "PA",
        "south korea" => "KR", "saudi arabia" => "SA");

    $counter = 0;
    foreach ($arr as &$group) {
        foreach ($group as &$team) {
            if ($counter > 3) {
                continue;
            }
            $team .= '<div style="width:0;">
            <img style=" height: 40;width: 40;" class="" src="https://lipis.github.io/flag-icon-css/flags/4x3/' . strtolower($countryToFlagAbbv[strtolower($team)]) . '.svg" alt="Aland Islands Flag">
            </div>
            ';
            $counter++;
        }
        $counter = 0;
    }
}
