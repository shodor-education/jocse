<?php
include_once("passwords.php5");

$DB_SERVER = "mysql-be-yes-I-really-mean-prod.shodor.org";
$SNAP2_DB_NAME = "db_snap";
$SNAP2_DB_USER = "db_snap_user";

$SDR_DB_NAME = "db_sdr";
$SDR_DB_USER = "search_sdr";

$snap2DbConn = new mysqli($DB_SERVER, $SNAP2_DB_USER, $SNAP2_DB_PASS, $SNAP2_DB_NAME);
if ($snap2DbConn->connect_error) {
  die("Database connection failed: " . $snap2DbConn->connect_error);
}
$sdrDbConn = new mysqli($DB_SERVER, $SDR_DB_USER, $SDR_DB_PASS, $SDR_DB_NAME);
if ($sdrDbConn->connect_error) {
  die("Database connection failed: " . $sdrDbConn->connect_error);
}

function getValues($type, $articleId, $fieldName) {
  global $sdrDbConn;
  if ($type == "IntText") {
    $table = "SDRTextValue";
  }
  else {
    $table = "SDR{$type}Value";
  }
  $query = <<<END
select entry
from $table
left join SDRVersionFieldValue on SDRVersionFieldValue.`valueId` = $table.`valueId`
left join SDRField on SDRField.`id` = SDRVersionFieldValue.`fieldId`
where SDRField.`name` = "$fieldName"
and SDRVersionFieldValue.`versionId` = $articleId
order by entry
END;
  return $sdrDbConn->query($query);
}

function getIntValue($articleId, $fieldName) {
  $results = getValues("Int", $articleId, $fieldName);
  if ($results->num_rows == 0) {
    $results = getValues("IntText", $articleId, $fieldName);
  }
  $result = $results->fetch_assoc();
  return $result["entry"];
}

function echoValues($type, $articleId, $fieldName, $label) {
  $results = getValues($type, $articleId, $fieldName);
  if ($results->num_rows == 0 && $type == "Int") {
    echoValues("IntText", $articleId, $fieldName, $label);
  }
  while ($result = $results->fetch_assoc()) {
    echo "$label: ";
    if ($type == "Text") {
      echo "\"";
    }
    echo str_replace("\"", "\\\"", $result["entry"]);
    if ($type == "Text") {
      echo "\"";
    }
    echo "\n";
  }
}

function echoValuesArray($type, $articleId, $fieldName, $label) {
  echo "$label:\n";
  $results = getValues($type, $articleId, $fieldName);
  while ($result = $results->fetch_assoc()) {
    echo "  - \"$result[entry]\"\n";
  }
}

?>

