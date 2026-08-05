<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<script>
(function (window) {
    function parsePlaceholderPositions(rawValue) {
        if (!rawValue) return null;
        if (typeof rawValue === 'object') return rawValue;
        try {
            return JSON.parse(rawValue);
        } catch (e) {
            return null;
        }
    }

    function getAspectRatio(pageSize, pageOrientation) {
        if (pageSize === 'Letter') {
            return pageOrientation === 'landscape' ? '279 / 216' : '216 / 279';
        }

        return pageOrientation === 'landscape' ? '297 / 210' : '210 / 297';
    }

    function resolveRatioValue(value, size) {
        if (value === null || value === '' || typeof value === 'undefined') return 0;
        if (typeof value === 'string' && value.trim().endsWith('%')) {
            var numeric = parseFloat(value);
            if (isNaN(numeric)) return 0;
            return Math.round((numeric / 100) * size);
        }
        var numeric = parseFloat(value);
        if (isNaN(numeric)) return 0;
        if (numeric >= 0 && numeric <= 1) {
            return Math.round(numeric * size);
        }
        if (numeric > 1 && numeric <= size) {
            return Math.round(numeric);
        }
        if (numeric > size && numeric <= 100) {
            return Math.round((numeric / 100) * size);
        }
        return Math.round(numeric);
    }

    function getDefaultPlaceholderPositions() {
        return {
            name: { x_ratio: 0.5, y_ratio: 0.3, font_size: 36, color: '#000000', align: 'center' },
            'kegiatan->nama_kegiatan': { x_ratio: 0.5, y_ratio: 0.4, font_size: 26, color: '#000000', align: 'center' },
            'kegiatan->tema_kegiatan': { x_ratio: 0.5, y_ratio: 0.5, font_size: 20, color: '#000000', align: 'center' },
            sebagai: { x_ratio: 0.5, y_ratio: 0.55, font_size: 20, color: '#000000', align: 'center' },
            nomor_surat: { x_ratio: 0.5, y_ratio: 0.63, font_size: 18, color: '#000000', align: 'center' },
            verification_text: { x_ratio: 0.5, y_ratio: 0.8, font_size: 16, color: '#000000', align: 'center' },
            verification_qr: { x_ratio: 0.85, y_ratio: 0.8, font_size: 16, color: '#000000', align: 'center', qr_size: 120 }
        };
    }

    function normalizePlaceholderPosition(rawPosition, canvasWidth, canvasHeight, fallback) {
        var position = rawPosition || {};
        var defaults = fallback || {};
        var width = Math.max(1, canvasWidth || 900);
        var height = Math.max(1, canvasHeight || 600);

        var x = defaults.x;
        var xValue = position.x_ratio;
        if (typeof xValue === 'undefined' && typeof position.x_percent !== 'undefined') {
            xValue = position.x_percent;
        }

        if (typeof xValue !== 'undefined') {
            x = resolveRatioValue(xValue, width);
        } else if (typeof position.x !== 'undefined' || typeof position.left !== 'undefined') {
            x = resolveRatioValue(typeof position.x !== 'undefined' ? position.x : position.left, width);
        } else if (typeof defaults.x_ratio !== 'undefined' || typeof defaults.x_percent !== 'undefined') {
            x = resolveRatioValue(typeof defaults.x_ratio !== 'undefined' ? defaults.x_ratio : defaults.x_percent, width);
        }

        var y = defaults.y;
        var yValue = position.y_ratio;
        if (typeof yValue === 'undefined' && typeof position.y_percent !== 'undefined') {
            yValue = position.y_percent;
        }

        if (typeof yValue !== 'undefined') {
            y = resolveRatioValue(yValue, height);
        } else if (typeof position.y !== 'undefined' || typeof position.top !== 'undefined') {
            y = resolveRatioValue(typeof position.y !== 'undefined' ? position.y : position.top, height);
        } else if (typeof defaults.y_ratio !== 'undefined' || typeof defaults.y_percent !== 'undefined') {
            y = resolveRatioValue(typeof defaults.y_ratio !== 'undefined' ? defaults.y_ratio : defaults.y_percent, height);
        }

        var fontSize = defaults.font_size;
        if (typeof defaults.size !== 'undefined') fontSize = defaults.size;
        if (typeof position.font_size !== 'undefined') fontSize = position.font_size;

        return {
            x: typeof x !== 'undefined' ? x : width / 2,
            y: typeof y !== 'undefined' ? y : height / 2,
            fontSize: fontSize || 24,
            color: position.color || defaults.color || '#000000',
            align: position.align || position.alignment || defaults.align || 'center',
            isQr: typeof position.is_qr !== 'undefined' ? !!position.is_qr : (typeof defaults.is_qr !== 'undefined' ? !!defaults.is_qr : true),
            qrSize: position.qr_size || defaults.qr_size || Math.max(80, Math.round((fontSize || 24) * 4))
        };
    }

    function resolvePlaceholderPositions(rawPositions, canvasWidth, canvasHeight) {
        var parsed = parsePlaceholderPositions(rawPositions);
        var defaults = getDefaultPlaceholderPositions();
        var resolved = {};

        if (!parsed || typeof parsed !== 'object' || Object.keys(parsed).length === 0) {
            Object.keys(defaults).forEach(function (key) {
                resolved[key] = normalizePlaceholderPosition(defaults[key], canvasWidth, canvasHeight, defaults[key]);
            });
            return resolved;
        }

        Object.keys(parsed).forEach(function (key) {
            var raw = parsed[key];
            resolved[key] = normalizePlaceholderPosition(raw, canvasWidth, canvasHeight, defaults[key]);
        });
        return resolved;
    }

    function renderCanvasPreview(container, options) {
        if (!container) return null;

        var canvas = document.createElement('canvas');
        canvas.width = options.width || 900;
        canvas.height = options.height || 600;
        canvas.style.width = '100%';
        canvas.style.height = 'auto';
        canvas.style.display = 'block';
        canvas.style.backgroundColor = '#ffffff';

        container.innerHTML = '';
        container.appendChild(canvas);

        var ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        var positions = options.positions ? resolvePlaceholderPositions(options.positions, canvas.width, canvas.height) : resolvePlaceholderPositions(options.rawPositions, canvas.width, canvas.height);
        var values = options.values || {};
        var labelValues = {
            name: values.name || 'Nama Peserta',
            'kegiatan->nama_kegiatan': values['kegiatan->nama_kegiatan'] || 'Nama Kegiatan',
            'kegiatan->tema_kegiatan': values['kegiatan->tema_kegiatan'] || 'Tema Kegiatan',
            sebagai: values.sebagai || 'Peserta',
verification_text: values.verification_text || 'ABC123-VERIFY',
        verification_qr: values.verification_qr || 'ABC123-VERIFY'
        };

        if (positions.barcode) {
            if (!positions.verification_text) {
                positions.verification_text = positions.barcode;
            }
            if (!positions.verification_qr) {
                positions.verification_qr = positions.barcode;
            }
        }

        function drawQrCode(ctx, x, y, size, value) {
            var qrValue = value || 'QR';
            if (window.QRious) {
                var qr = new QRious({ value: qrValue, size: size });
                var img = new Image();
                img.onload = function () {
                    ctx.drawImage(img, x - (size / 2), y - (size / 2), size, size);
                };
                img.src = qr.toDataURL();
            } else {
                ctx.strokeStyle = '#000000';
                ctx.lineWidth = 2;
                ctx.strokeRect(x - (size / 2), y - (size / 2), size, size);
                ctx.fillStyle = '#000000';
                ctx.font = Math.max(10, Math.round(size / 6)) + 'px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('QR', x, y);
            }
        }

        function drawPlaceholders() {
            Object.keys(positions).forEach(function (key) {
                var pos = positions[key];
                if (!pos) return;
                var text = labelValues[key] || '';
                var fontSize = pos.scaledFontSize || (pos.fontSize || 24);
                var qrSize = pos.scaledQrSize || (pos.qrSize || 120);
                if (key === 'verification_qr') {
                    drawQrCode(ctx, pos.x, pos.y, qrSize, text);
                    return;
                }
                ctx.font = fontSize + 'px Arial';
                ctx.fillStyle = pos.color || '#000000';
                ctx.textAlign = pos.align === 'left' ? 'left' : (pos.align === 'right' ? 'right' : 'center');
                ctx.textBaseline = 'middle';
                ctx.fillText(text, pos.x, pos.y);
            });
        }

        function renderPlaceholdersWithScale(imageScale) {
            Object.keys(positions).forEach(function (key) {
                var pos = positions[key];
                if (!pos) return;
                pos.scaledFontSize = Math.max(1, Math.round((pos.fontSize || 24) * (imageScale || 1)));
                pos.scaledQrSize = Math.max(40, Math.round((pos.qrSize || 120) * (imageScale || 1)));
            });
            drawPlaceholders(positions);
        }

        if (options.backgroundImage) {
            var img = new Image();
            img.onload = function () {
                var scale = Math.min(canvas.width / img.width, canvas.height / img.height);
                var drawWidth = img.width * scale;
                var drawHeight = img.height * scale;
                ctx.drawImage(img, 0, 0, drawWidth, drawHeight);
                renderPlaceholdersWithScale(scale);
            };
            img.src = options.backgroundImage;
        } else {
            renderPlaceholdersWithScale(1);
        }

        return canvas;
    }

    window.TemplatePreviewRenderer = {
        parsePlaceholderPositions: parsePlaceholderPositions,
        getAspectRatio: getAspectRatio,
        resolvePlaceholderPositions: resolvePlaceholderPositions,
        renderCanvasPreview: renderCanvasPreview
    };
})(window);
</script>
