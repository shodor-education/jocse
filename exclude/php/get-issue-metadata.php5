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
  $urls = getValues("Text", $article["id"], "Url");
  $url = $urls->fetch_assoc();
  $files["jocse-$volume-$issue"] = $url["entry"];
  echo "DIRECTORY::$volume/$issue\n---\n";
  $query = <<<END
select pubDate
from rssFeed
where link = "http://jocse.org/issues/$volume/$issue/"
END;
  $results = $cserdDbConn->query($query);
  $result = $results->fetch_assoc();
  echo "date: \"$result[pubDate]\"\n";
  echo "---\n";
}
foreach ($files as $name => $url) {
  echo "$name.pdf,$url\n";
}

?>

