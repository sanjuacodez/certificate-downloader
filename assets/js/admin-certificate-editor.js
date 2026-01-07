/**
 * Certificate Editor - Live Preview and Template Management
 *
 * Handles real-time preview updates for the certificate template editor.
 * Manages image uploads, color changes, font styling, and layout adjustments.
 * All changes are reflected instantly in the preview pane.
 *
 * @package SJS_Cert
 * @since 1.0.0
 */

jQuery(document).ready(function ($) {

    // Load configuration from PHP (passed via wp_localize_script)
    var config = sjsCertEditorConfig.config;
    var currentBgImage = sjsCertEditorConfig.background_image_url;
    var currentSealImage = sjsCertEditorConfig.seal_image_url;
    var placeholderImageUrl = sjsCertEditorConfig.placeholder_url;
    var bgOrnamental = sjsCertEditorConfig.bg_ornamental;
    var bgModernGeo = sjsCertEditorConfig.bg_modern_geo;
    var bgWaves = sjsCertEditorConfig.bg_waves;
    var sealSample = sjsCertEditorConfig.seal_sample;
    var courseName = sjsCertEditorConfig.course_name;
    var organizationName = sjsCertEditorConfig.organization_name || 'Your Academy';

    /**
     * Read uploaded file and update preview
     *
     * Uses FileReader API to convert uploaded image to data URL.
     * Updates the corresponding preview element with the new image.
     *
     * @param {HTMLInputElement} input  The file input element
     * @param {string}          target Target selector or preview type ('bg', 'seal', or jQuery selector)
     */
    function readURL(input, target) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                // Handle different target types
                if (target === 'bg') {
                    currentBgImage = e.target.result;
                    updatePreview();
                } else if (target === 'seal') {
                    currentSealImage = e.target.result;
                    updatePreview();
                } else {
                    // Direct image element update
                    $(target).attr('src', e.target.result).show();
                    updatePreview();
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Attach file upload listeners to image input fields
    $('#logo_file').change(function () { readURL(this, '#preview-logo-hidden'); });
    $('#footer_logo_file').change(function () { readURL(this, '#preview-footer-logo-hidden'); });
    $('#background_image_file').change(function () { readURL(this, 'bg'); });
    $('#seal_image_file').change(function () { readURL(this, 'seal'); });
    
    // Attach live preview listeners to all form controls
    // Triggers updatePreview() whenever any input value changes
    $('input, select, textarea').on('change input', function() {
        updatePreview();
    });

    /**
     * Update certificate preview in real-time
     *
     * This is the core function that rebuilds the entire certificate preview
     * based on current form values. It handles:
     * - Typography (fonts, sizes, colors)
     * - Layout (orientation, padding, dimensions)
     * - Background (color, image, opacity)
     * - All certificate sections (header, body, footer, signatures, etc.)
     *
     * Performance Note: This function is called on every input change,
     * so it needs to be efficient. Consider debouncing if performance issues arise.
     */
    function updatePreview() {
        // === 1. Gather all typography settings ===
        var headingFontFamily = $('#heading_font_family').val();
        var headingFontSize = $('#heading_font_size').val();
        var headingFontColor = $('#heading_font_color').val();
        
        // Body font with fallback for backward compatibility
        var bodyFontFamily = $('#font_family').val() || $('#body_font_family').val();
        var bodyFontSize = $('#body_font_size').val();
        var bodyFontColor = $('#body_font_color').val();
        
        // Student name styling
        var nameFontFamily = $('#name_font_family').val();
        var nameFontSize = $('#name_font_size').val();
        var nameFontColor = $('#name_font_color').val();
        var nameFontStyle = $('#name_font_style').val();
        var nameFontDecoration = $('#name_font_decoration').val();

        // === 2. Layout & Background settings ===
        var orientation = $('#orientation').val();
        var pageSize = $('#page_size').val();
        var layoutMode = $('#layout_mode').val();
        var contentPadding = $('#content_padding').val();
        var backgroundColor = $('#background_color').val();
        var backgroundOpacity = $('#background_opacity').val();
        var sidebarBg = $('#sidebar_bg').val();
        var sidebarWidth = $('#sidebar_width').val();
        var removeBg = $('input[name="remove_background_image"]').is(':checked');

		// === 3. Header section settings ===
        var logoHeight = $('#logo_height').val();
		var logoAlignment = $('#logo_alignment').val();
		var mainHeading = $('#main_heading').val();

		// === 4. Footer section settings ===
        var dateLabel = $('#date_label').val();
        var organizationName = $('#organization_name').val();
        var footerLogoHeight = $('#footer_logo_height').val();
		var footerLogoAlignment = $('#footer_logo_alignment').val();

        // === 5. Get DOM elements for manipulation ===
        var frame = $('#sjs-cert-preview-frame');
        var borderBox = $('#preview-border-box');
        var contentLayer = $('#preview-content-layer');
        var bgImg = $('#preview-bg-img');

        // === 6. Calculate frame dimensions based on page size and orientation ===
        // Scale factor for preview (0.6 = 60% of actual size)
        var scale = 0.6;
        var w = 1123, h = 794; // Default A4 dimensions in pixels at 96 DPI
        var containerW = 630;

        // Adjust dimensions based on selected page size
        if (pageSize === 'Letter') {
            w = 1056; h = 816;
        } else if (pageSize === 'Legal') {
            w = 1344; h = 816;
        } else {
            // A4 is default
            w = 1123; h = 794;
        }

        if (orientation === 'portrait') {
            var temp = w; w = h; h = temp;
        }

        scale = containerW / w;
        var vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
        var maxH = vh * 0.90;
        if ((h * scale) > maxH) {
            scale = maxH / h;
        }

        frame.css({ width: w + 'px', height: h + 'px', transform: 'scale(' + scale + ')' });
        $('#preview-wrapper').css({ width: (w * scale) + 'px', height: (h * scale) + 'px' });

        // --- 2. Background ---
        frame.css({ backgroundColor: backgroundColor });
        if (currentBgImage && !removeBg) {
            bgImg.attr('src', currentBgImage).show();
            bgImg.css('opacity', backgroundOpacity);
        } else {
            bgImg.hide();
        }

        // --- 3. Border ---
        var borderStyle = $('#border_style').val();
        var borderColor = $('#border_color').val();
        var borderWidth = $('#border_width').val();
        var borderRadius = $('#border_radius').val();

        var ibEnabled = $('#inner_border_enabled').is(':checked');
        var ibStyle = $('#inner_border_style').val();
        var ibColor = $('#inner_border_color').val();
        var ibWidth = $('#inner_border_width').val();

        var borderCss = { borderRadius: borderRadius + 'px' };
        if (borderStyle === 'none') {
            borderCss.border = 'none';
        } else {
            borderCss.borderStyle = borderStyle;
            borderCss.borderColor = borderColor;
            borderCss.borderWidth = borderWidth + 'px';
        }
        borderBox.css(borderCss);

        $('#preview-inner-border-box').css({
            border: (ibEnabled ? ibStyle + ' ' + ibWidth + 'px ' + ibColor : 'none'),
            borderRadius: Math.max(0, borderRadius - 10) + 'px',
            display: (ibEnabled ? 'block' : 'none')
        });

        // --- 4. Content Padding ---
        contentLayer.css('padding', contentPadding);

        // --- 5. Content Generation ---
        var logoSrc = $('#preview-logo-hidden').attr('src');
        var footerLogoSrc = $('#preview-footer-logo-hidden').attr('src');

        // Seal / Badge
        var sealHtml = '';
        if ($('#seal_enabled').is(':checked')) {
            var sSize = $('#seal_size').val();
            var sPos = $('#seal_position').val();
            var sText = $('#seal_text').val();
            var sStyle = `position: absolute; width: ${sSize}px; z-index: 10; display: flex; align-items: center; justify-content: center; text-align: center; font-family: sans-serif;`;

            if (sPos === 'top_right') sStyle += ' top: 40px; right: 40px;';
            else if (sPos === 'top_left') sStyle += ' top: 40px; left: 40px;';
            else if (sPos === 'bottom_center') sStyle += ' bottom: 40px; left: 50%; transform: translateX(-50%);';

            if (currentSealImage) {
                sealHtml = `<div style="${sStyle}"><img src="${currentSealImage}" style="width: 100%; height: auto;"></div>`;
            } else {
                sealHtml = `<div style="${sStyle} border: 2px solid ${bodyFontColor}; border-radius: 50%; height: ${sSize}px; font-size: 10px; color: ${bodyFontColor}; font-weight: bold; padding: 5px; box-sizing: border-box; background: rgba(255,255,255,0.8);">${sText}</div>`;
            }
        }

        // Content Blocks
        var blocks = ['pre_title', 'student_name', 'main_text', 'course_name', 'description'];
        var contentHtml = '';
        blocks.forEach(function (key) {
            var textInput = $('input[name="template_config[content_blocks][' + key + '][text]"]');
            if (textInput.length === 0) return;
            var text = textInput.val();
            if (text) {
                text = text.replace('[student_name]', 'John Doe')
                    .replace('[course_name]', courseName);

                var style = '';
                if (key === 'student_name') {
                    style = `font-family: '${nameFontFamily}', cursive; color: ${nameFontColor}; font-size: ${nameFontSize}px; text-decoration: ${nameFontDecoration}; margin: 15px 0;`;
                    if (nameFontStyle === 'italic') style += ' font-style: italic;';
                    if (nameFontStyle === 'bold') style += ' font-weight: bold;';
                    if (nameFontStyle === 'bold-italic') style += ' font-weight: bold; font-style: italic;';
                    if (nameFontStyle === 'normal') style += ' font-weight: normal; font-style: normal;';
                } else if (key === 'course_name') {
                    style = 'font-weight: bold; margin: 10px 0; font-size: 1.2em;';
                } else {
                    style = 'margin: 5px 0;';
                }
                contentHtml += `<div style="${style}">${text}</div>`;
            }
        });

        // Signatures
        var signaturesHtml = '';
        $('.signature-item').each(function () {
            var name = $(this).find('.sig-name').val();
            var role = $(this).find('.sig-role').val();
            var img = $(this).find('.existing-sig-img').attr('src');

            signaturesHtml += `
				<td style="padding: 0 10px;">
					<div style='text-align: center;'>
						${img ? `<img src="${img}" class="sig-img">` : ''}
						<div class="sig-line"></div>
						<div style="font-weight: bold; font-size: 14px;">${name}</div>
						<div style="font-size: 12px;">${role}</div>
					</div>
				</td>
			`;
        });

        // Student Photo
        var studentPhotoHtml = '';
        if ($('#enable_student_photo').is(':checked')) {
            var pSize = $('#photo_size').val();
            var pShape = $('#photo_shape').val();
            var pBRadius = (pShape === 'circle') ? '50%' : '0';

            var pStyle = `width: ${pSize}px; height: ${pSize}px; object-fit: cover; margin-bottom: 20px; border-radius: ${pBRadius};`;
            studentPhotoHtml = `<img src="${placeholderImageUrl}" style="${pStyle}" alt="Student Photo">`;
        }

        // QR Code
        var qrHtml = '';
        if ($('#enable_qr_code').is(':checked')) {
            var qrSize = $('#qr_size').val();
            qrHtml = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Example" style="width: ${qrSize}px; height: ${qrSize}px;">`;
        }

		var headerHtml = `
			<div class='header'>
				${logoSrc ? `<div style="text-align: ${logoAlignment};"><img src='${logoSrc}' class='logo' style="max-height: ${logoHeight}px;"></div>` : ''}
				${studentPhotoHtml ? `<div>${studentPhotoHtml}</div>` : ''}
				<h1 style="font-family: '${headingFontFamily}', sans-serif; color: ${headingFontColor}; font-size: ${headingFontSize}px; margin-bottom: 20px;">${mainHeading || ''}</h1>
			</div>
		`;

		var bodyHtml = `
			<div class='content' style="font-family: '${bodyFontFamily}', sans-serif; color: ${bodyFontColor}; font-size: ${bodyFontSize}px;">
				${contentHtml}
			</div>
		`;

        var qrLeft = ($('#enable_qr_code').is(':checked') && $('#qr_position').val() === 'bottom_left') ? qrHtml : '';
        var qrRight = ($('#enable_qr_code').is(':checked') && $('#qr_position').val() === 'bottom_right') ? qrHtml : '';

        var footerHtml = `
			<div class='footer'>
				<table class='footer-table'>
					<tr>
						<td width="33%">
							${qrLeft ? `<div>${qrLeft}</div>` : ''}
							<div style="font-weight: bold; margin-bottom: 5px;">${new Date().toLocaleDateString()}</div>
							<div style='border-bottom: 1px solid #000; width: 150px; margin-bottom: 5px; margin: 0 auto;'></div>
							<span style="color: ${bodyFontColor};">${dateLabel}</span>
						</td>
						<td width="33%" style="text-align: ${footerLogoAlignment};">
							${footerLogoSrc ? `<img src='${footerLogoSrc}' class='footer-logo' style="max-height: ${footerLogoHeight}px;">` : ''}
						${organizationName ? `<div style="margin-top: 10px; font-weight: bold; font-size: 14px;">${organizationName.replace('[organization_name]', organizationName)}</div>` : ''}
						</td>
						<td width="33%">
							${qrRight ? `<div>${qrRight}</div>` : ''}
							<table style='width: auto; margin: 0 auto;'>
								<tr>${signaturesHtml}</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
		`;

        var html = '';
        if (layoutMode === 'center') {
            html = `${sealHtml}<table class="layout-table"><tr><td style="vertical-align: middle; text-align: center;">${headerHtml}${bodyHtml}${footerHtml}</td></tr></table>`;
        } else if (layoutMode === 'two_column') {
            html = `
				${sealHtml}
				<table class="layout-table" style="height: 100%;">
					<tr>
						<td width="${sidebarWidth}%" style="background: ${sidebarBg}; vertical-align: top; padding: 40px 20px; border-right: 1px solid rgba(0,0,0,0.1);">
							${logoSrc ? `<div style="text-align: center; margin-bottom: 30px;"><img src='${logoSrc}' style="max-height: ${logoHeight}px;"></div>` : ''}
							${studentPhotoHtml ? `<div style="text-align: center; margin-bottom: 30px;">${studentPhotoHtml}</div>` : ''}
						</td>
						<td style="vertical-align: middle; text-align: center; padding: 40px;">
							<h1 style="font-family: '${headingFontFamily}', sans-serif; color: ${headingFontColor}; font-size: ${headingFontSize * 0.9}px; margin-bottom: 30px;">${mainHeading || ''}</h1>
							${bodyHtml}
							<div style="margin-top: 50px;">
								<table class="footer-table">
									<tr>
										<td width="50%" style="text-align: left;">
											<div style="font-weight: bold; margin-bottom: 5px;">${new Date().toLocaleDateString()}</div>
											<div style='border-bottom: 1px solid #000; width: 120px; margin-bottom: 5px;'></div>
											<span style="font-size: 11px; color: ${bodyFontColor};">${dateLabel}</span>
										</td>
										<td width="50%"><table style='width: auto; margin: 0 auto;'><tr>${signaturesHtml}</tr></table></td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
				</table>
			`;
        } else {
            var topAlign = (layoutMode === 'top');
            html = `${sealHtml}<table class="layout-table"><tr><td style="height: 1%; vertical-align: top; text-align: center;">${headerHtml}</td></tr><tr><td style="vertical-align: ${topAlign ? 'top' : 'middle'}; text-align: center;">${bodyHtml}</td></tr><tr><td style="height: 1%; vertical-align: bottom; text-align: center;">${footerHtml}</td></tr></table>`;
        }

        contentLayer.html(html);
    }

    // Templates Data
    var certTemplates = [
        {
            name: 'Royal Gold (Landscape A4)',
            config: {
                orientation: 'landscape', page_size: 'A4', layout_mode: 'distribute',
                heading_font_family: 'Georgia', heading_font_size: 48, heading_font_color: '#1a1a1a',
                body_font_family: 'Helvetica', body_font_size: 18, body_font_color: '#333333',
                name_font_family: 'Great Vibes', name_font_size: 56, name_font_color: '#cca43b',
                name_font_style: 'normal', outer_border_style: 'double', outer_border_color: '#cca43b',
                outer_border_width: 8, outer_border_radius: 0, inner_border_enabled: true,
                inner_border_style: 'solid', inner_border_color: '#e6d8a8', inner_border_width: 2,
                background_color: '#fffcf5', background_image_url: '', content_padding: '40px',
                seal_enabled: true, seal_image_url: sealSample, seal_size: 100, seal_position: 'top_right'
            }
        },
        {
            name: 'Formal Portrait (A4)',
            config: {
                orientation: 'portrait', page_size: 'A4', layout_mode: 'center',
                heading_font_family: 'Times New Roman', heading_font_size: 44, heading_font_color: '#2c3e50',
                body_font_family: 'Arial', body_font_size: 16, body_font_color: '#34495e',
                name_font_family: 'Georgia', name_font_size: 42, name_font_color: '#2980b9',
                name_font_style: 'bold', outer_border_style: 'solid', outer_border_color: '#2980b9',
                outer_border_width: 10, outer_border_radius: 0, inner_border_enabled: false,
                background_color: '#ffffff', background_image_url: '', content_padding: '50px',
                seal_enabled: true, seal_image_url: sealSample, seal_text: 'CERTIFIED', seal_size: 80, seal_position: 'bottom_center',
                enable_student_photo: true, photo_size: 120, photo_shape: 'circle'
            }
        },
        {
            name: 'Minimal Dark (Landscape)',
            config: {
                orientation: 'landscape', page_size: 'A4', layout_mode: 'distribute',
                heading_font_family: 'Helvetica', heading_font_size: 52, heading_font_color: '#ffffff',
                body_font_family: 'Helvetica', body_font_size: 18, body_font_color: '#ecf0f1',
                name_font_family: 'Pacifico', name_font_size: 60, name_font_color: '#f1c40f',
                name_font_style: 'normal', outer_border_style: 'solid', outer_border_color: '#f1c40f',
                outer_border_width: 2, outer_border_radius: 0, inner_border_enabled: false,
                background_color: '#2c3e50', background_image_url: bgModernGeo, background_opacity: 0.2,
                content_padding: '60px', seal_enabled: false
            }
        },
        {
            name: 'Artistic Script (Letter)',
            config: {
                orientation: 'landscape', page_size: 'Letter', layout_mode: 'center',
                heading_font_family: 'Pacifico', heading_font_size: 40, heading_font_color: '#005f73',
                body_font_family: 'Helvetica', body_font_size: 17, body_font_color: '#0a9396',
                name_font_family: 'Dancing Script', name_font_size: 48, name_font_color: '#94d2bd',
                name_font_style: 'normal', outer_border_style: 'none', inner_border_enabled: true,
                inner_border_style: 'dashed', inner_border_color: '#94d2bd', inner_border_width: 2,
                background_color: '#e9d8a6', background_image_url: bgWaves, content_padding: '50px',
                seal_enabled: true, seal_image_url: sealSample, seal_text: 'CREATIVE', seal_size: 90, seal_position: 'bottom_right'
            }
        },
        {
            name: 'Legal Diploma',
            config: {
                orientation: 'landscape', page_size: 'Legal', layout_mode: 'distribute',
                heading_font_family: 'Times New Roman', heading_font_size: 42, heading_font_color: '#000000',
                body_font_family: 'Times New Roman', body_font_size: 20, body_font_color: '#333333',
                name_font_family: 'Alex Brush', name_font_size: 54, name_font_color: '#b8860b',
                name_font_style: 'normal', outer_border_style: 'ridge', outer_border_color: '#daa520',
                outer_border_width: 15, outer_border_radius: 4, inner_border_enabled: true,
                background_color: '#fffdf0', content_padding: '40px', seal_enabled: true,
                seal_image_url: sealSample, seal_text: 'HONORS', seal_size: 110, seal_position: 'bottom_left'
            }
        },
        {
            name: 'Professional Sidebar (Landscape)',
            config: {
                orientation: 'landscape', page_size: 'A4', layout_mode: 'two_column',
                sidebar_bg: '#1a2a6c', sidebar_width: 30, heading_font_family: 'Georgia',
                heading_font_size: 42, heading_font_color: '#1a1a1a', body_font_family: 'Helvetica',
                body_font_size: 16, body_font_color: '#333333', name_font_family: 'Alex Brush',
                name_font_size: 52, name_font_color: '#b8860b', name_font_style: 'normal',
                outer_border_style: 'solid', outer_border_color: '#1a2a6c', outer_border_width: 4,
                background_color: '#ffffff', content_padding: '40px', seal_enabled: true,
                seal_image_url: sealSample, seal_size: 90, seal_position: 'bottom_center',
                enable_student_photo: true, photo_size: 140, photo_shape: 'circle'
            }
        },
        {
            name: 'Modern Split (Portrait A4)',
            config: {
                orientation: 'portrait', page_size: 'A4', layout_mode: 'two_column',
                sidebar_bg: '#ffffff', sidebar_width: 35, heading_font_family: 'Helvetica',
                heading_font_size: 38, heading_font_color: '#2d3436', body_font_family: 'Arial',
                body_font_size: 15, body_font_color: '#636e72', name_font_family: 'Satisfy',
                name_font_size: 44, name_font_color: '#e67e22', name_font_style: 'normal',
                outer_border_style: 'solid', outer_border_color: '#eee', outer_border_width: 1,
                outer_border_radius: 10, background_color: '#f9f9f9', content_padding: '30px',
                seal_enabled: false, enable_student_photo: true, photo_size: 130, photo_shape: 'square'
            }
        }
    ];

    // Render Templates
    var templateContainer = $('#template-reel');
    certTemplates.forEach(function (tpl, index) {
        var item = $('<div class="template-item" data-index="' + index + '"></div>');
        var baseW = 1123, baseH = 794;
        if (tpl.config.page_size === 'Letter') { baseW = 1056; baseH = 816; }
        else if (tpl.config.page_size === 'Legal') { baseW = 1344; baseH = 816; }
        if (tpl.config.orientation === 'portrait') { var tmp = baseW; baseW = baseH; baseH = tmp; }
        var aspectRatio = (baseH / baseW) * 100;
        var preview = $('<div class="template-preview"></div>');
        preview.css({
            paddingBottom: aspectRatio + '%',
            backgroundColor: tpl.config.background_color,
            backgroundImage: tpl.config.background_image_url ? 'url(' + tpl.config.background_image_url + ')' : 'none',
            border: (tpl.config.outer_border_style !== 'none') ? '2px ' + tpl.config.outer_border_style + ' ' + tpl.config.outer_border_color : '1px solid #eee'
        });
        if (tpl.config.orientation === 'portrait') preview.append('<div class="template-badge">Portrait</div>');
        preview.append('<div style="position:absolute; top:50%; left:0; width:100%; transform:translateY(-50%); text-align:center; padding:5px; box-sizing:border-box;"><h4 style="margin:0; font-size:9px; color:' + (tpl.config.heading_font_color || '#000') + '; font-family:\'' + (tpl.config.heading_font_family || 'Arial') + '\'; text-shadow: 0 0 2px #fff;">' + tpl.name.split(' ')[0] + '</h4></div>');
        item.append(preview).append('<div class="template-name" title="' + tpl.name + '">' + tpl.name + '</div>');
        templateContainer.append(item);
    });

    // Template Click Handler
    $(document).on('click', '.template-item', function () {
        var config = certTemplates[$(this).data('index')].config;
        if (config.orientation) $('#orientation').val(config.orientation);
        if (config.page_size) $('#page_size').val(config.page_size);
        if (config.layout_mode) $('#layout_mode').val(config.layout_mode);
        if (config.content_padding) $('#content_padding').val(config.content_padding.replace('px', ''));
        if (config.heading_font_family) $('#heading_font_family').val(config.heading_font_family);
        if (config.heading_font_size) $('#heading_font_size').val(config.heading_font_size);
        if (config.heading_font_color) $('#heading_font_color').val(config.heading_font_color);
        if (config.body_font_family) $('#body_font_family').val(config.body_font_family);
        if (config.body_font_size) $('#body_font_size').val(config.body_font_size);
        if (config.body_font_color) $('#body_font_color').val(config.body_font_color);
        if (config.name_font_family) $('#name_font_family').val(config.name_font_family);
        if (config.name_font_size) $('#name_font_size').val(config.name_font_size);
        if (config.name_font_color) $('#name_font_color').val(config.name_font_color);
        if (config.name_font_style) $('#name_font_style').val(config.name_font_style);
        if (config.name_font_decoration) $('#name_font_decoration').val(config.name_font_decoration || 'none');
        if (config.outer_border_style) $('#border_style').val(config.outer_border_style);
        if (config.outer_border_color) $('#border_color').val(config.outer_border_color);
        if (config.outer_border_width) $('#border_width').val(config.outer_border_width);
        if (config.outer_border_radius !== undefined) $('#border_radius').val(config.outer_border_radius);
        $('#inner_border_enabled').prop('checked', !!config.inner_border_enabled);
        if (config.inner_border_style) $('#inner_border_style').val(config.inner_border_style);
        if (config.inner_border_color) $('#inner_border_color').val(config.inner_border_color);
        if (config.inner_border_width) $('#inner_border_width').val(config.inner_border_width);
        if (config.background_color) $('#background_color').val(config.background_color);
        currentBgImage = config.background_image_url || '';
        $('#background_image_url').val(currentBgImage);
        $('#seal_enabled').prop('checked', !!config.seal_enabled);
        if (config.seal_text) $('#seal_text').val(config.seal_text);
        if (config.seal_size) $('#seal_size').val(config.seal_size);
        if (config.seal_position) $('#seal_position').val(config.seal_position);
        currentSealImage = config.seal_image_url || '';
        $('#seal_image_url').val(currentSealImage);
        if (config.sidebar_bg) $('#sidebar_bg').val(config.sidebar_bg);
        if (config.sidebar_width) $('#sidebar_width').val(config.sidebar_width);
        $('#two-column-settings').toggle(config.layout_mode === 'two_column');
        $('#enable_student_photo').prop('checked', !!config.enable_student_photo);
        if (config.photo_size) $('#photo_size').val(config.photo_size);
        if (config.photo_shape) $('#photo_shape').val(config.photo_shape || 'circle');
        $('.template-item').removeClass('selected');
        $(this).addClass('selected');
        updatePreview();
    });

    $('input, select, textarea').on('change keyup', updatePreview);
    $('#layout_mode').on('change', function () {
        if ($(this).val() === 'two_column') $('#two-column-settings').slideDown();
        else $('#two-column-settings').slideUp();
    });

    updatePreview();

    // Repeater Logic
    $('#add-signature').on('click', function () {
        var index = $('.signature-item').length;
        $('#signatures-container').append(`
			<div class="signature-item">
				<div class="form-field"><label>Name</label><input type="text" name="template_config[signatures][${index}][name]" class="widefat sig-name"></div>
				<div class="form-field"><label>Role</label><input type="text" name="template_config[signatures][${index}][role]" class="widefat sig-role"></div>
				<div class="form-field"><label>Image</label><input type="file" name="signature_files[${index}]" class="sig-file-input" accept="image/*"></div>
				<button type="button" class="button remove-signature">Remove</button>
			</div>
		`);
        updatePreview();
    });

    $(document).on('click', '.remove-signature', function () {
        $(this).closest('.signature-item').remove();
        updatePreview();
    });
});
