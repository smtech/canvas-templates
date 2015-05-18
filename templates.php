<?php

header('Content-Type: application/javascript');

require_once('config.inc.php');
require_once(SMCANVASLIB_PATH . '/include/canvas-api.inc.php');
require_once(SMCANVASLIB_PATH . '/include/cache.inc.php');

preg_match('|.*/courses/(\d+)(/.*)?|', $_REQUEST['location'], $matches);
$courseId = $matches[1]; // FIXME validation

$templatesHtml = getCache('key', "templates-$courseId", 'data');

if (!$templatesHtml) {
	$api = new CanvasApiProcess(CANVAS_API_URL, CANVAS_API_TOKEN);
	
	$assignmentTemplates = $api->get("/courses/2596/assignments",array(
		'search_term' => TEMPLATE_TAG
	));
	$templatesHtml = '<form id="stmarks-templates-form" method="post" action="' . APP_URL . '/template-copy.php"><label>My templates</label><select name="template_id"><option disabled selected>Choose a template</option>';
	if (sizeof($assignmentTemplates > 0)) {
		$templatesHtml .= '<optgroup label="Assignments">';
		foreach($assignmentTemplates as $assignmentTemplate) {
			$templateName = trim(str_replace(TEMPLATE_TAG, '', $assignmentTemplate['name']));
			$templatesHtml .= '<option value="assignments' . TYPE_SEPARATOR . '/courses/' . $courseId . '/assignments/' . $assignmentTemplate['id'] . '">' . $templateName . '</option>';
		}
		$templatesHtml .= '</optgroup>';
	}
	
	$discussionTemplates = $api->get("/courses/2596/discussion_topics",array(
		'search_term' => TEMPLATE_TAG
	));
	if (sizeof($discussionTemplates > 0)) {
		$templatesHtml .= '<optgroup label="Discussions">';
		foreach($discussionTemplates as $discussionTemplate) {
			$templateName = trim(str_replace(TEMPLATE_TAG, '', $discussionTemplate['title']));
			$templatesHtml .= '<option value="discussion_topics' . TYPE_SEPARATOR . '/courses/' . $courseId . '/discussion_topics/' . $discussionTemplate['id'] . '">' . $templateName . '</option>';
		}
		$templatesHtml .= '</optgroup>';
	}

	$pageTemplates = $api->get("/courses/2596/pages",array(
		'search_term' => TEMPLATE_TAG
	));
	if (sizeof($pageTemplates > 0)) {
		$templatesHtml .= '<optgroup label="Pages">';
		foreach($pageTemplates as $pageTemplate) {
			$templateName = trim(str_replace(TEMPLATE_TAG, '', $pageTemplate['title']));
			$templatesHtml .= '<option value="pages' . TYPE_SEPARATOR . '/courses/' . $courseId . '/pages/' . $pageTemplate['url'] . '">' . $templateName . '</option>';
		}
		$templatesHtml .= '</optgroup>';
	}

	$templatesHtml .= '<option value="rebuild@' . $courseId . '">Rebuild Template List</option>';
	$templatesHtml .= '</select><input type="submit" value="Create" />';
	setCache('key', "templates-$courseId", 'data', $templatesHtml);
}

?>
function stmarks_addTemplatesButton(courseSecondary) {
	var announcementsUrl = /courses\/\d+\/discussion_topics/;
	var newAnnouncementButton = null;
	var courseOptions = courseSecondary.getElementsByClassName('course-options')[0].children;
	for (var i = 0; i < courseOptions.length; i++) {
		if (announcementsUrl.test(courseOptions[i].href)) {
			newAnnouncementButton = courseOptions[i];
		} 
	}
	if (newAnnouncementButton != null) {
		var courseUrl = /.*\/courses\/(\d+).*/;
		var courseId = document.location.href.match(courseUrl)[1];
		var templatesButton = document.createElement('div');
		templatesButton.id = 'stmarks_templates';
		templatesButton.innerHTML = '<?= $templatesHtml ?>';
		newAnnouncementButton.parentElement.appendChild(templatesButton);
	}
}

function stmarks_templates() {
	stmarks_waitForDOMById(/courses\/\d+/, 'course_show_secondary', stmarks_addTemplatesButton);
}
