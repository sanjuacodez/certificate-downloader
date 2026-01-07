jQuery(document).ready(function ($) {
    // Import Form Handler (Batch with Progress Bar)
    $('#sjs-cert-import-form').on('submit', function (e) {
        e.preventDefault();

        var fileInput = $('#csv_file_input')[0];
        if (!fileInput.files.length) return;

        var file = fileInput.files[0];
        var reader = new FileReader();
        var result_div = $('#sjs-cert-import-result');
        var progress_div = $('#sjs-cert-import-progress');
        var progress_bar = progress_div.find('.progress-bar-inner');
        var progress_text = progress_div.find('.progress-text');

        result_div.html('<p class="spinner is-active" style="float:none;"></p> Preparing file...');

        reader.onload = function (e) {
            var contents = e.target.result;
            var lines = contents.split(/\r?\n/);
            var rows = [];

            // Simple CSV parser
            for (var i = 1; i < lines.length; i++) {
                if (lines[i].trim()) {
                    var row = lines[i].split(',').map(function (val) {
                        return val.replace(/^["']|["']$/g, '').trim(); // Basic CSV unquote
                    });
                    rows.push(row);
                }
            }

            if (rows.length === 0) {
                result_div.html('<div class="notice notice-error inline"><p>No data found in CSV.</p></div>');
                return;
            }

            result_div.html('<p>Importing ' + rows.length + ' students in batches...</p>');
            progress_div.show();
            progress_bar.css('width', '0%');
            progress_text.text('0%');

            var batchSize = 25;
            var totalBatches = Math.ceil(rows.length / batchSize);
            var successCount = 0;
            var errors = [];

            function sendBatch(index) {
                var start = index * batchSize;
                var end = Math.min(start + batchSize, rows.length);
                var batchRows = rows.slice(start, end);

                $.ajax({
                    url: sjs_cert_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'sjs_cert_import_batch_students',
                        nonce: sjs_cert_admin.nonce,
                        students: batchRows
                    },
                    success: function (response) {
                        if (response.success) {
                            successCount += response.data.imported;
                            if (response.data.errors) errors = errors.concat(response.data.errors);

                            var percent = Math.round(((index + 1) / totalBatches) * 100);
                            progress_bar.css('width', percent + '%');
                            progress_text.text(percent + '%');

                            if (index + 1 < totalBatches) {
                                sendBatch(index + 1);
                            } else {
                                finishImport();
                            }
                        } else {
                            result_div.append('<div class="notice notice-error inline"><p>' + response.data + '</p></div>');
                            // We don't stop on single batch error unless it's a fatal response
                        }
                    },
                    error: function () {
                        result_div.append('<div class="notice notice-error inline"><p>An error occurred in batch ' + (index + 1) + '. Continuing...</p></div>');
                        if (index + 1 < totalBatches) {
                            sendBatch(index + 1);
                        } else {
                            finishImport();
                        }
                    }
                });
            }

            function finishImport() {
                var finalHtml = '<div class="notice notice-success inline"><p>Successfully imported ' + successCount + ' students.</p></div>';
                if (errors.length > 0) {
                    finalHtml += '<div class="notice notice-warning inline"><p>Warnings / Errors:</p><ul style="max-height: 150px; overflow-y: auto; background: #fff; padding: 10px; border: 1px solid #ddd;">';
                    errors.forEach(function (err) {
                        finalHtml += '<li>' + err + '</li>';
                    });
                    finalHtml += '</ul></div>';
                }
                finalHtml += '<p><strong>Reloading page in 2 seconds to show updated student list...</strong></p>';
                result_div.html(finalHtml);
                $('#sjs-cert-import-form')[0].reset();
                $('.file-name').text('No file chosen');
                
                // Auto-reload page after 2 seconds to show new students
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            }

            sendBatch(0);
        };

        reader.readAsText(file);
    });
});
