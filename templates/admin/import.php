<div class="wrap">
	<h1>Import Students</h1>
	<div class="card">
		<h2>Upload CSV</h2>
		<p>Upload a CSV file with the following columns: <code>admission_number</code>, <code>full_name</code>, <code>email</code>, <code>phone</code>, <code>course_id</code></p>
		<form id="sjs-cert-import-form" enctype="multipart/form-data">
			<p>
				<input type="file" name="csv_file" accept=".csv" required>
			</p>
			<p>
				<button type="submit" class="button button-primary">Import Students</button>
			</p>
		</form>
		<div id="sjs-cert-import-result" style="margin-top: 20px;"></div>
	</div>
</div>
