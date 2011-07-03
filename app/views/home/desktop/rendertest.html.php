<?foreach($data as $row):?>
	<?foreach($row as $name=>$value):?>
		<?=$name?>:<?=$value?><br/>
	<?endforeach;?>
	<hr/>
<?endforeach;?>