<?php

$databasehostname = 'mysql:dbname=test;host=127.0.0.1';
$databasename = "test";

$databasetable = "events";
$databasetableages0 = "ages0";
$databasetableages1 = "ages1";
$databasetableages2 = "ages2";
$databasetableUserBreakDown = "userbreakdown";


$databaseusername="root";
$databasepassword = "";
$fieldseparator = ",";
$lineseparator = "\n";
$csvfile = "csv/events2.csv";

if(!file_exists($csvfile)) {
    die("File not found. Make sure you specified the correct path.");
}

try {
    $pdo = new PDO($databasehostname, $databaseusername, $databasepassword,
            array(
                PDO::MYSQL_ATTR_LOCAL_INFILE => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ));
} catch (PDOException $e) {
    die("database connection failed: ".$e->getMessage());
}

//drop table to start with
try{
    $pdo -> query("DROP TABLE IF EXISTS `$databasename`.`$databasetable`") ;
    //echo "event table was dropped";
}catch(PDOException $e) {

    die("drop table failed: ".$e->getMessage());
}

//Create the  Events table
$myTable = "create table events(DateTime DATETIME , UID INTEGER(10) NOT NULL, Event varchar(25) NOT NULL)";
$pdo -> query($myTable);
$myTable = "create index idx0 on events(UID)";
$pdo -> query($myTable);

