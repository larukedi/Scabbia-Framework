<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>media</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList />
	</info>
	<includeList>
		<include>media.php</include>
	</includeList>
	<classList>
		<class>media</class>
		<class>mediaFile</class>
	</classList>
	<events>
		<loadList>
			<load>media::extension_load</load>
		</loadList>
	</events>
</scabbia>