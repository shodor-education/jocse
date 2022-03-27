<?php
include_once("passwords.php5");

$DB_SERVER = "mysql-be-yes-I-really-mean-prod.shodor.org";

$CSERD_DB_NAME = "db_cserd";
$CSERD_DB_USER = "adm_cserd";

$SNAP2_DB_NAME = "db_snap";
$SNAP2_DB_USER = "db_snap_user";

$SDR_DB_NAME = "db_sdr";
$SDR_DB_USER = "search_sdr";

$cserdDbConn = new mysqli($DB_SERVER, $CSERD_DB_USER, $CSERD_DB_PASS, $CSERD_DB_NAME);
if ($cserdDbConn->connect_error) {
  die("Database connection failed: " . $cserdDbConn->connect_error);
}

$snap2DbConn = new mysqli($DB_SERVER, $SNAP2_DB_USER, $SNAP2_DB_PASS, $SNAP2_DB_NAME);
if ($snap2DbConn->connect_error) {
  die("Database connection failed: " . $snap2DbConn->connect_error);
}

$sdrDbConn = new mysqli($DB_SERVER, $SDR_DB_USER, $SDR_DB_PASS, $SDR_DB_NAME);
if ($sdrDbConn->connect_error) {
  die("Database connection failed: " . $sdrDbConn->connect_error);
}

function echoEducationLevels($articleId) {
  $results = getValues(
    "Text"
  , $articleId
  , "Education_Level"
  );
  $educationLevels = array();
  while ($result = $results->fetch_assoc()) {
    array_push($educationLevels, $result["entry"]);
  }
  usort($educationLevels, "sortEducationLevels");
  echo "education-levels:\n";
  foreach ($educationLevels as $educationLevel) {
    echo "  - \"$educationLevel\"\n";
  }
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
    $str = str_replace("\\", "\\\\", $result["entry"]);
    echo str_replace("\"", "\\\"", $str);
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

function getIntValue($articleId, $fieldName) {
  $results = getValues("Int", $articleId, $fieldName);
  if ($results->num_rows == 0) {
    $results = getValues("IntText", $articleId, $fieldName);
  }
  $result = $results->fetch_assoc();
  return $result["entry"];
}

function getValues($type, $articleId, $fieldName, $orderBy = "entry") {
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
order by $orderBy
END;
  return $sdrDbConn->query($query);
}

function sortEducationLevels($a, $b) {
  $sortedOptions = array(
    "Elementary School"
  , "Upper Elementary"
  , "Grade 3"
  , "Grade 4"
  , "Grade 5"
  , "Middle School"
  , "Grade 6"
  , "Grade 7"
  , "Grade 8"
  , "High School"
  , "Grade 9"
  , "Grade 10"
  , "Grade 11"
  , "Grade 12"
  , "Higher Education"
  , "Undergraduate (Lower Division)"
  , "Undergraduate (Upper Division)"
  , "Graduate/Professional"
  );
  return array_search($a, $sortedOptions)
  - array_search($b, $sortedOptions);
};

?>

