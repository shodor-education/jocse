<?php
header("Content-Type: text/plain");
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

$query = <<<END
select SDRVersion.`id`
from SDRResource
left join SDRResourceProject on SDRResourceProject.`cserdId` = SDRResource.`cserdId`
left join SDRVersion on SDRVersion.`cserdId` = SDRResource.`cserdId`
where SDRResourceProject.`projectId` = 13
and SDRVersion.`state` = "live"
END;

$files = array();
$articles = $sdrDbConn->query($query);
while ($article = $articles->fetch_assoc()) {
  $volume = getIntValue($article["id"], "Volume");
  $issue = getIntValue($article["id"], "Issue");
  $index = getIntValue($article["id"], "contentIndex");

  $urls = getValues("Text", $article["id"], "Url");
  $url = $urls->fetch_assoc();
  if ($index == 1000) {
    continue;
  }
  else if ($index == 1001) {
    $filename = "$volume-$issue-0";
    $index = 0;
  }
  else {
    $filename = "$volume-$issue-$index";
  }
  $files["jocse-$filename"] = $url["entry"];

  echo "FILENAME::$filename\n---\n";
  echoValues("Text", $article["id"], "Abstract", "abstract");
  echoValues("Text", $article["id"], "Additional_Resources", "additional-resources");
  echoValuesArray("Text", $article["id"], "Audience", "audiences");
  echo "authors:\n";
  $results = getValues("Text", $article["id"], "Creator");
  while ($result = $results->fetch_assoc()) {
    if ($result["entry"] == "The Shodor Education Foundation, Inc.") {
      $commaSplit = array($result["entry"]);
    }
    else {
      $commaSplit = explode(", ", $result["entry"]);
    }
    foreach ($commaSplit as $cs) {
      $andSplit = explode(" and ", $cs);
      foreach ($andSplit as $as) {
        $value = preg_replace("#^and #", "", $as);
        $value = str_replace("\"", "\\\"", $value);
        echo "  - \"$value\"\n";
      }
    }
  }
  $results = getValues("Text", $article["id"], "pageNumbers");
  $result = $results->fetch_assoc();
  $split = explode(" - ", $result["entry"]);
  if (count($split) == 1) {
    $split = explode("-", $result["entry"]);
  }
  $startPage = $split[0];
  if (count($split) == 1) {
    $endPage = $startPage;
  }
  else {
    $endPage = $split[1];
  }
  echo "end-page: $endPage\n";
  echoValuesArray("Text", $article["id"], "Education_Level", "education-levels");
  echo "index: $index\n";
  echo "issue: $issue\n";
  echoValuesArray("Text", $article["id"], "Keyword", "keywords");
  echo "permalink: \"articles/$volume/$issue/$index\"\n";
  echo "start-page: $startPage\n";
  echoValuesArray("Text", $article["id"], "Subject", "subjects");
  echoValues("Text", $article["id"], "Title", "title");
  echo "volume: $volume\n";
  echo "---\n";
}
foreach ($files as $name => $url) {
  echo "$name,$url\n";
}
?>

