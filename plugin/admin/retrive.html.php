<th>
<h2>Showing <?=Inflector::classify($table)?> <?=ID?></h2>
<h3>Attributes</h3>
<ul>
<?php
$attributes = $model->attributes_info();
foreach ($attributes as $attr) {
	$value = $data[$attr["name"]];
?>
	<li>
	<b><?=$attr["name"]."&lt;<i>".$attr["type"]."</i>&gt;"?></b>: <?=$value?>
	</li>	
<?}?>
</ul>
</th>