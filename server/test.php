<?php

require 'vendor/autoload.php';

//use Ramsey\Uuid\Uuid;

/*
for($i=1;$i<=624;$i++)
{
	echo "INSERT INTO ul_settings (`ul_id`,`settings`,`created`,`updated`,`last_update_user_id`,`thanks_mail_benevole`,`thanks_mail_benevole1j`,`token_benevole`,`token_benevole_1j`) VALUES ( $i,'{}','2019-02-27 16:47:34','2019-02-27 16:47:34',163,'','','".Uuid::uuid4()."','".Uuid::uuid4()."');\n";
}
*/


$sql =  "
SELECT  q.`id`,
        q.`email`,
        q.`first_name`,
        q.`last_name`,
        q.`secteur`,
        q.`nivol`,
        q.`mobile`,
        q.`created`,
        q.`updated`,
        q.`ul_id`,
        q.`notes`,
        q.`active`,
        q.`man`,
        q.`birthdate`,
        q.`qr_code_printed`,
       tq.`point_quete_id`,
       pq.name AS 'point_quete_name',
       tq.`depart_theorique`,       
       tq.`depart`, 
       tq.`retour`,
       u.name       as 'ul_name',
       u.latitude   as 'ul_latitude',
       u.longitude  as 'ul_longitude'
FROM  queteur     AS q LEFT JOIN tronc_queteur tq ON q.id = tq.queteur_id,
      point_quete AS pq, 
               ul AS u
WHERE  q.ul_id = :ul_id
AND    q.ul_id = u.id
AND    q.active= :active
AND  
(
  (
        tq.id IS NOT NULL
    AND tq.point_quete_id = pq.id
    AND tq.id = 
    ( 
        SELECT tqq.id 
        FROM  tronc_queteur tqq
        WHERE tqq.queteur_id = q.id
        ORDER BY tqq.depart_theorique DESC
        LIMIT 1
    )
  )
  OR 
  (
        tq.id IS NULL
    AND pq.id = 0
  )
)
ORDER BY q.last_name ASC
";

$sqlCount = "select count(1) as row_count FROM ". explode("FROM", $sql, 2)[1];

echo $sqlCount;

?>



<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<title>Géolocalisation</title>

</head>
<body>
<?php
$latitude=$_POST["latitude"];
$longitude=$_POST["longitude"];
$id= $_POST["id"];
echo "Coordonnées GPS de l'identifiant : ".$id." ".$latitude." | ".$longitude."<br />";//le petit affichage
$gps_xml = simplexml_load_file("gps.xml");
$date=date("d M Y, G : i");
foreach ($gps_xml->identite as $identite) {
	if ($identite['id']==$id) {
	$identite->latitude=$latitude;
	$identite->longitude=$longitude;
        $identite->date=$date;
        }
}
$gps_xml->asXML("gps.xml");
?>
</body> </html>
