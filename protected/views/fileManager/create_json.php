<?php
$response = array('model' => $model);
if ($model->hasErrors()) {
	$response['errors'] = $model->getErrors();
}
echo json_encode($response);