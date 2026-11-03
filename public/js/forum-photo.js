/* Forum photos are re-encoded in the browser before they are uploaded.
   A full-size phone photo is far too large to keep as the inline copy the feed
   renders from, and the PHP behind a deployment may not carry the GD
   extension, so the post would save with a picture nobody can see. Shrinking
   it here keeps the upload small and guarantees the copy in the database.

   Both listeners are installed on the document in capture phase so this runs
   before a page's own change and submit handlers, which read the file that is
   about to be replaced. */
(function () {
    var MAX_EDGE = 1400;
    var TARGET_BYTES = 420 * 1024;
    var READ_LIMIT = 20 * 1024 * 1024;
    // A normal photo clears the budget on the first pass; the ladder only runs
    // for pathological images (large flat PNGs of noise), where each encode is
    // another round trip on the main thread.
    var STEPS = [
        [1, 0.82], [1, 0.68], [1, 0.55],
        [0.72, 0.8], [0.72, 0.65], [0.55, 0.7]
    ];

    function isPhotoInput(node) {
        return node && node.matches && node.matches('input[type="file"][name="post_image"]');
    }

    function load(file) {
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () {
                URL.revokeObjectURL(url);
                resolve(img);
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('unreadable image'));
            };
            img.src = url;
        });
    }

    function encode(canvas, quality) {
        return new Promise(function (resolve) {
            canvas.toBlob(function (blob) { resolve(blob); }, 'image/jpeg', quality);
        });
    }

    function draw(img, scale) {
        var width = Math.max(1, Math.round(Math.min(img.naturalWidth, MAX_EDGE) * scale));
        var height = Math.max(1, Math.round((img.naturalHeight * width) / img.naturalWidth));
        var canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        var ctx = canvas.getContext('2d');
        // JPEG carries no alpha, so a transparent PNG would otherwise go black.
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);
        ctx.drawImage(img, 0, 0, width, height);
        return canvas;
    }

    function compress(file) {
        if (file.size <= TARGET_BYTES || file.size > READ_LIMIT) {
            return Promise.resolve(file);
        }

        return load(file).then(function (img) {
            var best = file;
            var canvases = {};

            function step(index) {
                if (index >= STEPS.length || best.size <= TARGET_BYTES) {
                    return best;
                }
                var scale = STEPS[index][0];
                var canvas = canvases[scale] || (canvases[scale] = draw(img, scale));

                return encode(canvas, STEPS[index][1]).then(function (blob) {
                    if (blob && (best === file || blob.size < best.size)) {
                        best = new File([blob], file.name.replace(/\.[^.]*$/, '') + '.jpg', {
                            type: 'image/jpeg',
                            lastModified: file.lastModified || Date.now()
                        });
                    }
                    return step(index + 1);
                });
            }

            return step(0);
        }).catch(function () {
            return file;
        });
    }

    document.addEventListener('change', function (event) {
        var input = event.target;
        if (!isPhotoInput(input)) return;

        var file = input.files[0];
        if (!file || file.size <= TARGET_BYTES) {
            input.photoPrep = null;
            input.dataset.photoPending = '';
            return;
        }

        input.dataset.photoPending = '1';
        input.photoPrep = compress(file).then(function (prepared) {
            input.dataset.photoPending = '';
            input.photoPrep = null;
            if (prepared !== file && input.files[0] === file) {
                var bag = new DataTransfer();
                bag.items.add(prepared);
                input.files = bag.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }, true);

    document.addEventListener('submit', function (event) {
        var input = event.target.querySelector
            ? event.target.querySelector('input[type="file"][name="post_image"]')
            : null;
        if (!input || !input.photoPrep) return;

        // The Post press landed while the photo was still being re-encoded.
        event.preventDefault();
        input.photoPrep.then(function () {
            if (input.files.length) event.target.requestSubmit();
        });
    }, true);
})();
