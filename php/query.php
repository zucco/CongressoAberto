<?php

  //The response should be utf8 encoded
	header('Content-Type: text/html; charset=utf-8');

//Include the extended API
include_once("gvServerAPIEx.php");

include_once("server.php");

//------------------------------------------

//-- Add here business logic, if needed
//-- For example users authentication and access control 

//------------------------------------------

// 2 parameters of the protocol are supported: tqx and responseHandler. 
// You should pass them as-is to the gvStreamerEx object
$tqx = $_GET['tqx'];
$resHandler = $_GET['responseHandler'];

// Read the data from MySql
// $host  = "mysql.cluelessresearch.com";
// $host  = "174.143.181.9";
// $con = mysql_connect($host,"monte","e123456");
// mysql_select_db("br", $con);
// $sql = "SELECT distinct state, count(party) as count FROM br_bio group by state";

//mysql_query("set names utf8");

if($_GET["form"]=="bills") {
  ## called from bill.php
  ## given a bill, return all related roll calls
  $billid=$_GET["billid"];  
  $sql = "select   
b.rcvoteid as 'Resultado',   
b.rcvoteid as 'Por partido', 
b.rcvoteid as 'Por estado', 
CAST(b.rcdate AS DATE) as Data,   
c.postid as Votacao, 
b.billdescription as Votacao, 
b.rcvoteid  from (select * from br_bills as d where d.billid=".$billid.") as a,  
br_votacoes as b, 
br_rcvoteidpostid as c  
where a.billyear=b.billyear and a.billno=b.billno and a.billtype=b.billtype  
and b.rcvoteid=c.rcvoteid
order by Data, a.billtype DESC" ;  
 #$sql = "select billyear from br_bills  where billid=440177";
 }

if($_GET["form"]=="allbills") {
  ##  return all bills
  $sql = "select *  from  br_bills order by billyear desc" ;  
  ##$sql = "select billyear from br_bills  where billid=37642";
 }


if($_GET["form"]=="votes") {
$sql = "select CAST(b.rcdate AS DATE) as Data, a.party as Partido, a.rc  as Voto, c.postid as `Votacao`    from 
	br_votos as a, 
	br_votacoes as b,
	br_rcvoteidpostid as c  
    where a.bioid=".$_GET["bioid"]." AND a.rcfile=b.rcfile AND b.rcvoteid=c.rcvoteid AND a.legis=53 order by Data DESC" ; 
    #$sql = "select * from br_votos limit 1";
	#echo $sql;
 }

if($_GET["form"]=="rcvotes") {
$sql = "select CAST(b.rcdate AS DATE) as Data, a.party as Partido, a.namelegis as nome, a.state as Estado, a.rc as Voto, b.bill as Proposicao, b.billdescription as Votacao, b.rcvoteid as ID  
	from 
		br_votos as a, 
		br_votacoes as b  
	where 
		a.rcvoteid=b.rcvoteid AND 
		a.rcvoteid=".$_GET["rcvoteid"]." order by Partido DESC" ;  
 }



if($_GET["form"]=="contrib") 
{
  $sql = "select a.donor as Doador, a.donortype as 'Tipo de doador', a.cpfcnpj as 'CPF/CNPJ do doador', a.contribsum as 'Valor da doacao' from br_contrib as a, br_bioidtse as b where b.bioid=".$_GET["bioid"]." AND a.candno=b.candidate_code AND a.state=b.state AND a.year=b.year order by a.contribsum DESC";
}


if($_GET["form"]=="legislist") 
{
  $sql = "SELECT d.postid as Nome, b.namelegis
  					, cast(a.party as binary) as Partido 
  					, cast(upper(a.state) as binary) as Estado
	  				, round(c.ausente_prop*100) `Faltas no ultimo ano (%)`
FROM
br_deputados_current as a,
br_bio as b,
br_ausencias as c,
br_bioidpostid as d
WHERE a.bioid=b.bioid and a.bioid=c.bioid and a.bioid=d.bioid 
";
}
// , , 
//, 
//order by Estado, Partido DESC

if($_GET["limit"]!="") 
  {
	$sql = $sql." limit ".$_GET["limit"];
  }


if($_GET["form"]=="test") 
  {
	$sql = "select * from br_votos limit 10"; 
  }



//concat(DAY(b.rcdate),'/',MONTH(b.rcdate),'/',YEAR(b.rcdate)) as year,
//+MONTH(b.rcdate)+'/'+YEAR(b.rcdate)
$result = mysql_query($sql);
if ($_GET["mode"]=="test") {
  echo $sql;
  $result = mysql_query($sql);
  $row = mysql_fetch_row($result);
  echo $row[0];
}
// Initialize the gvStreamerEx object
$gvJsonObj = new gvStreamerEx();

// If there will be an error during the inialization
// gvStreamerEx object will generate an error message
if($gvJsonObj->init($tqx, $resHandler) == true);
{
    //convert the entire query result into the compliant response
    $gvJsonObj->convertMysqlRes($result, "%01.0f", "d/m/Y", "G:i:s","d/m/Y G:i:s");
//    $gvJsonObj->setColumnPattern(3,"#0.0########");
}

// Close the connection to DB
mysql_close($con);
    
echo $gvJsonObj;


?>