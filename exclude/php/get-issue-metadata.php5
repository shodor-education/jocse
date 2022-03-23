<?php
header("Content-Type: text/plain");
include_once("helpers.php5");

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

$query = <<<END
select SDRVersion.`id`
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

$issues = $sdrDbConn->query($query);
while ($article = $issues->fetch_assoc()) {
  $volume = getIntValue($article["id"], "Volume");
  $issue = getIntValue($article["id"], "Issue");
  $filename = "$volume-$issue";
  echo "FILENAME::$filename\n---\n";
  $results = getValues(
    "Text"
  , $article["id"]
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
  echo "issue: $issue\n";
  echoValues("Int", $article["id"], "Month", "month");
  echo "permalink: \"/issues/$volume/$issue\"\n";
  echoValuesArray("Text", $article["id"], "Subject", "subjects");
  echo "title: \"Volume $volume Issue $issue\"\n";
  echo "volume: $volume\n";
  echoValues("Int", $article["id"], "Year", "year");
  echo "---\n";
}

?>

