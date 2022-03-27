<?php
header("Content-Type: text/plain");
include_once("helpers.php5");

$query = <<<END
select SDRVersion.`id`, SDRResource.`cserdId` as resourceId
from SDRResource
left join SDRResourceProject on SDRResourceProject.`cserdId` = SDRResource.`cserdId`
left join SDRVersion on SDRVersion.`cserdId` = SDRResource.`cserdId`
left join SDRVersionFieldValue on SDRVersionFieldValue.`versionId` = SDRVersion.`id`
left join SDRField on SDRField.`id` = SDRVersionFieldValue.`fieldId`
left join SDRIntValue on SDRIntValue.`valueId` = SDRVersionFieldValue.`valueId`
where SDRResourceProject.`projectId` = 13
and SDRVersion.`state` = "live"
and SDRField.`name` = "contentIndex"
and SDRIntValue.`entry` = 1000
END;

$files = array();
$issues = $sdrDbConn->query($query);
while ($article = $issues->fetch_assoc()) {
  $volume = getIntValue($article["id"], "Volume");
  $issue = getIntValue($article["id"], "Issue");
  $month = getIntValue($article["id"], "Month");
  $year = getIntValue($article["id"], "Year");
  $filename = "$volume-$issue";
  $urls = getValues("Text", $article["id"], "Url");
  $url = $urls->fetch_assoc();
  $files["jocse-$filename"] = $url["entry"];
  echo "FILENAME::$filename\n---\n";
  $query = <<<END
select pubDate
from rssFeed
where link = "http://jocse.org/issues/$volume/$issue/"
END;
  $results = $cserdDbConn->query($query);
  $result = $results->fetch_assoc();
  echo "date: \"$result[pubDate]\"\n";
  echoEducationLevels($article["id"]);
  echo "issue: $issue\n";
  echo "month: $month\n";
  echo "permalink: \"/issues/$volume/$issue\"\n";
  echoValuesArray("Text", $article["id"], "Subject", "subjects");
  $monthNames = array(
    ""
  , "January"
  , "February"
  , "March"
  , "April"
  , "May"
  , "June"
  , "July"
  , "August"
  , "September"
  , "October"
  , "November"
  , "December"
  );
  echo "title: \"Volume $volume Issue $issue &mdash; $monthNames[$month] $year\"\n";
  echo "volume: $volume\n";
  echo "year: $year\n";
  echo "---\n";
}
foreach ($files as $name => $url) {
  echo "$name.pdf,$url\n";
}

?>