$affectedRows = $pdo->exec("
    LOAD DATA LOCAL INFILE ".$pdo->quote($csvfile)." INTO TABLE `$databasetable`
      FIELDS TERMINATED BY ".$pdo->quote($fieldseparator)."
      LINES TERMINATED BY ".$pdo->quote($lineseparator)." IGNORE 1 LINES ");


$subquery1 = $pdo -> query("select min(datetime) from events");
$minDate = $subquery1 -> fetch(PDO::FETCH_ASSOC);


//SignUp Data
$query1 = "SELECT TIMESTAMPDIFF(week, '2015-07-12', DateTime) AS weeks , count(*) AS signups FROM events WHERE event = 'signup' GROUP BY TIMESTAMPDIFF(week,'2015-07-12', DateTime) ORDER BY 1";
$graph1Data = $pdo -> query($query1);
$graph1rows = array();
while($r = $graph1Data -> fetch(PDO::FETCH_ASSOC)) {
    $graph1rows[] = $r;
}
$graph1Data = json_encode($graph1rows);

//User Visit Data
$query2 = "SELECT TIMESTAMPDIFF(week, '2015-07-12', DateTime) AS weeks , count(DISTINCT uid) AS userIds  FROM events  GROUP BY TIMESTAMPDIFF(week, '2015-07-12',DateTime) ORDER BY 1";
$graph2Data = $pdo -> query($query2);
$graph2rows = array();
while($r = $graph2Data -> fetch(PDO::FETCH_ASSOC)) {
    $graph2rows[] = $r;
}
$graph2Data = json_encode($graph2rows);

//retention rate by week age of user
//Im creating 3 intermediate table to make it much simpler

//drop tables to start with
try{
    $pdo -> query("DROP TABLE IF EXISTS `$databasename`.`$databasetableages0`") ;
    //echo "age0 table was dropped";

    $pdo -> query("DROP TABLE IF EXISTS `$databasename`.`$databasetableages1`") ;
    //echo "age1 table was dropped";

    $pdo -> query("DROP TABLE IF EXISTS `$databasename`.`$databasetableages2`") ;
    //echo "age2 table was dropped";
}catch(PDOException $e) {

    die("drop table failed: ".$e->getMessage());
}

//Create age0 table
$ages0Table = "create table ages0(uid INTEGER(10) , signupdt DATETIME)";
$pdo -> query($ages0Table);
$ages0Table = "create index idx0 on ages0(UID)";
$pdo -> query($ages0Table);

//Insert data into age0
$ages0Table = "insert into ages0 select distinct a.uid, a.datetime AS signupdt from events AS a where a.event='signup'";
$pdo -> query($ages0Table);

//Create age1 table
$ages1Table = "create table ages1(histwks INTEGER(10))";
$pdo -> query($ages1Table);


//Insert data into age0
$ages1Table = "insert into ages1 select TIMESTAMPDIFF(week,y.signupdt, z.maxdt) AS histwks from ages0 AS y cross join (select max(a.DateTime) as maxdt from events as a) as Z";
$pdo -> query($ages1Table);

//Create age1 table
$ages2Table = "create table ages2(uid INTEGER(10), visitage INTEGER(10))";
$pdo -> query($ages2Table);
$ages2Table = "create index idx0 on ages2(UID)";
$pdo -> query($ages2Table);

//Insert data into age0
$ages2Table = "insert into ages2 select DISTINCT x.uid, TIMESTAMPDIFF(week,y.signupdt, x.visitdt) AS visitage from (select a.uid, a.datetime as visitdt from events as a where event='visit') as x inner JOIN ages0 as y on x.uid = y.uid";
$pdo -> query($ages2Table);

//retention rate data

$query3 = "SELECT x.*, case when x.possible = 0 then 0 else (x.actual*1.00/x.possible) end AS retentionrate from
   (select x.wk,
   (select count(*) from ages1 as a where a.histwks >= x.wk) as possible,
   (select count(*) from ages2 as a where a.visitage = x.wk) as actual FROM
   (select histwks as wk from ages1 union select visitage from ages2) as x) as x order by 1";
$graph3Data = $pdo -> query($query3);
$graph3rows = array();
while($r = $graph3Data -> fetch(PDO::FETCH_ASSOC)) {
    $graph3rows[] = $r;
}
$graph3Data = json_encode($graph3rows);

//Data for new, retain, resurrencted and Churned
//Im creating a new table to store all these info


//drop tables to start with
try{
    $pdo -> query("DROP TABLE IF EXISTS `$databasename`.`$databasetableUserBreakDown`") ;
    //echo "userbreakdown table was dropped";

}catch(PDOException $e) {

    die("drop table failed: ".$e->getMessage());
}

//Create userbreakdown table
$userbreakdownTable = "create table userbreakdown(uid INTEGER(10), wk INTEGER (10), signup INTEGER(10), retained INTEGER(10), resurrected INTEGER(10), willchurn INTEGER(10))";
$pdo -> query($userbreakdownTable);
$userbreakdownTable = "create index idx0 on userbreakdown(UID)";
$pdo -> query($userbreakdownTable);

//Insert data into userbreakdown
$userbreakdownTable = "insert into userbreakdown
                        select
                        a.uid,
                        a.wk,
                        CASE WHEN a.event = 'signup' then 1 else 0 end AS signup,
                        CASE WHEN IFNULL(b.uid, -1) > -1 then 1 else 0 end AS retained,
                        CASE WHEN a.event = 'visit' and IFNULL(b.uid, -1)= -1 then 1 else 0 end AS resurrected,
                        CASE WHEN IFNULL(c.uid, -1) = -1 THEN 1 else 0 end AS willchurn
                        FROM
                        (SELECT a.uid, TIMESTAMPDIFF(week, '2015-07-12', a.DateTime) AS wk, min(a.event) AS event
                         FROM events AS a GROUP BY a.uid, timestampdiff(week,'2015-07-12',a.datetime)) AS a
                         LEFT OUTER JOIN
                         (SELECT a.uid, TIMESTAMPDIFF(week, '2015-07-12', a.DateTime) AS wk, min(a.event) AS event
                          FROM events AS a GROUP BY a.uid, TIMESTAMPDIFF(week,'2015-07-12',a.datetime)) AS b
                          on a.uid=b.uid and a.wk=b.wk+1
                          LEFT OUTER JOIN
                          (SELECT DISTINCT a.uid, TIMESTAMPDIFF(week,'2015-07-12',a.datetime) AS wk from events AS a ) AS c
                          on a.uid=c.uid and a.wk=c.wk-1";
$pdo -> query($userbreakdownTable);

$query4 = "SELECT
           a.wk AS wk,
           (SELECT sum(x.signup) from userbreakdown AS x WHERE x.wk = a.wk) AS signups,
           (SELECT sum(x.retained) from userbreakdown AS x WHERE x.wk = a.wk) AS retained,
           (SELECT sum(x.willchurn) from userbreakdown AS x WHERE x.wk = a.wk) AS churned,
           (SELECT sum(x.resurrected) from userbreakdown AS x WHERE x.wk = a.wk) AS resurrected
           FROM
           (SELECT DISTINCT wk from userbreakdown) AS a";
$graph4Data = $pdo -> query($query4);
$graph4rows = array();
while($r = $graph4Data -> fetch(PDO::FETCH_ASSOC)) {
    $graph4rows[] = $r;
}
$graph4Data = json_encode($graph4rows);
$pdo = null;


?>
