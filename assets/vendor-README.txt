Local vendor assets

This project prefers local copies of third-party JS/CSS for offline reliability.

Quill editor (v1.3.6 recommended)
- Download `quill.min.js` and `quill.snow.css` from the Quill releases or CDN.
- Place `quill.min.js` into `assets/js/quill.min.js`.
- Place `quill.snow.css` into `assets/css/quill.snow.css`.
- If the CSS references any font files, put them under `assets/fonts/` and update the paths in the CSS accordingly.

Fonts
- Put any custom fonts (e.g., certificate fonts) into `assets/fonts/` and add @font-face rules to `assets/css/print.css` or a new `assets/css/fonts.css`.

After adding the real files, remove these placeholder instructions. If you want, I can try to download and vendor them for you if you grant network access or provide the files.
