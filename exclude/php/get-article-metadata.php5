<?php
header("Content-Type: text/plain");
include_once("helpers.php5");

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
  echoEducationLevels($article["id"]);
  echo "end-page: $endPage\n";
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

